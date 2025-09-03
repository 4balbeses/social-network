<?php

namespace App\Controller;

use App\Service\ArtistService;
use App\Service\MediaService;
use App\Service\MapperService;
use App\Dto\Artist\Request\ArtistCreateRequest;
use App\Dto\Artist\Request\ArtistUpdateRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/artists')]
class ArtistController extends AbstractController
{
    public function __construct(
        private ArtistService $artistService,
        private MediaService $mediaService,
        private MapperService $mapperService,
        private ValidatorInterface $validator
    ) {
    }

    #[Route('', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $artists = $this->artistService->getAllArtists();
        $responseData = [];
        
        foreach ($artists as $artist) {
            $responseData[] = $this->mapperService->artistToResponse($artist);
        }
        
        return $this->json($responseData);
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        $createRequest = new ArtistCreateRequest();
        $createRequest->fullName = $data['fullName'] ?? '';
        $createRequest->description = $data['description'] ?? null;
        
        $errors = $this->validator->validate($createRequest);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }
        
        try {
            $artist = $this->artistService->createArtist($createRequest);
            return $this->json($this->mapperService->artistToResponse($artist), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $artist = $this->artistService->getArtistById($id);
        
        if (!$artist) {
            return $this->json(['error' => 'Artist not found'], Response::HTTP_NOT_FOUND);
        }
        
        return $this->json($this->mapperService->artistToResponse($artist));
    }

    #[Route('/{id}', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $artist = $this->artistService->getArtistById($id);
        
        if (!$artist) {
            return $this->json(['error' => 'Artist not found'], Response::HTTP_NOT_FOUND);
        }
        
        $data = json_decode($request->getContent(), true);
        
        $updateRequest = new ArtistUpdateRequest();
        $updateRequest->fullName = $data['fullName'] ?? null;
        $updateRequest->description = $data['description'] ?? null;
        
        $errors = $this->validator->validate($updateRequest);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }
        
        try {
            $updatedArtist = $this->artistService->updateArtist($artist, $updateRequest);
            return $this->json($this->mapperService->artistToResponse($updatedArtist));
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $artist = $this->artistService->getArtistById($id);
        
        if (!$artist) {
            return $this->json(['error' => 'Artist not found'], Response::HTTP_NOT_FOUND);
        }
        
        try {
            $this->artistService->deleteArtist($artist);
            return $this->json(null, Response::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}/profile-image', methods: ['POST'])]
    public function setProfileImage(int $id, Request $request): JsonResponse
    {
        $artist = $this->artistService->getArtistById($id);
        
        if (!$artist) {
            return $this->json(['error' => 'Artist not found'], Response::HTTP_NOT_FOUND);
        }
        
        $data = json_decode($request->getContent(), true);
        $mediaId = $data['mediaId'] ?? null;
        
        if (!$mediaId) {
            return $this->json(['error' => 'Media ID is required'], Response::HTTP_BAD_REQUEST);
        }
        
        $media = $this->mediaService->getMediaById($mediaId);
        
        if (!$media) {
            return $this->json(['error' => 'Media not found'], Response::HTTP_NOT_FOUND);
        }
        
        if (!$this->mediaService->isImageFile($media->getMimeType())) {
            return $this->json(['error' => 'Media must be an image file'], Response::HTTP_BAD_REQUEST);
        }
        
        try {
            $updatedArtist = $this->artistService->setArtistProfileImage($artist, $media);
            return $this->json($this->mapperService->artistToResponse($updatedArtist));
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}/profile-image', methods: ['DELETE'])]
    public function removeProfileImage(int $id): JsonResponse
    {
        $artist = $this->artistService->getArtistById($id);
        
        if (!$artist) {
            return $this->json(['error' => 'Artist not found'], Response::HTTP_NOT_FOUND);
        }
        
        try {
            $updatedArtist = $this->artistService->removeArtistProfileImage($artist);
            return $this->json($this->mapperService->artistToResponse($updatedArtist));
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}