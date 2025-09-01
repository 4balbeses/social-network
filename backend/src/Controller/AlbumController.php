<?php

namespace App\Controller;

use App\Service\AlbumService;
use App\Service\MapperService;
use App\Dto\Album\Request\AlbumCreateRequest;
use App\Dto\Album\Request\AlbumUpdateRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/albums')]
class AlbumController extends AbstractController
{
    public function __construct(
        private AlbumService $albumService,
        private MapperService $mapperService,
        private ValidatorInterface $validator
    ) {
    }

    #[Route('', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $albums = $this->albumService->getAllAlbums();
        $responseData = [];
        
        foreach ($albums as $album) {
            $responseData[] = $this->mapperService->albumToResponse($album);
        }
        
        return $this->json($responseData);
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        $createRequest = new AlbumCreateRequest();
        $createRequest->name = $data['name'] ?? '';
        $createRequest->description = $data['description'] ?? null;
        $createRequest->artistId = $data['artistId'] ?? 0;
        
        $errors = $this->validator->validate($createRequest);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }
        
        try {
            $album = $this->albumService->createAlbum($createRequest);
            return $this->json($this->mapperService->albumToResponse($album), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $album = $this->albumService->getAlbumById($id);
        
        if (!$album) {
            return $this->json(['error' => 'Album not found'], Response::HTTP_NOT_FOUND);
        }
        
        return $this->json($this->mapperService->albumToResponse($album));
    }

    #[Route('/{id}', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $album = $this->albumService->getAlbumById($id);
        
        if (!$album) {
            return $this->json(['error' => 'Album not found'], Response::HTTP_NOT_FOUND);
        }
        
        $data = json_decode($request->getContent(), true);
        
        $updateRequest = new AlbumUpdateRequest();
        $updateRequest->name = $data['name'] ?? null;
        $updateRequest->description = $data['description'] ?? null;
        $updateRequest->artistId = $data['artistId'] ?? null;
        
        $errors = $this->validator->validate($updateRequest);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }
        
        try {
            $updatedAlbum = $this->albumService->updateAlbum($album, $updateRequest);
            return $this->json($this->mapperService->albumToResponse($updatedAlbum));
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $album = $this->albumService->getAlbumById($id);
        
        if (!$album) {
            return $this->json(['error' => 'Album not found'], Response::HTTP_NOT_FOUND);
        }
        
        try {
            $this->albumService->deleteAlbum($album);
            return $this->json(null, Response::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/artist/{artistId}', methods: ['GET'])]
    public function byArtist(int $artistId): JsonResponse
    {
        $albums = $this->albumService->getAlbumsByArtist($artistId);
        $responseData = [];
        
        foreach ($albums as $album) {
            $responseData[] = $this->mapperService->albumToResponse($album);
        }
        
        return $this->json($responseData);
    }
}