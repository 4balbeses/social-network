<?php

namespace App\Tests\Controller;

use App\Entity\RefreshToken;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class TokenControllerTest extends WebTestCase
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
            $refreshToken->setExpiresAt((new DateTimeImmutable())->modify("-1 day"));
        } else {
            $refreshToken->setExpiresAt((new DateTimeImmutable())->modify("+30 days"));
        }

        $this->entityManager->persist($refreshToken);
        $this->entityManager->flush();

        return $refreshToken;
    }

    public function testTokenRefreshSuccess(): void
    {
        $user = $this->createUser();
        $refreshToken = $this->createRefreshToken($user);

        $requestData = [
            'refresh_token' => $refreshToken->getToken()
        ];

        $this->client->request(
            'POST',
            '/api/token/refresh',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($requestData)
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('token', $data);
        $this->assertArrayHasKey('refresh_token', $data);
        $this->assertNotEmpty($data['token']);
        $this->assertNotEmpty($data['refresh_token']);
        
        // New refresh token should be different from the old one
        $this->assertNotEquals($refreshToken->getToken(), $data['refresh_token']);
    }

    public function testTokenRefreshRotation(): void
    {
        $user = $this->createUser();
        $refreshToken = $this->createRefreshToken($user);
        $oldToken = $refreshToken->getToken();

        $requestData = [
            'refresh_token' => $oldToken
        ];

        $this->client->request(
            'POST',
            '/api/token/refresh',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($requestData)
        );

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        // Old token should be removed from database
        $oldTokenEntity = $this->entityManager->getRepository(RefreshToken::class)
            ->findOneBy(['token' => $oldToken]);
        $this->assertNull($oldTokenEntity);

        // New token should exist
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $newTokenEntity = $this->entityManager->getRepository(RefreshToken::class)
            ->findOneBy(['token' => $data['refresh_token']]);
        $this->assertNotNull($newTokenEntity);
        $this->assertEquals($user->getId(), $newTokenEntity->getUser()->getId());
    }

    public function testTokenRefreshWithMissingToken(): void
    {
        $this->client->request(
            'POST',
            '/api/token/refresh',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([])
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals('missing refresh_token', $data['error']);
    }

    public function testTokenRefreshWithInvalidToken(): void
    {
        $requestData = [
            'refresh_token' => 'invalid_token_12345'
        ];

        $this->client->request(
            'POST',
            '/api/token/refresh',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($requestData)
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals('invalid or expired refresh_token', $data['error']);
    }

    public function testTokenRefreshWithExpiredToken(): void
    {
        $user = $this->createUser();
        $expiredToken = $this->createRefreshToken($user, true); // Create expired token

        $requestData = [
            'refresh_token' => $expiredToken->getToken()
        ];

        $this->client->request(
            'POST',
            '/api/token/refresh',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($requestData)
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals('invalid or expired refresh_token', $data['error']);
    }

    public function testTokenRefreshWithNullTokenValue(): void
    {
        $requestData = [
            'refresh_token' => null
        ];

        $this->client->request(
            'POST',
            '/api/token/refresh',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($requestData)
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals('missing refresh_token', $data['error']);
    }

    public function testTokenRefreshWithEmptyTokenValue(): void
    {
        $requestData = [
            'refresh_token' => ''
        ];

        $this->client->request(
            'POST',
            '/api/token/refresh',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($requestData)
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals('missing refresh_token', $data['error']);
    }

    public function testTokenRefreshNewTokenHasCorrectExpiration(): void
    {
        $user = $this->createUser();
        $refreshToken = $this->createRefreshToken($user);

        $requestData = [
            'refresh_token' => $refreshToken->getToken()
        ];

        $this->client->request(
            'POST',
            '/api/token/refresh',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($requestData)
        );

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $newTokenEntity = $this->entityManager->getRepository(RefreshToken::class)
            ->findOneBy(['token' => $data['refresh_token']]);

        $this->assertNotNull($newTokenEntity);
        
        // Check that the new token expires in approximately 30 days
        $now = new DateTimeImmutable();
        $expectedExpiry = $now->modify('+30 days');
        $actualExpiry = $newTokenEntity->getExpiresAt();
        
        $diff = $actualExpiry->getTimestamp() - $expectedExpiry->getTimestamp();
        $this->assertLessThan(60, abs($diff)); // Should be within 60 seconds of expected
    }

    public function testTokenRefreshGeneratesUniqueToken(): void
    {
        $user = $this->createUser();
        $refreshToken1 = $this->createRefreshToken($user);
        $refreshToken2 = $this->createRefreshToken($user);

        // Tokens should be unique
        $this->assertNotEquals($refreshToken1->getToken(), $refreshToken2->getToken());
        
        // Token length should be consistent (128 hex chars = 64 bytes * 2)
        $this->assertEquals(128, strlen($refreshToken1->getToken()));
        $this->assertEquals(128, strlen($refreshToken2->getToken()));
    }

    public function testTokenRefreshInvalidJson(): void
    {
        $this->client->request(
            'POST',
            '/api/token/refresh',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            'invalid json'
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals('missing refresh_token', $data['error']);
    }

    public function testTokenRefreshOnlyAllowsPost(): void
    {
        $methods = ['GET', 'PUT', 'PATCH', 'DELETE'];

        foreach ($methods as $method) {
            $this->client->request($method, '/api/token/refresh');
            $this->assertEquals(Response::HTTP_METHOD_NOT_ALLOWED, $this->client->getResponse()->getStatusCode());
        }
    }

    public function testMultipleRefreshTokensPerUser(): void
    {
        $user = $this->createUser();
        $token1 = $this->createRefreshToken($user);
        $token2 = $this->createRefreshToken($user);

        // Both tokens should work initially
        $requestData1 = ['refresh_token' => $token1->getToken()];
        $this->client->request(
            'POST',
            '/api/token/refresh',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($requestData1)
        );
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $requestData2 = ['refresh_token' => $token2->getToken()];
        $this->client->request(
            'POST',
            '/api/token/refresh',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($requestData2)
        );
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }
}