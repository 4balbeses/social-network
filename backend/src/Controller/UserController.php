<?php

namespace App\Controller;

use App\Service\UserService;
use App\Service\MapperService;
use App\Dto\User\Request\UserCreateRequest;
use App\Dto\User\Request\UserUpdateRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/users')]
class UserController extends AbstractController
{
    public function __construct(
        private UserService $userService,
        private MapperService $mapperService,
        private ValidatorInterface $validator
    ) {
    }

    #[Route('', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function index(): JsonResponse
    {
        $users = $this->userService->getAllUsers();
        $responseData = [];
        
        foreach ($users as $user) {
            $responseData[] = $this->mapperService->userToResponse($user);
        }
        
        return $this->json($responseData);
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->json(['error' => 'Invalid JSON'], Response::HTTP_BAD_REQUEST);
        }
        
        $createRequest = new UserCreateRequest();
        $createRequest->username = $data['username'] ?? '';
        $createRequest->password = $data['password'] ?? '';
        $createRequest->fullName = $data['fullName'] ?? '';
        $createRequest->roles = ['ROLE_USER'];
        
        $errors = $this->validator->validate($createRequest);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }
        
        try {
            $user = $this->userService->createUser($createRequest);
            return $this->json($this->mapperService->userToResponse($user), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $user = $this->userService->getUserById($id);
        
        if (!$user) {
            return $this->json(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }
        
        return $this->json($this->mapperService->userToResponse($user));
    }

    #[Route('/{id}', methods: ['PUT'])]
    #[IsGranted('ROLE_USER')]
    public function update(int $id, Request $request): JsonResponse
    {
        $user = $this->userService->getUserById($id);
        
        if (!$user) {
            return $this->json(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }
        
        if ($user->getId() !== $this->getUser()->getId() && !$this->isGranted('ROLE_ADMIN')) {
            return $this->json(['error' => 'Access denied'], Response::HTTP_FORBIDDEN);
        }
        
        $data = json_decode($request->getContent(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->json(['error' => 'Invalid JSON'], Response::HTTP_BAD_REQUEST);
        }
        
        $updateRequest = new UserUpdateRequest();
        $updateRequest->username = $data['username'] ?? null;
        $updateRequest->password = $data['password'] ?? null;
        $updateRequest->fullName = $data['fullName'] ?? null;
        $updateRequest->roles = $this->isGranted('ROLE_ADMIN') ? ($data['roles'] ?? null) : null;
        
        $errors = $this->validator->validate($updateRequest);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }
        
        try {
            $updatedUser = $this->userService->updateUser($user, $updateRequest);
            return $this->json($this->mapperService->userToResponse($updatedUser));
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(int $id): JsonResponse
    {
        $user = $this->userService->getUserById($id);
        
        if (!$user) {
            return $this->json(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }
        
        try {
            $this->userService->deleteUser($user);
            return $this->json(null, Response::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/search/{username}', methods: ['GET'])]
    public function findByUsername(string $username): JsonResponse
    {
        $user = $this->userService->getUserByUsername($username);
        
        if (!$user) {
            return $this->json(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }
        
        return $this->json($this->mapperService->userToResponse($user));
    }

    #[Route('/active-with-playlists', methods: ['GET'])]
    public function activeUsersWithPlaylists(): JsonResponse
    {
        $users = $this->userService->getActiveUsersWithPlaylists();
        $responseData = [];
        
        foreach ($users as $user) {
            $responseData[] = $this->mapperService->userToResponse($user);
        }
        
        return $this->json($responseData);
    }
}