<?php

namespace App\Tests\Integration;

use App\Entity\RefreshToken;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RefreshTokenIntegrationTest extends WebTestCase
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

    private function loginUser(string $email, string $password): array
    {
        $loginData = [
            'email' => $email,
            'password' => $password
        ];

        $this->client->request(
            'POST',
            '/api/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($loginData)
        );

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        return json_decode($this->client->getResponse()->getContent(), true);
    }

    public function testFullAuthenticationFlowWithRefreshToken(): void
    {
        // Step 1: Create user and login
        $user = $this->createUser('test@example.com', 'password123');
        $loginResponse = $this->loginUser('test@example.com', 'password123');

        // Verify login response contains both tokens
        $this->assertArrayHasKey('token', $loginResponse);
        $this->assertArrayHasKey('refresh_token', $loginResponse);
        $this->assertNotEmpty($loginResponse['token']);
        $this->assertNotEmpty($loginResponse['refresh_token']);

        $accessToken = $loginResponse['token'];
        $refreshToken = $loginResponse['refresh_token'];

        // Step 2: Use access token to access protected endpoint
        $this->client->request(
            'GET',
            '/api/profile',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $accessToken
            ]
        );

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $profileData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('test@example.com', $profileData['email']);

        // Step 3: Use refresh token to get new access token
        $refreshData = ['refresh_token' => $refreshToken];
        $this->client->request(
            'POST',
            '/api/token/refresh',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($refreshData)
        );

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $refreshResponse = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('token', $refreshResponse);
        $this->assertArrayHasKey('refresh_token', $refreshResponse);
        $newAccessToken = $refreshResponse['token'];
        $newRefreshToken = $refreshResponse['refresh_token'];

        // New tokens should be different from original
        $this->assertNotEquals($accessToken, $newAccessToken);
        $this->assertNotEquals($refreshToken, $newRefreshToken);

        // Step 4: Use new access token to access protected endpoint
        $this->client->request(
            'GET',
            '/api/profile',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $newAccessToken
            ]
        );

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $newProfileData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('test@example.com', $newProfileData['email']);

        // Step 5: Verify old refresh token is no longer valid (token rotation)
        $oldRefreshData = ['refresh_token' => $refreshToken];
        $this->client->request(
            'POST',
            '/api/token/refresh',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($oldRefreshData)
        );

        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $this->client->getResponse()->getStatusCode());
    }

    public function testConcurrentRefreshTokenRequests(): void
    {
        $user = $this->createUser();
        $loginResponse = $this->loginUser('test@example.com', 'password123');
        $refreshToken = $loginResponse['refresh_token'];

        // Simulate concurrent requests by making two rapid refresh requests
        // In a real scenario, only one should succeed due to token rotation
        
        $requestData = ['refresh_token' => $refreshToken];

        // First request
        $this->client->request(
            'POST',
            '/api/token/refresh',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($requestData)
        );
        $firstResponse = $this->client->getResponse();
        $firstStatusCode = $firstResponse->getStatusCode();

        // Second request with the same token (should fail due to rotation)
        $this->client->request(
            'POST',
            '/api/token/refresh',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($requestData)
        );
        $secondResponse = $this->client->getResponse();
        $secondStatusCode = $secondResponse->getStatusCode();

        // First request should succeed, second should fail
        $this->assertEquals(Response::HTTP_OK, $firstStatusCode);
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $secondStatusCode);

        // Verify only one refresh token exists in database for this user
        $refreshTokens = $this->entityManager->getRepository(RefreshToken::class)
            ->findBy(['user' => $user]);
        $this->assertCount(1, $refreshTokens);
    }

    public function testRefreshTokenLifecycleWithMultipleLogins(): void
    {
        $user = $this->createUser();

        // First login
        $login1 = $this->loginUser('test@example.com', 'password123');
        $refreshToken1 = $login1['refresh_token'];

        // Verify one refresh token exists
        $tokens = $this->entityManager->getRepository(RefreshToken::class)->findBy(['user' => $user]);
        $this->assertCount(1, $tokens);

        // Second login (new session)
        $login2 = $this->loginUser('test@example.com', 'password123');
        $refreshToken2 = $login2['refresh_token'];

        // Verify two refresh tokens exist (multiple sessions allowed)
        $tokens = $this->entityManager->getRepository(RefreshToken::class)->findBy(['user' => $user]);
        $this->assertCount(2, $tokens);

        // Both refresh tokens should be valid
        $this->assertNotEquals($refreshToken1, $refreshToken2);

        // Use first refresh token
        $refreshData1 = ['refresh_token' => $refreshToken1];
        $this->client->request(
            'POST',
            '/api/token/refresh',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($refreshData1)
        );
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        // Use second refresh token
        $refreshData2 = ['refresh_token' => $refreshToken2];
        $this->client->request(
            'POST',
            '/api/token/refresh',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($refreshData2)
        );
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    public function testRefreshTokenCleanupOnExpiration(): void
    {
        $user = $this->createUser();

        // Create expired refresh token manually
        $expiredToken = new RefreshToken();
        $expiredToken->setUser($user);
        $expiredToken->setToken(bin2hex(random_bytes(64)));
        $expiredToken->setCreatedAt(new DateTimeImmutable('-31 days'));
        $expiredToken->setExpiresAt(new DateTimeImmutable('-1 day'));

        $this->entityManager->persist($expiredToken);
        $this->entityManager->flush();

        // Create valid refresh token
        $validToken = new RefreshToken();
        $validToken->setUser($user);
        $validToken->setToken(bin2hex(random_bytes(64)));
        $validToken->setCreatedAt(new DateTimeImmutable());
        $validToken->setExpiresAt(new DateTimeImmutable('+30 days'));

        $this->entityManager->persist($validToken);
        $this->entityManager->flush();

        // Verify both tokens exist
        $allTokens = $this->entityManager->getRepository(RefreshToken::class)->findAll();
        $this->assertCount(2, $allTokens);

        // Run cleanup
        $repository = $this->entityManager->getRepository(RefreshToken::class);
        $deletedCount = $repository->purgeExpired();

        // Verify cleanup removed expired token
        $this->assertEquals(1, $deletedCount);
        $remainingTokens = $this->entityManager->getRepository(RefreshToken::class)->findAll();
        $this->assertCount(1, $remainingTokens);
        $this->assertEquals($validToken->getToken(), $remainingTokens[0]->getToken());
    }

    public function testRefreshTokenSecurityValidation(): void
    {
        $user1 = $this->createUser('user1@example.com', 'password123');
        $user2 = $this->createUser('user2@example.com', 'password456');

        // Get refresh token for user1
        $login1 = $this->loginUser('user1@example.com', 'password123');
        $user1RefreshToken = $login1['refresh_token'];

        // Get refresh token for user2  
        $login2 = $this->loginUser('user2@example.com', 'password456');
        $user2RefreshToken = $login2['refresh_token'];

        // Try to use user1's token to refresh (should work)
        $refreshData1 = ['refresh_token' => $user1RefreshToken];
        $this->client->request(
            'POST',
            '/api/token/refresh',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($refreshData1)
        );
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $response1 = json_decode($this->client->getResponse()->getContent(), true);
        
        // Use new access token to get profile - should be user1
        $this->client->request(
            'GET',
            '/api/profile',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $response1['token']
            ]
        );
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $profile = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('user1@example.com', $profile['email']);

        // Try to use user2's token to refresh (should work)
        $refreshData2 = ['refresh_token' => $user2RefreshToken];
        $this->client->request(
            'POST',
            '/api/token/refresh',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($refreshData2)
        );
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $response2 = json_decode($this->client->getResponse()->getContent(), true);

        // Use new access token to get profile - should be user2
        $this->client->request(
            'GET',
            '/api/profile',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $response2['token']
            ]
        );
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $profile = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('user2@example.com', $profile['email']);

        // Verify tokens are different and properly isolated
        $this->assertNotEquals($response1['token'], $response2['token']);
        $this->assertNotEquals($response1['refresh_token'], $response2['refresh_token']);
    }

    public function testRefreshTokenSQLInjectionPrevention(): void
    {
        $user = $this->createUser();
        
        // Attempt SQL injection in refresh token
        $maliciousTokens = [
            "'; DROP TABLE refresh_tokens; --",
            "' OR 1=1 --",
            "'; UPDATE users SET password='hacked'; --",
            "' UNION SELECT * FROM users --",
            "\"; DROP TABLE refresh_tokens; --",
        ];

        foreach ($maliciousTokens as $maliciousToken) {
            $requestData = ['refresh_token' => $maliciousToken];
            
            $this->client->request(
                'POST',
                '/api/token/refresh',
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                json_encode($requestData)
            );

            // Should return unauthorized, not cause SQL errors
            $this->assertEquals(Response::HTTP_UNAUTHORIZED, $this->client->getResponse()->getStatusCode());
            
            $data = json_decode($this->client->getResponse()->getContent(), true);
            $this->assertEquals('invalid or expired refresh_token', $data['error']);
        }

        // Verify database integrity - user should still exist
        $this->entityManager->clear();
        $userExists = $this->entityManager->getRepository(User::class)->find($user->getId());
        $this->assertNotNull($userExists);
        $this->assertEquals('test@example.com', $userExists->getEmail());
    }

    public function testRefreshTokenLengthAndCharacterValidation(): void
    {
        $user = $this->createUser();
        
        $invalidTokens = [
            str_repeat('a', 64),   // Too short (64 chars instead of 128)
            str_repeat('a', 256),  // Too long (256 chars)
            'invalid-chars-!@#$%', // Invalid characters
            '',                    // Empty string
            '   ',                // Whitespace only
            'abc123',             // Too short and mixed case
        ];

        foreach ($invalidTokens as $invalidToken) {
            $requestData = ['refresh_token' => $invalidToken];
            
            $this->client->request(
                'POST',
                '/api/token/refresh',
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                json_encode($requestData)
            );

            $this->assertEquals(Response::HTTP_UNAUTHORIZED, $this->client->getResponse()->getStatusCode());
            
            $data = json_decode($this->client->getResponse()->getContent(), true);
            $this->assertEquals('invalid or expired refresh_token', $data['error']);
        }
    }

    public function testRefreshTokenRateLimiting(): void
    {
        $user = $this->createUser();
        $loginResponse = $this->loginUser('test@example.com', 'password123');
        
        // Make multiple rapid refresh requests
        $refreshCount = 0;
        $successCount = 0;
        $currentRefreshToken = $loginResponse['refresh_token'];

        for ($i = 0; $i < 5; $i++) {
            $requestData = ['refresh_token' => $currentRefreshToken];
            
            $this->client->request(
                'POST',
                '/api/token/refresh',
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                json_encode($requestData)
            );

            $refreshCount++;
            $statusCode = $this->client->getResponse()->getStatusCode();
            
            if ($statusCode === Response::HTTP_OK) {
                $successCount++;
                $response = json_decode($this->client->getResponse()->getContent(), true);
                $currentRefreshToken = $response['refresh_token'];
            } else {
                // After first successful refresh, subsequent requests with old token should fail
                $this->assertEquals(Response::HTTP_UNAUTHORIZED, $statusCode);
            }
        }

        // Only first refresh should succeed due to token rotation
        $this->assertEquals(5, $refreshCount);
        $this->assertEquals(1, $successCount);
    }
}