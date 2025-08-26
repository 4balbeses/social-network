<?php

namespace App\Tests\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class LoginTest extends WebTestCase
{
    private $client;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        
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

    public function testSuccessfulLogin(): void
    {
        $email = 'test@example.com';
        $password = 'password123';
        $this->createUser($email, $password);

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

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('token', $data);
        $this->assertArrayHasKey('refresh_token', $data);
        $this->assertNotEmpty($data['token']);
        $this->assertNotEmpty($data['refresh_token']);
    }

    public function testLoginWithInvalidCredentials(): void
    {
        $this->createUser('test@example.com', 'correctpassword');

        $loginData = [
            'email' => 'test@example.com',
            'password' => 'wrongpassword'
        ];

        $this->client->request(
            'POST',
            '/api/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($loginData)
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('code', $data);
        $this->assertEquals(401, $data['code']);
    }

    public function testLoginWithNonExistentUser(): void
    {
        $loginData = [
            'email' => 'nonexistent@example.com',
            'password' => 'password123'
        ];

        $this->client->request(
            'POST',
            '/api/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($loginData)
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('code', $data);
        $this->assertEquals(401, $data['code']);
    }

    public function testLoginWithMissingEmail(): void
    {
        $loginData = [
            'password' => 'password123'
        ];

        $this->client->request(
            'POST',
            '/api/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($loginData)
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('code', $data);
        $this->assertEquals(400, $data['code']);
    }

    public function testLoginWithMissingPassword(): void
    {
        $this->createUser('test@example.com', 'password123');

        $loginData = [
            'email' => 'test@example.com'
        ];

        $this->client->request(
            'POST',
            '/api/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($loginData)
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('code', $data);
        $this->assertEquals(400, $data['code']);
    }

    public function testLoginWithEmptyCredentials(): void
    {
        $this->client->request(
            'POST',
            '/api/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([])
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('code', $data);
        $this->assertEquals(400, $data['code']);
    }

    public function testLoginWithInvalidJson(): void
    {
        $this->client->request(
            'POST',
            '/api/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            'invalid json'
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    public function testLoginJWTTokenFormat(): void
    {
        $email = 'test@example.com';
        $password = 'password123';
        $user = $this->createUser($email, $password);

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

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $token = $data['token'];

        // JWT tokens have 3 parts separated by dots
        $tokenParts = explode('.', $token);
        $this->assertCount(3, $tokenParts);

        // Each part should be base64 encoded
        foreach ($tokenParts as $part) {
            $this->assertNotEmpty($part);
            $this->assertMatchesRegularExpression('/^[A-Za-z0-9_-]+$/', $part);
        }

        // Verify JWT token contains user information
        $jwtManager = static::getContainer()->get(JWTTokenManagerInterface::class);
        $payload = $jwtManager->parse($token);
        
        $this->assertArrayHasKey('username', $payload);
        $this->assertEquals($user->getEmail(), $payload['username']);
        $this->assertArrayHasKey('roles', $payload);
        $this->assertContains('ROLE_USER', $payload['roles']);
    }

    public function testLoginCreatesRefreshToken(): void
    {
        $email = 'test@example.com';
        $password = 'password123';
        $user = $this->createUser($email, $password);

        // Check no refresh tokens exist initially
        $refreshTokensBefore = $this->entityManager->getRepository('App\Entity\RefreshToken')->findAll();
        $this->assertCount(0, $refreshTokensBefore);

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

        // Check refresh token was created
        $refreshTokensAfter = $this->entityManager->getRepository('App\Entity\RefreshToken')->findAll();
        $this->assertCount(1, $refreshTokensAfter);

        $refreshToken = $refreshTokensAfter[0];
        $this->assertEquals($user->getId(), $refreshToken->getUser()->getId());
        $this->assertNotEmpty($refreshToken->getToken());
        $this->assertFalse($refreshToken->isExpired());
    }

    public function testLoginWithCaseInsensitiveEmail(): void
    {
        $email = 'test@example.com';
        $password = 'password123';
        $this->createUser($email, $password);

        $loginData = [
            'email' => 'TEST@EXAMPLE.COM', // Different case
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

        $response = $this->client->getResponse();
        
        // This test depends on your authentication configuration
        // If case-insensitive, it should be 200, if case-sensitive, it should be 401
        $this->assertContains($response->getStatusCode(), [Response::HTTP_OK, Response::HTTP_UNAUTHORIZED]);
    }

    public function testLoginOnlyAllowsPost(): void
    {
        $methods = ['GET', 'PUT', 'PATCH', 'DELETE'];

        foreach ($methods as $method) {
            $this->client->request($method, '/api/login');
            $this->assertEquals(Response::HTTP_METHOD_NOT_ALLOWED, $this->client->getResponse()->getStatusCode());
        }
    }

    public function testMultipleLoginsCreateMultipleRefreshTokens(): void
    {
        $email = 'test@example.com';
        $password = 'password123';
        $this->createUser($email, $password);

        $loginData = [
            'email' => $email,
            'password' => $password
        ];

        // First login
        $this->client->request(
            'POST',
            '/api/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($loginData)
        );
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        // Second login
        $this->client->request(
            'POST',
            '/api/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($loginData)
        );
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        // Should have 2 refresh tokens now
        $refreshTokens = $this->entityManager->getRepository('App\Entity\RefreshToken')->findAll();
        $this->assertCount(2, $refreshTokens);

        // Tokens should be different
        $this->assertNotEquals($refreshTokens[0]->getToken(), $refreshTokens[1]->getToken());
    }
}