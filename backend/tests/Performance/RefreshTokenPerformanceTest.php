<?php

namespace App\Tests\Performance;

use App\Entity\RefreshToken;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RefreshTokenPerformanceTest extends WebTestCase
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

    public function testRefreshTokenResponseTime(): void
    {
        $user = $this->createUser();
        $refreshToken = $this->createRefreshToken($user);

        $requestData = ['refresh_token' => $refreshToken->getToken()];
        
        // Measure response time for refresh token request
        $startTime = microtime(true);
        
        $this->client->request(
            'POST',
            '/api/token/refresh',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($requestData)
        );
        
        $endTime = microtime(true);
        $responseTime = $endTime - $startTime;

        // Response should be fast (under 200ms for simple refresh)
        $this->assertLessThan(0.2, $responseTime, 
            "Refresh token request took too long: {$responseTime}s");

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('token', $data);
        $this->assertArrayHasKey('refresh_token', $data);
    }

    public function testPurgeExpiredTokensPerformance(): void
    {
        $user = $this->createUser();
        
        // Create many expired tokens to test purge performance
        $expiredTokenCount = 100;
        for ($i = 0; $i < $expiredTokenCount; $i++) {
            $this->createRefreshToken($user, true); // expired
        }
        
        // Create some valid tokens
        $validTokenCount = 10;
        for ($i = 0; $i < $validTokenCount; $i++) {
            $this->createRefreshToken($user, false); // valid
        }

        $repository = $this->entityManager->getRepository(RefreshToken::class);
        
        // Verify all tokens exist
        $totalTokens = $repository->findAll();
        $this->assertCount($expiredTokenCount + $validTokenCount, $totalTokens);

        // Measure purge performance
        $startTime = microtime(true);
        $deletedCount = $repository->purgeExpired();
        $endTime = microtime(true);
        
        $purgeTime = $endTime - $startTime;
        
        // Purge should be fast even with many tokens (under 1 second)
        $this->assertLessThan(1.0, $purgeTime, 
            "Purge expired tokens took too long: {$purgeTime}s for {$expiredTokenCount} expired tokens");

        // Verify correct number of tokens were deleted
        $this->assertEquals($expiredTokenCount, $deletedCount);
        
        // Verify only valid tokens remain
        $remainingTokens = $repository->findAll();
        $this->assertCount($validTokenCount, $remainingTokens);
    }

    public function testConcurrentRefreshTokenRequests(): void
    {
        $user = $this->createUser();
        
        // Create multiple refresh tokens for concurrent testing
        $tokenCount = 5;
        $refreshTokens = [];
        
        for ($i = 0; $i < $tokenCount; $i++) {
            $refreshTokens[] = $this->createRefreshToken($user);
        }

        $results = [];
        $startTime = microtime(true);

        // Simulate concurrent requests by making multiple rapid requests
        foreach ($refreshTokens as $index => $refreshToken) {
            $requestData = ['refresh_token' => $refreshToken->getToken()];
            
            $requestStart = microtime(true);
            
            $this->client->request(
                'POST',
                '/api/token/refresh',
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                json_encode($requestData)
            );
            
            $requestEnd = microtime(true);
            
            $results[] = [
                'index' => $index,
                'status' => $this->client->getResponse()->getStatusCode(),
                'time' => $requestEnd - $requestStart,
                'content' => json_decode($this->client->getResponse()->getContent(), true)
            ];
        }
        
        $totalTime = microtime(true) - $startTime;

        // Verify all requests completed successfully
        $successCount = 0;
        $maxRequestTime = 0;
        
        foreach ($results as $result) {
            if ($result['status'] === Response::HTTP_OK) {
                $successCount++;
                $this->assertArrayHasKey('token', $result['content']);
                $this->assertArrayHasKey('refresh_token', $result['content']);
            }
            
            $maxRequestTime = max($maxRequestTime, $result['time']);
        }

        // All requests should succeed
        $this->assertEquals($tokenCount, $successCount);
        
        // Total time should be reasonable
        $this->assertLessThan(2.0, $totalTime, 
            "Concurrent requests took too long: {$totalTime}s");
            
        // Individual requests should be fast
        $this->assertLessThan(0.5, $maxRequestTime, 
            "Slowest individual request took too long: {$maxRequestTime}s");
    }

    public function testRefreshTokenDatabaseIndexPerformance(): void
    {
        $user = $this->createUser();
        
        // Create many refresh tokens to test database query performance
        $tokenCount = 500;
        $tokens = [];
        
        for ($i = 0; $i < $tokenCount; $i++) {
            $token = $this->createRefreshToken($user);
            $tokens[] = $token->getToken();
        }

        $repository = $this->entityManager->getRepository(RefreshToken::class);

        // Test token lookup performance (should use index)
        $randomToken = $tokens[array_rand($tokens)];
        
        $startTime = microtime(true);
        $foundToken = $repository->findOneBy(['token' => $randomToken]);
        $endTime = microtime(true);
        
        $lookupTime = $endTime - $startTime;

        // Token lookup should be very fast due to unique index
        $this->assertLessThan(0.01, $lookupTime, 
            "Token lookup took too long: {$lookupTime}s with {$tokenCount} tokens");

        $this->assertNotNull($foundToken);
        $this->assertEquals($randomToken, $foundToken->getToken());

        // Test non-existent token lookup (should also be fast)
        $nonExistentToken = bin2hex(random_bytes(64));
        
        $startTime = microtime(true);
        $notFoundToken = $repository->findOneBy(['token' => $nonExistentToken]);
        $endTime = microtime(true);
        
        $notFoundLookupTime = $endTime - $startTime;

        // Non-existent token lookup should also be fast
        $this->assertLessThan(0.01, $notFoundLookupTime, 
            "Non-existent token lookup took too long: {$notFoundLookupTime}s");

        $this->assertNull($notFoundToken);
    }

    public function testRefreshTokenMemoryUsage(): void
    {
        $user = $this->createUser();
        $initialMemory = memory_get_usage();

        // Create and use refresh tokens to test memory usage
        $tokenCount = 50;
        
        for ($i = 0; $i < $tokenCount; $i++) {
            $refreshToken = $this->createRefreshToken($user);
            
            $requestData = ['refresh_token' => $refreshToken->getToken()];
            
            $this->client->request(
                'POST',
                '/api/token/refresh',
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                json_encode($requestData)
            );
            
            $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
            
            // Clear entity manager periodically to prevent memory buildup
            if ($i % 10 === 0) {
                $this->entityManager->clear();
            }
        }

        $finalMemory = memory_get_usage();
        $memoryIncrease = $finalMemory - $initialMemory;

        // Memory increase should be reasonable (less than 10MB for 50 operations)
        $maxMemoryIncrease = 10 * 1024 * 1024; // 10MB
        $this->assertLessThan($maxMemoryIncrease, $memoryIncrease,
            "Memory usage increased too much: " . number_format($memoryIncrease / 1024 / 1024, 2) . "MB");
    }

    public function testTokenGenerationPerformance(): void
    {
        $user = $this->createUser();
        
        // Test token generation performance
        $tokenCount = 100;
        $startTime = microtime(true);
        
        for ($i = 0; $i < $tokenCount; $i++) {
            // Generate token the same way as the application
            $token = bin2hex(random_bytes(64));
            
            // Verify token properties
            $this->assertEquals(128, strlen($token));
            $this->assertMatchesRegularExpression('/^[a-f0-9]{128}$/', $token);
        }
        
        $endTime = microtime(true);
        $generationTime = $endTime - $startTime;
        
        // Token generation should be very fast
        $this->assertLessThan(0.1, $generationTime,
            "Token generation took too long: {$generationTime}s for {$tokenCount} tokens");
            
        $averageTimePerToken = $generationTime / $tokenCount;
        $this->assertLessThan(0.001, $averageTimePerToken,
            "Average time per token too high: {$averageTimePerToken}s");
    }

    public function testBulkTokenOperationsPerformance(): void
    {
        $userCount = 10;
        $tokensPerUser = 20;
        
        // Create multiple users
        $users = [];
        for ($i = 0; $i < $userCount; $i++) {
            $users[] = $this->createUser("user{$i}@example.com");
        }

        $startTime = microtime(true);

        // Create bulk refresh tokens
        $totalTokens = 0;
        foreach ($users as $user) {
            for ($j = 0; $j < $tokensPerUser; $j++) {
                $this->createRefreshToken($user);
                $totalTokens++;
            }
        }

        $creationTime = microtime(true) - $startTime;

        // Bulk token creation should be reasonably fast
        $this->assertLessThan(5.0, $creationTime,
            "Bulk token creation took too long: {$creationTime}s for {$totalTokens} tokens");

        // Test bulk purge performance
        $repository = $this->entityManager->getRepository(RefreshToken::class);
        
        // Make half the tokens expired
        $allTokens = $repository->findAll();
        $expiredCount = 0;
        foreach ($allTokens as $index => $token) {
            if ($index % 2 === 0) {
                $token->setExpiresAt(new DateTimeImmutable('-1 day'));
                $this->entityManager->persist($token);
                $expiredCount++;
            }
        }
        $this->entityManager->flush();

        $purgeStart = microtime(true);
        $deletedCount = $repository->purgeExpired();
        $purgeTime = microtime(true) - $purgeStart;

        $this->assertEquals($expiredCount, $deletedCount);
        $this->assertLessThan(1.0, $purgeTime,
            "Bulk purge took too long: {$purgeTime}s for {$expiredCount} expired tokens");
    }

    public function testRefreshTokenRequestThroughput(): void
    {
        $user = $this->createUser();
        $requestCount = 20;
        $refreshTokens = [];

        // Pre-create refresh tokens
        for ($i = 0; $i < $requestCount; $i++) {
            $refreshTokens[] = $this->createRefreshToken($user);
        }

        $startTime = microtime(true);
        $successfulRequests = 0;

        // Make rapid requests to test throughput
        foreach ($refreshTokens as $refreshToken) {
            $requestData = ['refresh_token' => $refreshToken->getToken()];
            
            $this->client->request(
                'POST',
                '/api/token/refresh',
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                json_encode($requestData)
            );

            if ($this->client->getResponse()->getStatusCode() === Response::HTTP_OK) {
                $successfulRequests++;
            }
        }

        $totalTime = microtime(true) - $startTime;
        $requestsPerSecond = $requestCount / $totalTime;

        // Should handle at least 10 requests per second
        $this->assertGreaterThan(10, $requestsPerSecond,
            "Refresh token throughput too low: {$requestsPerSecond} requests/second");

        // All requests should succeed
        $this->assertEquals($requestCount, $successfulRequests);
    }
}