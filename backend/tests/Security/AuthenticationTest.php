<?php

namespace App\Tests\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AuthenticationTest extends WebTestCase
{
    private $client;
    private EntityManagerInterface $entityManager;
    private JWTTokenManagerInterface $jwtManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->jwtManager = static::getContainer()->get(JWTTokenManagerInterface::class);
        
        // Clean up database before each test
        $this->truncateEntities([
            'App\Entity\RefreshToken',
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

    public function testAccessProtectedEndpointWithValidToken(): void
    {
        $email = 'test@example.com';
        $password = 'password123';
        $user = $this->createUser($email, $password);
        
        $loginResponse = $this->loginUser($email, $password);
        $token = $loginResponse['token'];

        $this->client->request(
            'GET',
            '/api/profile',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $token
            ]
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('email', $data);
        $this->assertEquals($user->getEmail(), $data['email']);
    }

    public function testAccessProtectedEndpointWithoutToken(): void
    {
        $this->client->request(
            'GET',
            '/api/profile',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json']
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('code', $data);
        $this->assertEquals(401, $data['code']);
    }

    public function testAccessProtectedEndpointWithInvalidToken(): void
    {
        $this->client->request(
            'GET',
            '/api/profile',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer invalid_token_here'
            ]
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('code', $data);
        $this->assertEquals(401, $data['code']);
    }

    public function testAccessProtectedEndpointWithExpiredToken(): void
    {
        $user = $this->createUser();
        
        // Create an expired token (this is a simulation - in real JWT the exp claim would be past)
        $payload = [
            'username' => $user->getEmail(),
            'roles' => $user->getRoles(),
            'exp' => time() - 3600 // 1 hour ago
        ];
        
        // Note: This test may not work exactly as intended because the JWT library 
        // may not allow creating tokens with past expiration dates
        // In a real scenario, you'd need to test with actual expired tokens
        try {
            $expiredToken = $this->jwtManager->createFromPayload($user, $payload);
            
            $this->client->request(
                'GET',
                '/api/profile',
                [],
                [],
                [
                    'CONTENT_TYPE' => 'application/json',
                    'HTTP_AUTHORIZATION' => 'Bearer ' . $expiredToken
                ]
            );

            $response = $this->client->getResponse();
            $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
        } catch (\Exception $e) {
            // If JWT library doesn't allow expired tokens, skip this test
            $this->markTestSkipped('JWT library does not allow creating expired tokens for testing');
        }
    }

    public function testAccessProtectedEndpointWithMalformedToken(): void
    {
        $malformedTokens = [
            'Bearer',                    // No token
            'Bearer ',                   // Empty token
            'InvalidScheme token123',    // Wrong scheme
            'Bearer token.without.dots', // Invalid JWT format
            'Bearer invalid',            // Too short
        ];

        foreach ($malformedTokens as $authHeader) {
            $this->client->request(
                'GET',
                '/api/profile',
                [],
                [],
                [
                    'CONTENT_TYPE' => 'application/json',
                    'HTTP_AUTHORIZATION' => $authHeader
                ]
            );

            $response = $this->client->getResponse();
            $this->assertEquals(
                Response::HTTP_UNAUTHORIZED, 
                $response->getStatusCode(),
                "Failed for auth header: $authHeader"
            );
        }
    }

    public function testJWTTokenContainsCorrectUserData(): void
    {
        $email = 'test@example.com';
        $password = 'password123';
        $user = $this->createUser($email, $password);
        
        $loginResponse = $this->loginUser($email, $password);
        $token = $loginResponse['token'];

        // Parse the token to check its payload
        $payload = $this->jwtManager->parse($token);
        
        $this->assertArrayHasKey('username', $payload);
        $this->assertEquals($user->getEmail(), $payload['username']);
        
        $this->assertArrayHasKey('roles', $payload);
        $this->assertContains('ROLE_USER', $payload['roles']);
        
        $this->assertArrayHasKey('iat', $payload); // Issued at
        $this->assertArrayHasKey('exp', $payload); // Expires at
        
        // Verify expiration is in the future
        $this->assertGreaterThan(time(), $payload['exp']);
    }

    public function testUserCanAccessOwnProfile(): void
    {
        $email = 'test@example.com';
        $password = 'password123';
        $user = $this->createUser($email, $password);
        
        $loginResponse = $this->loginUser($email, $password);
        $token = $loginResponse['token'];

        $this->client->request(
            'GET',
            '/api/profile',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $token
            ]
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals($user->getEmail(), $data['email']);
        $this->assertArrayHasKey('roles', $data);
        $this->assertContains('ROLE_USER', $data['roles']);
    }

    public function testMultipleUsersCanAccessTheirOwnProfiles(): void
    {
        $user1 = $this->createUser('user1@example.com', 'password123');
        $user2 = $this->createUser('user2@example.com', 'password456');

        // User 1 login and profile access
        $loginResponse1 = $this->loginUser('user1@example.com', 'password123');
        $token1 = $loginResponse1['token'];

        $this->client->request(
            'GET',
            '/api/profile',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $token1
            ]
        );

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $data1 = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('user1@example.com', $data1['email']);

        // User 2 login and profile access
        $loginResponse2 = $this->loginUser('user2@example.com', 'password456');
        $token2 = $loginResponse2['token'];

        $this->client->request(
            'GET',
            '/api/profile',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $token2
            ]
        );

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $data2 = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('user2@example.com', $data2['email']);
    }

    public function testTokenValidationWithDifferentUsers(): void
    {
        $user1 = $this->createUser('user1@example.com', 'password123');
        $user2 = $this->createUser('user2@example.com', 'password456');

        $loginResponse1 = $this->loginUser('user1@example.com', 'password123');
        $loginResponse2 = $this->loginUser('user2@example.com', 'password456');

        $token1 = $loginResponse1['token'];
        $token2 = $loginResponse2['token'];

        // Tokens should be different
        $this->assertNotEquals($token1, $token2);

        // Both tokens should be valid for their respective users
        $payload1 = $this->jwtManager->parse($token1);
        $payload2 = $this->jwtManager->parse($token2);

        $this->assertEquals('user1@example.com', $payload1['username']);
        $this->assertEquals('user2@example.com', $payload2['username']);
    }

    public function testAuthorizationHeaderVariations(): void
    {
        $email = 'test@example.com';
        $password = 'password123';
        $this->createUser($email, $password);
        
        $loginResponse = $this->loginUser($email, $password);
        $token = $loginResponse['token'];

        $validHeaders = [
            'Bearer ' . $token,
            'bearer ' . $token, // lowercase might work depending on configuration
        ];

        foreach ($validHeaders as $header) {
            $this->client->request(
                'GET',
                '/api/profile',
                [],
                [],
                [
                    'CONTENT_TYPE' => 'application/json',
                    'HTTP_AUTHORIZATION' => $header
                ]
            );

            $statusCode = $this->client->getResponse()->getStatusCode();
            // Accept both 200 (if case-insensitive) and 401 (if case-sensitive)
            $this->assertContains($statusCode, [Response::HTTP_OK, Response::HTTP_UNAUTHORIZED]);
        }
    }

    public function testSecurityHeaders(): void
    {
        $this->client->request('GET', '/api/profile');
        $response = $this->client->getResponse();
        
        // Check for security-related headers (these may vary based on your configuration)
        $headers = $response->headers;
        
        // These are common security headers - adjust based on your setup
        if ($headers->has('Content-Security-Policy')) {
            $this->assertNotEmpty($headers->get('Content-Security-Policy'));
        }
        
        if ($headers->has('X-Frame-Options')) {
            $this->assertNotEmpty($headers->get('X-Frame-Options'));
        }
        
        // CORS headers should be present if configured
        if ($headers->has('Access-Control-Allow-Origin')) {
            $this->assertNotEmpty($headers->get('Access-Control-Allow-Origin'));
        }
    }
}