<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

final class ProfileController extends AbstractController
{
    #[Route('/api/profile', name: 'api_profile', methods: ['GET'])]
    public function __invoke(): JsonResponse
    {
        /** @var \App\Entity\User|null $u */
        $u = $this->getUser();
        if (!$u) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        return $this->json([
            'id'    => $u->getId(),
            'email' => $u->getUserIdentifier(),
            'roles' => $u->getRoles(),
        ]);
    }
}
