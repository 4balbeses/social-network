<?php

namespace App\Service;

use App\Entity\Media;
use App\DTO\Request\MediaCreateRequest;
use App\DTO\Request\MediaUpdateRequest;
use App\Repository\MediaRepository;
use Doctrine\ORM\EntityManagerInterface;

class MediaService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private MediaRepository $mediaRepository
    ) {
    }

    public function createMedia(MediaCreateRequest $request): Media
    {
        $media = new Media();
        $media->setOriginalName($request->originalName);
        $media->setFileName($request->fileName);
        $media->setFilePath($request->filePath);
        $media->setMimeType($request->mimeType);
        $media->setFileSize($request->fileSize);
        
        $this->entityManager->persist($media);
        $this->entityManager->flush();
        
        return $media;
    }

    public function updateMedia(Media $media, MediaUpdateRequest $request): Media
    {
        if ($request->originalName !== null) {
            $media->setOriginalName($request->originalName);
        }
        
        if ($request->fileName !== null) {
            $media->setFileName($request->fileName);
        }
        
        if ($request->filePath !== null) {
            $media->setFilePath($request->filePath);
        }
        
        if ($request->mimeType !== null) {
            $media->setMimeType($request->mimeType);
        }
        
        if ($request->fileSize !== null) {
            $media->setFileSize($request->fileSize);
        }
        
        $this->entityManager->flush();
        
        return $media;
    }

    public function deleteMedia(Media $media): void
    {
        $this->entityManager->remove($media);
        $this->entityManager->flush();
    }

    public function getAllMedia(): array
    {
        return $this->mediaRepository->findAll();
    }

    public function getMediaById(int $id): ?Media
    {
        return $this->mediaRepository->find($id);
    }

    public function getMediaByMimeType(string $mimeType): array
    {
        return $this->mediaRepository->findBy(['mimeType' => $mimeType]);
    }

    public function detectMimeType(string $filePath): string
    {
        if (!file_exists($filePath)) {
            throw new \InvalidArgumentException('File does not exist: ' . $filePath);
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($filePath);
        
        if ($mimeType === false) {
            $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            $mimeType = $this->getMimeTypeByExtension($extension);
        }
        
        return $mimeType;
    }

    public function isAudioFile(string $mimeType): bool
    {
        return str_starts_with($mimeType, 'audio/');
    }

    public function isImageFile(string $mimeType): bool
    {
        return str_starts_with($mimeType, 'image/');
    }

    public function getSupportedAudioMimeTypes(): array
    {
        return [
            'audio/mpeg',
            'audio/mp3',
            'audio/wav',
            'audio/ogg',
            'audio/aac',
            'audio/flac',
            'audio/m4a',
            'audio/webm',
        ];
    }

    public function getSupportedImageMimeTypes(): array
    {
        return [
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/gif',
            'image/bmp',
            'image/webp',
            'image/svg+xml',
        ];
    }

    private function getMimeTypeByExtension(string $extension): string
    {
        $mimeTypes = [
            'mp3' => 'audio/mpeg',
            'wav' => 'audio/wav',
            'ogg' => 'audio/ogg',
            'aac' => 'audio/aac',
            'flac' => 'audio/flac',
            'm4a' => 'audio/m4a',
            'webm' => 'audio/webm',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
        ];

        return $mimeTypes[$extension] ?? 'application/octet-stream';
    }
}