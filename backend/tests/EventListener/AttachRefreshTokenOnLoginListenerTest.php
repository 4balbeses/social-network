<?php

namespace App\Tests\EventListener;

use App\Entity\RefreshToken;
use App\Entity\User;
use App\EventListener\AttachRefreshTokenOnLoginListener;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\User\UserInterface;

class AttachRefreshTokenOnLoginListenerTest extends TestCase
{
    private MockObject $entityManager;
    private AttachRefreshTokenOnLoginListener $listener;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->listener = new AttachRefreshTokenOnLoginListener($this->entityManager);
    }

    private function createUser(): User
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setPassword('hashedpassword');
        $user->setRoles(['ROLE_USER']);
        return $user;
    }

    public function testOnAuthenticationSuccessCreatesRefreshToken(): void
    {
        $user = $this->createUser();
        $response = new JsonResponse(['token' => 'jwt_token_here']);
        $event = new AuthenticationSuccessEvent(['token' => 'jwt_token_here'], $user, $response);

        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->callback(function ($refreshToken) use ($user) {
                return $refreshToken instanceof RefreshToken
                    && $refreshToken->getUser() === $user
                    && strlen($refreshToken->getToken()) === 128
                    && $refreshToken->getCreatedAt() instanceof DateTimeImmutable
                    && $refreshToken->getExpiresAt() instanceof DateTimeImmutable
                    && $refreshToken->getExpiresAt() > new DateTimeImmutable()
                    && !$refreshToken->isExpired();
            }));

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $this->listener->onAuthenticationSuccess($event);

        // Check that refresh token was added to the response data
        $responseData = json_decode($event->getResponse()->getContent(), true);
        $this->assertArrayHasKey('refresh_token', $responseData);
        $this->assertNotEmpty($responseData['refresh_token']);
        $this->assertEquals(128, strlen($responseData['refresh_token']));
    }

    public function testOnAuthenticationSuccessWithNonUserInterface(): void
    {
        $nonUser = $this->createMock(UserInterface::class);
        $response = new JsonResponse(['token' => 'jwt_token_here']);
        $event = new AuthenticationSuccessEvent(['token' => 'jwt_token_here'], $nonUser, $response);

        $this->entityManager
            ->expects($this->never())
            ->method('persist');

        $this->entityManager
            ->expects($this->never())
            ->method('flush');

        $this->listener->onAuthenticationSuccess($event);

        // Check that no refresh token was added to the response
        $responseData = json_decode($event->getResponse()->getContent(), true);
        $this->assertArrayNotHasKey('refresh_token', $responseData);
    }

    public function testRefreshTokenHasCorrectExpiration(): void
    {
        $user = $this->createUser();
        $response = new JsonResponse(['token' => 'jwt_token_here']);
        $event = new AuthenticationSuccessEvent(['token' => 'jwt_token_here'], $user, $response);

        $capturedRefreshToken = null;
        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->callback(function ($refreshToken) use (&$capturedRefreshToken) {
                $capturedRefreshToken = $refreshToken;
                return true;
            }));

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $this->listener->onAuthenticationSuccess($event);

        // Verify the refresh token expires in approximately 30 days
        $this->assertInstanceOf(RefreshToken::class, $capturedRefreshToken);
        $now = new DateTimeImmutable();
        $expectedExpiry = $now->modify('+30 days');
        $actualExpiry = $capturedRefreshToken->getExpiresAt();
        
        $diff = $actualExpiry->getTimestamp() - $expectedExpiry->getTimestamp();
        $this->assertLessThan(60, abs($diff)); // Should be within 60 seconds
    }

    public function testMultipleAuthenticationSuccessEventsCreateUniqueTokens(): void
    {
        $user = $this->createUser();
        $response1 = new JsonResponse(['token' => 'jwt_token_1']);
        $response2 = new JsonResponse(['token' => 'jwt_token_2']);
        $event1 = new AuthenticationSuccessEvent(['token' => 'jwt_token_1'], $user, $response1);
        $event2 = new AuthenticationSuccessEvent(['token' => 'jwt_token_2'], $user, $response2);

        $capturedTokens = [];
        $this->entityManager
            ->expects($this->exactly(2))
            ->method('persist')
            ->with($this->callback(function ($refreshToken) use (&$capturedTokens) {
                $capturedTokens[] = $refreshToken->getToken();
                return true;
            }));

        $this->entityManager
            ->expects($this->exactly(2))
            ->method('flush');

        $this->listener->onAuthenticationSuccess($event1);
        $this->listener->onAuthenticationSuccess($event2);

        // Verify tokens are unique
        $this->assertCount(2, $capturedTokens);
        $this->assertNotEquals($capturedTokens[0], $capturedTokens[1]);
        $this->assertEquals(128, strlen($capturedTokens[0]));
        $this->assertEquals(128, strlen($capturedTokens[1]));
    }

    public function testRefreshTokenIsAddedToResponseData(): void
    {
        $user = $this->createUser();
        $originalData = ['token' => 'jwt_token_here', 'user' => 'data'];
        $response = new JsonResponse($originalData);
        $event = new AuthenticationSuccessEvent($originalData, $user, $response);

        $capturedToken = null;
        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->callback(function ($refreshToken) use (&$capturedToken) {
                $capturedToken = $refreshToken->getToken();
                return true;
            }));

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $this->listener->onAuthenticationSuccess($event);

        // Verify the response was updated with the refresh token
        $responseData = json_decode($event->getResponse()->getContent(), true);
        $this->assertArrayHasKey('refresh_token', $responseData);
        $this->assertEquals($capturedToken, $responseData['refresh_token']);
        
        // Verify original data is preserved
        $this->assertArrayHasKey('token', $responseData);
        $this->assertArrayHasKey('user', $responseData);
        $this->assertEquals('jwt_token_here', $responseData['token']);
        $this->assertEquals('data', $responseData['user']);
    }

    public function testEntityManagerExceptionHandling(): void
    {
        $user = $this->createUser();
        $response = new JsonResponse(['token' => 'jwt_token_here']);
        $event = new AuthenticationSuccessEvent(['token' => 'jwt_token_here'], $user, $response);

        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->will($this->throwException(new \RuntimeException('Database error')));

        $this->entityManager
            ->expects($this->never())
            ->method('flush');

        // Should not throw exception, but handle gracefully
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Database error');
        
        $this->listener->onAuthenticationSuccess($event);
    }
}