<?php

namespace App\Controller;

use App\Service\PlaylistService;
use App\Service\UserService;
use App\Service\MapperService;
use App\Dto\Playlist\Request\PlaylistCreateRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Security\Core\Security;

#[Route('/api/playlists')]
class PlaylistController extends AbstractController
{
    public function __construct(
        private PlaylistService $playlistService,
        private UserService $userService,
        private MapperService $mapperService,
        private ValidatorInterface $validator
    ) {
    }

    #[Route('', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $playlists = $this->playlistService->getAllPlaylists();
        $responseData = [];
        
        foreach ($playlists as $playlist) {
            $responseData[] = $this->mapperService->playlistToResponse($playlist);
        }
        
        return $this->json($responseData);
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        $createRequest = new PlaylistCreateRequest();
        $createRequest->name = $data['name'] ?? '';
        $createRequest->description = $data['description'] ?? null;
        $createRequest->isPublic = $data['isPublic'] ?? false;
        
        $errors = $this->validator->validate($createRequest);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }
        
        $ownerId = $data['ownerId'] ?? null;
        if (!$ownerId) {
            return $this->json(['error' => 'Owner ID is required'], Response::HTTP_BAD_REQUEST);
        }
        
        $owner = $this->userService->getUserById($ownerId);
        if (!$owner) {
            return $this->json(['error' => 'Owner not found'], Response::HTTP_NOT_FOUND);
        }
        
        try {
            $playlist = $this->playlistService->createPlaylist($createRequest, $owner);
            return $this->json($this->mapperService->playlistToResponse($playlist), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $playlist = $this->playlistService->getPlaylistById($id);
        
        if (!$playlist) {
            return $this->json(['error' => 'Playlist not found'], Response::HTTP_NOT_FOUND);
        }
        
        return $this->json($this->mapperService->playlistToResponse($playlist));
    }

    #[Route('/{id}', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $playlist = $this->playlistService->getPlaylistById($id);
        
        if (!$playlist) {
            return $this->json(['error' => 'Playlist not found'], Response::HTTP_NOT_FOUND);
        }
        
        $data = json_decode($request->getContent(), true);
        
        $updateRequest = new PlaylistCreateRequest();
        $updateRequest->name = $data['name'] ?? null;
        $updateRequest->description = $data['description'] ?? null;
        $updateRequest->isPublic = $data['isPublic'] ?? $playlist->isIsPublic();
        
        $errors = $this->validator->validate($updateRequest);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }
        
        try {
            $updatedPlaylist = $this->playlistService->updatePlaylist($playlist, $updateRequest);
            return $this->json($this->mapperService->playlistToResponse($updatedPlaylist));
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $playlist = $this->playlistService->getPlaylistById($id);
        
        if (!$playlist) {
            return $this->json(['error' => 'Playlist not found'], Response::HTTP_NOT_FOUND);
        }
        
        try {
            $this->playlistService->deletePlaylist($playlist);
            return $this->json(null, Response::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/owner/{ownerId}', methods: ['GET'])]
    public function byOwner(int $ownerId): JsonResponse
    {
        $owner = $this->userService->getUserById($ownerId);
        if (!$owner) {
            return $this->json(['error' => 'Owner not found'], Response::HTTP_NOT_FOUND);
        }
        
        $playlists = $this->playlistService->getPlaylistsByOwner($owner);
        $responseData = [];
        
        foreach ($playlists as $playlist) {
            $responseData[] = $this->mapperService->playlistToResponse($playlist);
        }
        
        return $this->json($responseData);
    }

    #[Route('/public', methods: ['GET'])]
    public function publicPlaylists(): JsonResponse
    {
        $playlists = $this->playlistService->getPublicPlaylists();
        $responseData = [];
        
        foreach ($playlists as $playlist) {
            $responseData[] = $this->mapperService->playlistToResponse($playlist);
        }
        
        return $this->json($responseData);
    }
}