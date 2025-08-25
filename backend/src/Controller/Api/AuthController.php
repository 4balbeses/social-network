<?php

namespace App\Controller\Api;

use App\Entity\User;
use Doctrine\DBAL\Exception\NotNullConstraintViolationException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api', name: 'api_')]
final class AuthController extends AbstractController
{
    #[Route('/register', name: 'register', methods: ['POST'])]
    public function register(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher
    ): JsonResponse {
        $in = json_decode($request->getContent(), true) ?? [];
        $email = trim((string)($in['email'] ?? ''));
        $plain = (string)($in['password'] ?? '');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($plain) < 6) {
            return $this->json(['error' => 'Invalid email or short password'], 422);
        }

        if ($em->getRepository(User::class)->findOneBy(['email' => $email])) {
            return $this->json(['error' => 'User already exists'], 409);
        }

        try {
            $u = new User();
            $u->setEmail($email);
            $u->setPassword($hasher->hashPassword($u, $plain));
            $u->setRoles(['ROLE_USER']);

            $em->persist($u);
            $em->flush();

            return $this->json(['message' => 'Registered'], 201);
        } catch (UniqueConstraintViolationException) {
            return $this->json(['error' => 'User already exists'], 409);
        } catch (NotNullConstraintViolationException $e) {
            return $this->json(['error' => 'Missing required fields', 'detail' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            return $this->json(['error' => 'Server error', 'detail' => $e->getMessage()], 500);
        }
    }

    #[Route('/login', name: 'login', methods: ['POST'])]
    public function login(): never
    {
        throw new \LogicException('Handled by the security system.');
    }
}
