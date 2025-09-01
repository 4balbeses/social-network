<?php

namespace App\Service;

use App\Entity\User;
use App\DTO\Request\UserCreateRequest;
use App\DTO\Request\UserUpdateRequest;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function createUser(UserCreateRequest $request): User
    {
        $user = new User();
        $user->setUsername($request->username);
        $user->setFullName($request->fullName);
        $user->setRoles($request->roles);
        
        $hashedPassword = $this->passwordHasher->hashPassword($user, $request->password);
        $user->setPassword($hashedPassword);
        
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        
        return $user;
    }

    public function updateUser(User $user, UserUpdateRequest $request): User
    {
        if ($request->username !== null) {
            $user->setUsername($request->username);
        }
        
        if ($request->fullName !== null) {
            $user->setFullName($request->fullName);
        }
        
        if ($request->roles !== null) {
            $user->setRoles($request->roles);
        }
        
        if ($request->password !== null) {
            $hashedPassword = $this->passwordHasher->hashPassword($user, $request->password);
            $user->setPassword($hashedPassword);
        }
        
        $this->entityManager->flush();
        
        return $user;
    }

    public function deleteUser(User $user): void
    {
        $this->entityManager->remove($user);
        $this->entityManager->flush();
    }

    public function getAllUsers(): array
    {
        return $this->userRepository->findAll();
    }

    public function getUserById(int $id): ?User
    {
        return $this->userRepository->findOneById($id);
    }

    public function getUserByUsername(string $username): ?User
    {
        return $this->userRepository->findByUsername($username);
    }

    public function getActiveUsersWithPlaylists(): array
    {
        return $this->userRepository->findActiveUsersWithPlaylists();
    }
}