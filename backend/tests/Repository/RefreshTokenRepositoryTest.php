<?php

namespace App\Tests\Repository;

use App\Entity\RefreshToken;
use App\Entity\User;
use App\Repository\RefreshTokenRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RefreshTokenRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private RefreshTokenRepository $repository;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()->get(EntityManagerInterface::class);
        $this->repository = $this->entityManager->getRepository(RefreshToken::class);
        
        // Clean up database before each test
        $this->truncateEntities([RefreshToken::class, User::class]);
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

    private function createUser(string $email = 'test@example.com'): User
    {
        $user = new User();
        $user->setEmail($email);
        
        $hasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        $user->setPassword($hasher->hashPassword($user, 'password123'));
        $user->setRoles(['ROLE_USER']);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    private function createRefreshToken(User $user, bool $expired = false, int $daysOffset = 0): RefreshToken
    {
        $refreshToken = new RefreshToken();
        $refreshToken->setUser($user);
        $refreshToken->setToken(bin2hex(random_bytes(64)));
        $refreshToken->setCreatedAt(new DateTimeImmutable());
        
        if ($expired) {
            $refreshToken->setExpiresAt((new DateTimeImmutable())->modify("-1 day"));
        } else {
            $expiryModifier = $daysOffset !== 0 ? sprintf("%+d days", $daysOffset) : "+30 days";
            $refreshToken->setExpiresAt((new DateTimeImmutable())->modify($expiryModifier));
        }

        $this->entityManager->persist($refreshToken);
        $this->entityManager->flush();

        return $refreshToken;
    }

    public function testPurgeExpiredRemovesExpiredTokens(): void
    {
        $user = $this->createUser();
        
        // Create expired tokens
        $expiredToken1 = $this->createRefreshToken($user, true);
        $expiredToken2 = $this->createRefreshToken($user, true);
        
        // Create valid token
        $validToken = $this->createRefreshToken($user, false);

        // Verify all tokens exist before purge
        $allTokens = $this->repository->findAll();
        $this->assertCount(3, $allTokens);

        // Purge expired tokens
        $deletedCount = $this->repository->purgeExpired();
        
        // Verify 2 tokens were deleted
        $this->assertEquals(2, $deletedCount);
        
        // Verify only valid token remains
        $remainingTokens = $this->repository->findAll();
        $this->assertCount(1, $remainingTokens);
        $this->assertEquals($validToken->getToken(), $remainingTokens[0]->getToken());
    }

    public function testPurgeExpiredWithNoExpiredTokens(): void
    {
        $user = $this->createUser();
        
        // Create only valid tokens
        $this->createRefreshToken($user, false);
        $this->createRefreshToken($user, false);

        // Verify tokens exist
        $this->assertCount(2, $this->repository->findAll());

        // Purge expired tokens
        $deletedCount = $this->repository->purgeExpired();
        
        // Verify no tokens were deleted
        $this->assertEquals(0, $deletedCount);
        $this->assertCount(2, $this->repository->findAll());
    }

    public function testPurgeExpiredWithEmptyDatabase(): void
    {
        // Purge with no tokens in database
        $deletedCount = $this->repository->purgeExpired();
        
        $this->assertEquals(0, $deletedCount);
        $this->assertCount(0, $this->repository->findAll());
    }

    public function testPurgeExpiredHandlesTokensExpiringToday(): void
    {
        $user = $this->createUser();
        
        // Create token that expires right now
        $nowToken = new RefreshToken();
        $nowToken->setUser($user);
        $nowToken->setToken(bin2hex(random_bytes(64)));
        $nowToken->setCreatedAt(new DateTimeImmutable());
        $nowToken->setExpiresAt(new DateTimeImmutable()); // Expires exactly now
        
        $this->entityManager->persist($nowToken);
        $this->entityManager->flush();

        // Sleep for 1 second to ensure token is expired
        sleep(1);

        $deletedCount = $this->repository->purgeExpired();
        
        $this->assertEquals(1, $deletedCount);
        $this->assertCount(0, $this->repository->findAll());
    }

    public function testPurgeExpiredWithMixedExpirationDates(): void
    {
        $user = $this->createUser();
        
        // Create tokens with different expiration dates
        $expiredYesterday = $this->createRefreshToken($user, false, -1);
        $expiresIn5Days = $this->createRefreshToken($user, false, 5);
        $expiresIn10Days = $this->createRefreshToken($user, false, 10);
        $expiredLastWeek = $this->createRefreshToken($user, false, -7);

        $this->assertCount(4, $this->repository->findAll());

        $deletedCount = $this->repository->purgeExpired();
        
        // Should delete the 2 expired tokens
        $this->assertEquals(2, $deletedCount);
        
        // Verify remaining tokens are the valid ones
        $remainingTokens = $this->repository->findAll();
        $this->assertCount(2, $remainingTokens);
        
        $remainingTokenValues = array_map(fn($token) => $token->getToken(), $remainingTokens);
        $this->assertContains($expiresIn5Days->getToken(), $remainingTokenValues);
        $this->assertContains($expiresIn10Days->getToken(), $remainingTokenValues);
    }

    public function testPurgeExpiredAcrossMultipleUsers(): void
    {
        $user1 = $this->createUser('user1@example.com');
        $user2 = $this->createUser('user2@example.com');
        
        // Create expired and valid tokens for both users
        $user1ExpiredToken = $this->createRefreshToken($user1, true);
        $user1ValidToken = $this->createRefreshToken($user1, false);
        $user2ExpiredToken = $this->createRefreshToken($user2, true);
        $user2ValidToken = $this->createRefreshToken($user2, false);

        $this->assertCount(4, $this->repository->findAll());

        $deletedCount = $this->repository->purgeExpired();
        
        $this->assertEquals(2, $deletedCount);
        
        $remainingTokens = $this->repository->findAll();
        $this->assertCount(2, $remainingTokens);
        
        // Verify remaining tokens belong to both users
        $remainingUserIds = array_unique(array_map(fn($token) => $token->getUser()->getId(), $remainingTokens));
        $this->assertCount(2, $remainingUserIds);
        $this->assertContains($user1->getId(), $remainingUserIds);
        $this->assertContains($user2->getId(), $remainingUserIds);
    }
}