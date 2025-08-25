<?php

namespace App\EventListener;

use App\Entity\RefreshToken;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\Security\Core\User\UserInterface;

final class AttachRefreshTokenOnLoginListener
{
    public function __construct(private EntityManagerInterface $em) {}

    public function onAuthenticationSuccess(AuthenticationSuccessEvent $event): void
    {
        $user = $event->getUser();
        if (!$user instanceof UserInterface) {
            return;
        }

        $rt = new RefreshToken();
        $rt->setUser($user);
        $rt->setToken(bin2hex(random_bytes(64)));
        $rt->setCreatedAt(new DateTimeImmutable());
        $rt->setExpiresAt((new DateTimeImmutable())->modify(+30
