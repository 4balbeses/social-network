<?php

namespace App\Tests\Entity;

use App\Entity\RefreshToken;
use App\Entity\User;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class RefreshTokenTest extends TestCase
{
    private function createUser(): User
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setPassword('hashedpassword');
        $user->setRoles(['ROLE_USER']);
        return $user;
    }

    public function testRefreshTokenCreation(): void
    {
        $user = $this->createUser();
        $token = 'test_token_12345';
        $createdAt = new DateTimeImmutable();
        $expiresAt = new DateTimeImmutable('+30 days');

        $refreshToken = new RefreshToken();
        $refreshToken->setUser($user);
        $refreshToken->setToken($token);
        $refreshToken->setCreatedAt($createdAt);
        $refreshToken->setExpiresAt($expiresAt);

        $this->assertNull($refreshToken->getId()); // ID is null before persistence
        $this->assertSame($user, $refreshToken->getUser());
        $this->assertEquals($token, $refreshToken->getToken());
        $this->assertEquals($createdAt, $refreshToken->getCreatedAt());
        $this->assertEquals($expiresAt, $refreshToken->getExpiresAt());
    }

    public function testIsExpiredWithExpiredToken(): void
    {
        $refreshToken = new RefreshToken();
        $refreshToken->setUser($this->createUser());
        $refreshToken->setToken('test_token');
        $refreshToken->setCreatedAt(new DateTimeImmutable('-2 days'));
        $refreshToken->setExpiresAt(new DateTimeImmutable('-1 day')); // Expired yesterday

        $this->assertTrue($refreshToken->isExpired());
    }

    public function testIsExpiredWithValidToken(): void
    {
        $refreshToken = new RefreshToken();
        $refreshToken->setUser($this->createUser());
        $refreshToken->setToken('test_token');
        $refreshToken->setCreatedAt(new DateTimeImmutable());
        $refreshToken->setExpiresAt(new DateTimeImmutable('+30 days')); // Valid for 30 days

        $this->assertFalse($refreshToken->isExpired());
    }

    public function testIsExpiredWithTokenExpiringNow(): void
    {
        $refreshToken = new RefreshToken();
        $refreshToken->setUser($this->createUser());
        $refreshToken->setToken('test_token');
        $refreshToken->setCreatedAt(new DateTimeImmutable('-1 hour'));
        $refreshToken->setExpiresAt(new DateTimeImmutable()); // Expires exactly now

        // Token expiring exactly now should be considered expired
        $this->assertTrue($refreshToken->isExpired());
    }

    public function testIsExpiredWithTokenExpiringInOneSecond(): void
    {
        $refreshToken = new RefreshToken();
        $refreshToken->setUser($this->createUser());
        $refreshToken->setToken('test_token');
        $refreshToken->setCreatedAt(new DateTimeImmutable());
        $refreshToken->setExpiresAt(new DateTimeImmutable('+1 second')); // Expires in 1 second

        $this->assertFalse($refreshToken->isExpired());
    }

    public function testTokenLength(): void
    {
        $refreshToken = new RefreshToken();
        $refreshToken->setUser($this->createUser());
        
        // Test with token generated the same way as in the application
        $token = bin2hex(random_bytes(64));
        $refreshToken->setToken($token);
        
        // Token should be 128 characters (64 bytes * 2 hex chars)
        $this->assertEquals(128, strlen($refreshToken->getToken()));
        $this->assertEquals(128, strlen($token));
    }

    public function testTokenUniqueness(): void
    {
        $user = $this->createUser();
        
        $token1 = bin2hex(random_bytes(64));
        $token2 = bin2hex(random_bytes(64));
        
        $refreshToken1 = new RefreshToken();
        $refreshToken1->setUser($user);
        $refreshToken1->setToken($token1);
        
        $refreshToken2 = new RefreshToken();
        $refreshToken2->setUser($user);
        $refreshToken2->setToken($token2);
        
        // Tokens should be unique
        $this->assertNotEquals($refreshToken1->getToken(), $refreshToken2->getToken());
    }

    public function testUserRelationship(): void
    {
        $user1 = $this->createUser();
        $user2 = $this->createUser();
        $user2->setEmail('user2@example.com');

        $refreshToken = new RefreshToken();
        $refreshToken->setUser($user1);
        
        $this->assertSame($user1, $refreshToken->getUser());
        
        // Change user
        $refreshToken->setUser($user2);
        $this->assertSame($user2, $refreshToken->getUser());
        $this->assertNotSame($user1, $refreshToken->getUser());
    }

    public function testDateTimeImmutability(): void
    {
        $refreshToken = new RefreshToken();
        $refreshToken->setUser($this->createUser());
        
        $createdAt = new DateTimeImmutable();
        $expiresAt = new DateTimeImmutable('+30 days');
        
        $refreshToken->setCreatedAt($createdAt);
        $refreshToken->setExpiresAt($expiresAt);
        
        $this->assertInstanceOf(DateTimeImmutable::class, $refreshToken->getCreatedAt());
        $this->assertInstanceOf(DateTimeImmutable::class, $refreshToken->getExpiresAt());
        
        // Verify immutability - modifying returned objects shouldn't affect the entity
        $retrievedCreatedAt = $refreshToken->getCreatedAt();
        $modifiedCreatedAt = $retrievedCreatedAt->modify('+1 day');
        
        $this->assertNotSame($retrievedCreatedAt, $modifiedCreatedAt);
        $this->assertEquals($createdAt, $refreshToken->getCreatedAt());
    }

    public function testExpirationLogic(): void
    {
        $refreshToken = new RefreshToken();
        $refreshToken->setUser($this->createUser());
        $refreshToken->setToken('test_token');
        $refreshToken->setCreatedAt(new DateTimeImmutable());
        
        // Test various expiration scenarios
        $scenarios = [
            ['-1 year', true],   // Expired last year
            ['-1 month', true],  // Expired last month  
            ['-1 day', true],    // Expired yesterday
            ['-1 hour', true],   // Expired an hour ago
            ['-1 minute', true], // Expired a minute ago
            ['+1 minute', false], // Expires in a minute
            ['+1 hour', false],   // Expires in an hour
            ['+1 day', false],    // Expires tomorrow
            ['+1 month', false],  // Expires next month
            ['+1 year', false],   // Expires next year
        ];
        
        foreach ($scenarios as [$modifier, $shouldBeExpired]) {
            $refreshToken->setExpiresAt(new DateTimeImmutable($modifier));
            $this->assertEquals(
                $shouldBeExpired, 
                $refreshToken->isExpired(),
                "Token with expiration '{$modifier}' should " . ($shouldBeExpired ? 'be expired' : 'not be expired')
            );
        }
    }
}