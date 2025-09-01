<?php

namespace App\Controller;

use App\Entity\UserPlaylist;
use App\Repository\UserPlaylistRepository;
use App\Repository\UserRepository;
use App\Repository\PlaylistRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/user-playlists')]
class UserPlaylistController extends AbstractController
{
    public function __construct(
        private UserPlaylistRepository $userPlaylistRepository,
        private UserRepository $userRepository,
        private PlaylistRepository $playlistRepository,
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator
    ) {
    }

    #[Route('', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $userPlaylists = $this->userPlaylistRepository->findAll();
        $responseData = [];
        
        foreach ($userPlaylists as $userPlaylist) {
            $responseData[] = [
                'user' => [
                    'id' => $userPlaylist->getUser()?->getId(),
                    'username' => $userPlaylist->getUser()?->getUsername()
                ],
                'playlist' => [
                    'id' => $userPlaylist->getPlaylist()?->getId(),
                    'name' => $userPlaylist->getPlaylist()?->getName()
                ],
                'addedAt' => $userPlaylist->getAddedAt()?->format('Y-m-d H:i:s')
            ];
        }
        
        return $this->json($responseData);
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        $user = $this->userRepository->find($data['userId'] ?? null);
        if (!$user) {
            return $this->json(['error' => 'User not found'], Response::HTTP_BAD_REQUEST);
        }
        
        $playlist = $this->playlistRepository->find($data['playlistId'] ?? null);
        if (!$playlist) {
            return $this->json(['error' => 'Playlist not found'], Response::HTTP_BAD_REQUEST);
        }
        
        $existingUserPlaylist = $this->userPlaylistRepository->findOneBy([
            'user' => $user,
            'playlist' => $playlist
        ]);
        if ($existingUserPlaylist) {
            return $this->json(['error' => 'User-Playlist relationship already exists'], Response::HTTP_BAD_REQUEST);
        }
        
        $userPlaylist = new UserPlaylist();
        $userPlaylist->setUser($user);
        $userPlaylist->setPlaylist($playlist);
        
        if (isset($data['addedAt'])) {
            $userPlaylist->setAddedAt(new \DateTime($data['addedAt']));
        }
        
        $errors = $this->validator->validate($userPlaylist);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }
        
        try {
            $this->entityManager->persist($userPlaylist);
            $this->entityManager->flush();
            
            return $this->json([
                'user' => [
                    'id' => $userPlaylist->getUser()?->getId(),
                    'username' => $userPlaylist->getUser()?->getUsername()
                ],
                'playlist' => [
                    'id' => $userPlaylist->getPlaylist()?->getId(),
                    'name' => $userPlaylist->getPlaylist()?->getName()
                ],
                'addedAt' => $userPlaylist->getAddedAt()?->format('Y-m-d H:i:s')
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/user/{userId}/playlist/{playlistId}', methods: ['GET'])]
    public function show(int $userId, int $playlistId): JsonResponse
    {
        $user = $this->userRepository->find($userId);
        if (!$user) {
            return $this->json(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }
        
        $playlist = $this->playlistRepository->find($playlistId);
        if (!$playlist) {
            return $this->json(['error' => 'Playlist not found'], Response::HTTP_NOT_FOUND);
        }
        
        $userPlaylist = $this->userPlaylistRepository->findOneBy([
            'user' => $user,
            'playlist' => $playlist
        ]);
        
        if (!$userPlaylist) {
            return $this->json(['error' => 'User-Playlist relationship not found'], Response::HTTP_NOT_FOUND);
        }
        
        return $this->json([
            'user' => [
                'id' => $userPlaylist->getUser()?->getId(),
                'username' => $userPlaylist->getUser()?->getUsername()
            ],
            'playlist' => [
                'id' => $userPlaylist->getPlaylist()?->getId(),
                'name' => $userPlaylist->getPlaylist()?->getName()
            ],
            'addedAt' => $userPlaylist->getAddedAt()?->format('Y-m-d H:i:s')
        ]);
    }

    #[Route('/user/{userId}/playlist/{playlistId}', methods: ['PUT'])]
    public function update(int $userId, int $playlistId, Request $request): JsonResponse
    {
        $user = $this->userRepository->find($userId);
        if (!$user) {
            return $this->json(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }
        
        $playlist = $this->playlistRepository->find($playlistId);
        if (!$playlist) {
            return $this->json(['error' => 'Playlist not found'], Response::HTTP_NOT_FOUND);
        }
        
        $userPlaylist = $this->userPlaylistRepository->findOneBy([
            'user' => $user,
            'playlist' => $playlist
        ]);
        
        if (!$userPlaylist) {
            return $this->json(['error' => 'User-Playlist relationship not found'], Response::HTTP_NOT_FOUND);
        }
        
        $data = json_decode($request->getContent(), true);
        
        if (isset($data['addedAt'])) {
            $userPlaylist->setAddedAt(new \DateTime($data['addedAt']));
        }
        
        $errors = $this->validator->validate($userPlaylist);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }
        
        try {
            $this->entityManager->flush();
            
            return $this->json([
                'user' => [
                    'id' => $userPlaylist->getUser()?->getId(),
                    'username' => $userPlaylist->getUser()?->getUsername()
                ],
                'playlist' => [
                    'id' => $userPlaylist->getPlaylist()?->getId(),
                    'name' => $userPlaylist->getPlaylist()?->getName()
                ],
                'addedAt' => $userPlaylist->getAddedAt()?->format('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/user/{userId}/playlist/{playlistId}', methods: ['DELETE'])]
    public function delete(int $userId, int $playlistId): JsonResponse
    {
        $user = $this->userRepository->find($userId);
        if (!$user) {
            return $this->json(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }
        
        $playlist = $this->playlistRepository->find($playlistId);
        if (!$playlist) {
            return $this->json(['error' => 'Playlist not found'], Response::HTTP_NOT_FOUND);
        }
        
        $userPlaylist = $this->userPlaylistRepository->findOneBy([
            'user' => $user,
            'playlist' => $playlist
        ]);
        
        if (!$userPlaylist) {
            return $this->json(['error' => 'User-Playlist relationship not found'], Response::HTTP_NOT_FOUND);
        }
        
        try {
            $this->entityManager->remove($userPlaylist);
            $this->entityManager->flush();
            return $this->json(null, Response::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}