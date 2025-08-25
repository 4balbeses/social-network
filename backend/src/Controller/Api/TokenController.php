<?php

namespace App\Controller\Api;

use App\Entity\RefreshToken;
use App\Repository\RefreshTokenRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class TokenController extends AbstractController
{
    public function __construct(
        private RefreshTokenRepository $repo,
        private EntityManagerInterface $em,
        private JWTTokenManagerInterface $jwt
    ) {}

    #[Route("/api/token/refresh", name: "token_refresh", methods: ["POST"])]
    public function refresh(Request $request): JsonResponse
    {
        $payload = json_decode($request->getContent(), true) ?? [];
        $tokenStr = $payload["refresh_token"] ?? null;
        if (!$tokenStr) {
            return $this->json(["error" => "missing refresh_token"], 400);
        }

        /** @var RefreshToken|null $rt */
        $rt = $this->repo->findOneBy(["token" => $tokenStr]);
        if (!$rt || $rt->isExpired()) {
            return $this->json(["error" => "invalid or expired refresh_token"], 401);
        }

        $user = $rt->getUser();

        // ротация: удаляем старый, создаём новый
        $this->em->remove($rt);

        $new = new RefreshToken();
        $new->setUser($user);
        $new->setToken(bin2hex(random_bytes(64)));
        $new->setCreatedAt(new DateTimeImmutable());
        $new->setExpiresAt((new DateTimeImmutable())->modify("+30 days"));

        $this->em->persist($new);
        $this->em->flush();

        $access = $this->jwt->create($user);

        return $this->json([
            "token"         => $access,
            "refresh_token" => $new->getToken(),
        ]);
    }
}
