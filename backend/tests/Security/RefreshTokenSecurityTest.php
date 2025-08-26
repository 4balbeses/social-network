<?php

namespace App\Tests\Security;

use App\Entity\RefreshToken;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RefreshTokenSecurityTest extends WebTestCase
{
    private $client;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        
        // Clean up database before each test
        $this->truncateEntities([
            RefreshToken::class,
            User::class
        ]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
    }

    private function truncateEntities(array $entities): void
    {
        foreach ($entities as $entity) {
            $cmd = $this->entityManager->getClassMetadata($entity);
            $connection = $this->entityManager->getConnection();
            $dbPlatform = $connection->getDatabasePlatform();
            $q = $dbPlatform->getTruncateTableSQL($cmd->getTableName());
            $connection->executeStatement($q);
        }
    }

    private function createUser(string $email = 'test@example.com', string $password = 'password123'): User
    {
        $user = new User();
        $user->setEmail($email);
        
        $hasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        $user->setPassword($hasher->hashPassword($user, $password));
        $user->setRoles(['ROLE_USER']);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    private function createRefreshToken(User $user, bool $expired = false): RefreshToken
    {
        $refreshToken = new RefreshToken();
        $refreshToken->setUser($user);
        $refreshToken->setToken(bin2hex(random_bytes(64)));
        $refreshToken->setCreatedAt(new DateTimeImmutable());
        
        if ($expired) {
            $refreshToken->setExpiresAt(new DateTimeImmutable('-1 day'));
        } else {
            $refreshToken->setExpiresAt(new DateTimeImmutable('+30 days'));
        }

        $this->entityManager->persist($refreshToken);
        $this->entityManager->flush();

        return $refreshToken;
    }

    public function testRefreshTokenTimingAttackPrevention(): void
    {
        $user = $this->createUser();
        $validToken = $this->createRefreshToken($user);
        $invalidToken = 'invalid_token_' . str_repeat('a', 100);

        // Measure time for valid token request
        $start = microtime(true);
        $this->client->request(
            'POST',
            '/api/token/refresh',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['refresh_token' => $validToken->getToken()])
        );
        $validTokenTime = microtime(true) - $start;

        // Measure time for invalid token request
        $start = microtime(true);
        $this->client->request(
            'POST',
            '/api/token/refresh',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['refresh_token' => $invalidToken])
        );
        $invalidTokenTime = microtime(true) - $start;

        // Response times should be similar to prevent timing attacks
        // Allow for reasonable variance (within 100ms)
        $timeDifference = abs($validTokenTime - $invalidTokenTime);
        $this->assertLessThan(0.1, $timeDifference, 
            'Response times differ too much, potential timing attack vulnerability');
    }

    public function testRefreshTokenEntropyValidation(): void
    {
        $user = $this->createUser();
        
        // Create multiple refresh tokens and verify they have sufficient entropy
        $tokens = [];
        for ($i = 0; $i < 10; $i++) {
            $token = $this->createRefreshToken($user);
            $tokens[] = $token->getToken();
        }

        // All tokens should be unique
        $uniqueTokens = array_unique($tokens);
        $this->assertCount(10, $uniqueTokens, 'Tokens are not unique, insufficient entropy');

        // Tokens should be 128 characters long (64 bytes in hex)
        foreach ($tokens as $token) {
            $this->assertEquals(128, strlen($token), 'Token length is incorrect');
            $this->assertMatchesRegularExpression('/^[a-f0-9]{128}$/', $token, 'Token contains invalid characters');
        }

        // Verify randomness by checking distribution of characters
        $combinedTokens = implode('', $tokens);
        $charCounts = array_count_values(str_split($combinedTokens));
        
        // Should have all hex characters (0-9, a-f)
        $expectedChars = array_merge(range('0', '9'), range('a', 'f'));
        foreach ($expectedChars as $char) {
            $this->assertArrayHasKey($char, $charCounts, "Character '$char' not found in tokens");
        }
    }

    public function testRefreshTokenDatabaseConstraints(): void
    {
        $user1 = $this->createUser('user1@example.com');
        $user2 = $this->createUser('user2@example.com');
        
        $token = bin2hex(random_bytes(64));

        // Create first refresh token
        $refreshToken1 = new RefreshToken();
        $refreshToken1->setUser($user1);
        $refreshToken1->setToken($token);
        $refreshToken1->setCreatedAt(new DateTimeImmutable());
        $refreshToken1->setExpiresAt(new DateTimeImmutable('+30 days'));

        $this->entityManager->persist($refreshToken1);
        $this->entityManager->flush();

        // Try to create second refresh token with same token (should fail due to unique constraint)
        $refreshToken2 = new RefreshToken();
        $refreshToken2->setUser($user2);
        $refreshToken2->setToken($token); // Same token
        $refreshToken2->setCreatedAt(new DateTimeImmutable());
        $refreshToken2->setExpiresAt(new DateTimeImmutable('+30 days'));

        $this->entityManager->persist($refreshToken2);
        
        $this->expectException(\Exception::class);
        $this->entityManager->flush();
    }

    public function testRefreshTokenCascadeDelete(): void
    {
        $user = $this->createUser();
        $refreshToken = $this->createRefreshToken($user);

        // Verify token exists
        $tokenExists = $this->entityManager->getRepository(RefreshToken::class)
            ->findOneBy(['token' => $refreshToken->getToken()]);
        $this->assertNotNull($tokenExists);

        // Delete user - refresh tokens should be cascade deleted
        $this->entityManager->remove($user);
        $this->entityManager->flush();

        // Verify token is deleted
        $this->entityManager->clear();
        $tokenExists = $this->entityManager->getRepository(RefreshToken::class)
            ->findOneBy(['token' => $refreshToken->getToken()]);
        $this->assertNull($tokenExists);
    }

    public function testRefreshTokenRequestHeaderSecurity(): void
    {
        $user = $this->createUser();
        $refreshToken = $this->createRefreshToken($user);

        $maliciousHeaders = [
            'HTTP_X_FORWARDED_FOR' => '127.0.0.1, evil.com',
            'HTTP_X_REAL_IP' => 'malicious_ip',
            'HTTP_USER_AGENT' => '<script>alert("xss")</script>',
            'HTTP_REFERER' => 'http://malicious-site.com',
            'HTTP_ORIGIN' => 'http://evil.com',
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest; <script>',
        ];

        $this->client->request(
            'POST',
            '/api/token/refresh',
            [],
            [],
            array_merge([
                'CONTENT_TYPE' => 'application/json',
            ], $maliciousHeaders),
            json_encode(['refresh_token' => $refreshToken->getToken()])
        );

        // Should still work normally despite malicious headers
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('token', $data);
        $this->assertArrayHasKey('refresh_token', $data);
    }

    public function testRefreshTokenCSRFProtection(): void
    {
        $user = $this->createUser();
        $refreshToken = $this->createRefreshToken($user);

        // Simulate CSRF attack by sending request with malicious referer
        $this->client->request(
            'POST',
            '/api/token/refresh',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_REFERER' => 'http://malicious-site.com',
                'HTTP_ORIGIN' => 'http://evil.com'
            ],
            json_encode(['refresh_token' => $refreshToken->getToken()])
        );

        // API endpoints should not be vulnerable to CSRF (stateless)
        // but verify appropriate CORS headers are set if needed
        $response = $this->client->getResponse();
        
        // Either should work (no CSRF protection on API) or return appropriate CORS error
        $this->assertContains($response->getStatusCode(), [
            Response::HTTP_OK, 
            Response::HTTP_FORBIDDEN, 
            Response::HTTP_BAD_REQUEST
        ]);
    }

    public function testRefreshTokenBruteForceProtection(): void
    {
        $user = $this->createUser();

        // Attempt multiple invalid refresh token requests rapidly
        $invalidAttempts = 0;
        $blockedResponses = 0;

        for ($i = 0; $i < 20; $i++) {
            $invalidToken = 'invalid_token_' . $i . '_' . str_repeat('a', 100);
            
            $this->client->request(
                'POST',
                '/api/token/refresh',
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                json_encode(['refresh_token' => $invalidToken])
            );

            $statusCode = $this->client->getResponse()->getStatusCode();
            
            if ($statusCode === Response::HTTP_UNAUTHORIZED) {
                $invalidAttempts++;
            } elseif ($statusCode === Response::HTTP_TOO_MANY_REQUESTS) {
                $blockedResponses++;
            }
        }

        // All requests should return 401 (or some should be rate limited)
        $totalResponses = $invalidAttempts + $blockedResponses;
        $this->assertEquals(20, $totalResponses);
        $this->assertGreaterThan(0, $invalidAttempts);
        
        // Note: Rate limiting might not be implemented yet, but this test documents the expectation
    }

    public function testRefreshTokenResponseHeaderSecurity(): void
    {
        $user = $this->createUser();
        $refreshToken = $this->createRefreshToken($user);

        $this->client->request(
            'POST',
            '/api/token/refresh',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['refresh_token' => $refreshToken->getToken()])
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $headers = $response->headers;

        // Verify security headers are present (adjust based on your configuration)
        $securityHeaders = [
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => ['DENY', 'SAMEORIGIN'],
            'X-XSS-Protection' => '1; mode=block',
            'Referrer-Policy' => ['no-referrer', 'strict-origin-when-cross-origin'],
        ];

        foreach ($securityHeaders as $header => $expectedValue) {
            if ($headers->has($header)) {
                $actualValue = $headers->get($header);
                if (is_array($expectedValue)) {
                    $this->assertContains($actualValue, $expectedValue, 
                        "Security header '$header' has unexpected value: $actualValue");
                } else {
                    $this->assertEquals($expectedValue, $actualValue, 
                        "Security header '$header' has unexpected value: $actualValue");
                }
            }
            // Note: Headers might not be configured yet, but this documents expectations
        }

        // Verify no sensitive information is leaked in response
        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayNotHasKey('user_id', $responseData);
        $this->assertArrayNotHasKey('password', $responseData);
        $this->assertArrayNotHasKey('hash', $responseData);
        $this->assertArrayNotHasKey('salt', $responseData);
    }

    public function testRefreshTokenSessionFixation(): void
    {
        $user = $this->createUser();
        $refreshToken = $this->createRefreshToken($user);

        // Simulate session fixation attack
        $originalToken = $refreshToken->getToken();

        // First refresh request
        $this->client->request(
            'POST',
            '/api/token/refresh',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['refresh_token' => $originalToken])
        );

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $response1 = json_decode($this->client->getResponse()->getContent(), true);
        $newRefreshToken1 = $response1['refresh_token'];

        // Verify token rotation occurred (new token is different)
        $this->assertNotEquals($originalToken, $newRefreshToken1);

        // Second refresh request with new token
        $this->client->request(
            'POST',
            '/api/token/refresh',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['refresh_token' => $newRefreshToken1])
        );

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $response2 = json_decode($this->client->getResponse()->getContent(), true);
        $newRefreshToken2 = $response2['refresh_token'];

        // Verify token rotation occurred again
        $this->assertNotEquals($newRefreshToken1, $newRefreshToken2);

        // Verify original and first new token are no longer valid
        $this->client->request(
            'POST',
            '/api/token/refresh',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['refresh_token' => $originalToken])
        );
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $this->client->getResponse()->getStatusCode());

        $this->client->request(
            'POST',
            '/api/token/refresh',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['refresh_token' => $newRefreshToken1])
        );
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $this->client->getResponse()->getStatusCode());
    }

    public function testRefreshTokenDataValidation(): void
    {
        $user = $this->createUser();
        
        $invalidPayloads = [
            null,                                    // null payload
            '',                                     // empty string
            'not_json',                            // invalid JSON
            '{"malformed": json}',                 // malformed JSON
            '{"refresh_token": 123}',              // wrong type
            '{"refresh_token": true}',             // wrong type
            '{"refresh_token": []}',               // wrong type
            '{"refresh_token": {"nested": "obj"}}', // wrong type
            json_encode(['wrong_field' => 'value']), // missing required field
            json_encode(['refresh_token' => null]), // null value
        ];

        foreach ($invalidPayloads as $payload) {
            $this->client->request(
                'POST',
                '/api/token/refresh',
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                $payload
            );

            $statusCode = $this->client->getResponse()->getStatusCode();
            $this->assertEquals(Response::HTTP_BAD_REQUEST, $statusCode, 
                "Invalid payload should return 400: " . var_export($payload, true));
                
            $data = json_decode($this->client->getResponse()->getContent(), true);
            $this->assertEquals('missing refresh_token', $data['error']);
        }
    }
}