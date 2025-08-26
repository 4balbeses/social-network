<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AuthControllerTest extends WebTestCase
{
    private $client;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        
        // Clean up database before each test
        $this->truncateEntities([
            User::class,
            'App\Entity\RefreshToken'
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

    public function testUserRegistrationSuccess(): void
    {
        $userData = [
            'email' => 'test@example.com',
            'password' => 'testpassword123'
        ];

        $this->client->request(
            'POST',
            '/api/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($userData)
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals('Registered', $data['message']);

        // Verify user was created in database
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $userData['email']]);
        $this->assertNotNull($user);
        $this->assertEquals($userData['email'], $user->getEmail());
        $this->assertTrue(in_array('ROLE_USER', $user->getRoles()));
    }

    public function testUserRegistrationWithInvalidEmail(): void
    {
        $userData = [
            'email' => 'invalid-email',
            'password' => 'testpassword123'
        ];

        $this->client->request(
            'POST',
            '/api/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($userData)
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals('Invalid email or short password', $data['error']);
    }

    public function testUserRegistrationWithShortPassword(): void
    {
        $userData = [
            'email' => 'test@example.com',
            'password' => '12345' // Too short
        ];

        $this->client->request(
            'POST',
            '/api/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($userData)
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals('Invalid email or short password', $data['error']);
    }

    public function testUserRegistrationWithMissingFields(): void
    {
        $userData = [
            'email' => 'test@example.com'
            // Missing password
        ];

        $this->client->request(
            'POST',
            '/api/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($userData)
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals('Invalid email or short password', $data['error']);
    }

    public function testUserRegistrationDuplicateEmail(): void
    {
        // First registration
        $userData = [
            'email' => 'test@example.com',
            'password' => 'testpassword123'
        ];

        $this->client->request(
            'POST',
            '/api/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($userData)
        );

        $this->assertEquals(Response::HTTP_CREATED, $this->client->getResponse()->getStatusCode());

        // Second registration with same email
        $this->client->request(
            'POST',
            '/api/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($userData)
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_CONFLICT, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals('User already exists', $data['error']);
    }

    public function testUserRegistrationInvalidJson(): void
    {
        $this->client->request(
            'POST',
            '/api/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            'invalid json'
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testUserRegistrationEmptyRequest(): void
    {
        $this->client->request(
            'POST',
            '/api/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([])
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals('Invalid email or short password', $data['error']);
    }

    public function testPasswordHashingCorrectly(): void
    {
        $userData = [
            'email' => 'test@example.com',
            'password' => 'testpassword123'
        ];

        $this->client->request(
            'POST',
            '/api/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($userData)
        );

        $this->assertEquals(Response::HTTP_CREATED, $this->client->getResponse()->getStatusCode());

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $userData['email']]);
        $hasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        
        // Password should be hashed, not stored in plain text
        $this->assertNotEquals($userData['password'], $user->getPassword());
        // But the hasher should be able to verify it
        $this->assertTrue($hasher->isPasswordValid($user, $userData['password']));
    }

    public function testEmailTrimming(): void
    {
        $userData = [
            'email' => '  test@example.com  ', // With spaces
            'password' => 'testpassword123'
        ];

        $this->client->request(
            'POST',
            '/api/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($userData)
        );

        $this->assertEquals(Response::HTTP_CREATED, $this->client->getResponse()->getStatusCode());

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => 'test@example.com']);
        $this->assertNotNull($user);
        $this->assertEquals('test@example.com', $user->getEmail()); // Should be trimmed
    }

    public function testUserHasCorrectRoles(): void
    {
        $userData = [
            'email' => 'test@example.com',
            'password' => 'testpassword123'
        ];

        $this->client->request(
            'POST',
            '/api/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($userData)
        );

        $this->assertEquals(Response::HTTP_CREATED, $this->client->getResponse()->getStatusCode());

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $userData['email']]);
        $this->assertContains('ROLE_USER', $user->getRoles());
        $this->assertCount(1, $user->getRoles()); // Should only have ROLE_USER
    }

    public function testLoginEndpointThrowsLogicException(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Handled by the security system.');

        $this->client->request(
            'POST',
            '/api/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['email' => 'test@example.com', 'password' => 'password'])
        );
    }

    /**
     * Test that non-POST methods are not allowed
     */
    public function testRegistrationOnlyAllowsPost(): void
    {
        $methods = ['GET', 'PUT', 'PATCH', 'DELETE'];

        foreach ($methods as $method) {
            $this->client->request($method, '/api/register');
            $this->assertEquals(Response::HTTP_METHOD_NOT_ALLOWED, $this->client->getResponse()->getStatusCode());
        }
    }

    public function testLoginOnlyAllowsPost(): void
    {
        $methods = ['GET', 'PUT', 'PATCH', 'DELETE'];

        foreach ($methods as $method) {
            $this->client->request($method, '/api/login');
            $this->assertEquals(Response::HTTP_METHOD_NOT_ALLOWED, $this->client->getResponse()->getStatusCode());
        }
    }
}