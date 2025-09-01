<?php

namespace App\Controller;

use App\Service\TrackService;
use App\Service\MapperService;
use App\Dto\Track\Request\TrackCreateRequest;
use App\Dto\Track\Request\TrackUpdateRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/tracks')]
class TrackController extends AbstractController
{
    public function __construct(
        private TrackService $trackService,
        private MapperService $mapperService,
        private ValidatorInterface $validator
    ) {
    }

    #[Route('', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $tracks = $this->trackService->getAllTracks();
        $responseData = [];
        
        foreach ($tracks as $track) {
            $responseData[] = $this->mapperService->trackToResponse($track);
        }
        
        return $this->json($responseData);
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        $createRequest = new TrackCreateRequest();
        $createRequest->name = $data['name'] ?? '';
        $createRequest->description = $data['description'] ?? null;
        $createRequest->trackFileId = $data['trackFileId'] ?? 0;
        $createRequest->genreId = $data['genreId'] ?? 0;
        
        $errors = $this->validator->validate($createRequest);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }
        
        try {
            $track = $this->trackService->createTrack($createRequest);
            return $this->json($this->mapperService->trackToResponse($track), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $track = $this->trackService->getTrackById($id);
        
        if (!$track) {
            return $this->json(['error' => 'Track not found'], Response::HTTP_NOT_FOUND);
        }
        
        return $this->json($this->mapperService->trackToResponse($track));
    }

    #[Route('/{id}', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $track = $this->trackService->getTrackById($id);
        
        if (!$track) {
            return $this->json(['error' => 'Track not found'], Response::HTTP_NOT_FOUND);
        }
        
        $data = json_decode($request->getContent(), true);
        
        $updateRequest = new TrackUpdateRequest();
        $updateRequest->name = $data['name'] ?? null;
        $updateRequest->description = $data['description'] ?? null;
        $updateRequest->trackFileId = $data['trackFileId'] ?? null;
        $updateRequest->genreId = $data['genreId'] ?? null;
        
        $errors = $this->validator->validate($updateRequest);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }
        
        try {
            $updatedTrack = $this->trackService->updateTrack($track, $updateRequest);
            return $this->json($this->mapperService->trackToResponse($updatedTrack));
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $track = $this->trackService->getTrackById($id);
        
        if (!$track) {
            return $this->json(['error' => 'Track not found'], Response::HTTP_NOT_FOUND);
        }
        
        try {
            $this->trackService->deleteTrack($track);
            return $this->json(null, Response::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/genre/{genreId}', methods: ['GET'])]
    public function byGenre(int $genreId): JsonResponse
    {
        $tracks = $this->trackService->getTracksByGenre($genreId);
        $responseData = [];
        
        foreach ($tracks as $track) {
            $responseData[] = $this->mapperService->trackToResponse($track);
        }
        
        return $this->json($responseData);
    }

    #[Route('/search/{searchTerm}', methods: ['GET'])]
    public function search(string $searchTerm): JsonResponse
    {
        $tracks = $this->trackService->searchTracksByName($searchTerm);
        $responseData = [];
        
        foreach ($tracks as $track) {
            $responseData[] = $this->mapperService->trackToResponse($track);
        }
        
        return $this->json($responseData);
    }

    #[Route('/top-rated/{limit}', methods: ['GET'])]
    public function topRated(int $limit = 10): JsonResponse
    {
        $tracks = $this->trackService->getTopRatedTracks($limit);
        $responseData = [];
        
        foreach ($tracks as $track) {
            $responseData[] = $this->mapperService->trackToResponse($track);
        }
        
        return $this->json($responseData);
    }
}