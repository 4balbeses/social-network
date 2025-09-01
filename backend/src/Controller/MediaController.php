<?php

namespace App\Controller;

use App\Service\MediaService;
use App\Service\MapperService;
use App\Dto\Media\Request\MediaCreateRequest;
use App\Dto\Media\Request\MediaUpdateRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/media')]
class MediaController extends AbstractController
{
    public function __construct(
        private MediaService $mediaService,
        private MapperService $mapperService,
        private ValidatorInterface $validator
    ) {
    }

    #[Route('', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $medias = $this->mediaService->getAllMedia();
        $responseData = [];
        
        foreach ($medias as $media) {
            $responseData[] = $this->mapperService->mediaToResponse($media);
        }
        
        return $this->json($responseData);
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        $createRequest = new MediaCreateRequest();
        $createRequest->originalName = $data['originalName'] ?? '';
        $createRequest->fileName = $data['fileName'] ?? '';
        $createRequest->filePath = $data['filePath'] ?? '';
        $createRequest->mimeType = $data['mimeType'] ?? '';
        $createRequest->fileSize = $data['fileSize'] ?? 0;
        
        $errors = $this->validator->validate($createRequest);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }
        
        try {
            $media = $this->mediaService->createMedia($createRequest);
            return $this->json($this->mapperService->mediaToResponse($media), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $media = $this->mediaService->getMediaById($id);
        
        if (!$media) {
            return $this->json(['error' => 'Media not found'], Response::HTTP_NOT_FOUND);
        }
        
        return $this->json($this->mapperService->mediaToResponse($media));
    }

    #[Route('/{id}', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $media = $this->mediaService->getMediaById($id);
        
        if (!$media) {
            return $this->json(['error' => 'Media not found'], Response::HTTP_NOT_FOUND);
        }
        
        $data = json_decode($request->getContent(), true);
        
        $updateRequest = new MediaUpdateRequest();
        $updateRequest->originalName = $data['originalName'] ?? null;
        $updateRequest->fileName = $data['fileName'] ?? null;
        $updateRequest->filePath = $data['filePath'] ?? null;
        $updateRequest->mimeType = $data['mimeType'] ?? null;
        $updateRequest->fileSize = $data['fileSize'] ?? null;
        
        $errors = $this->validator->validate($updateRequest);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }
        
        try {
            $updatedMedia = $this->mediaService->updateMedia($media, $updateRequest);
            return $this->json($this->mapperService->mediaToResponse($updatedMedia));
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $media = $this->mediaService->getMediaById($id);
        
        if (!$media) {
            return $this->json(['error' => 'Media not found'], Response::HTTP_NOT_FOUND);
        }
        
        try {
            $this->mediaService->deleteMedia($media);
            return $this->json(null, Response::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}