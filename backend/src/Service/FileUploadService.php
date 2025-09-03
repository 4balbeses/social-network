<?php

namespace App\Service;

use App\Entity\Media;
use App\DTO\Request\MediaCreateRequest;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploadService
{
    private string $publicPath;
    private MediaService $mediaService;

    public function __construct(
        string $projectDir,
        MediaService $mediaService
    ) {
        $this->publicPath = $projectDir . '/public';
        $this->mediaService = $mediaService;
    }

    public function uploadFile(UploadedFile $file, string $type = 'general'): Media
    {
        $mimeType = $file->getMimeType() ?? $this->mediaService->detectMimeType($file->getPathname());
        
        if ($this->mediaService->isImageFile($mimeType)) {
            $directory = '/media/images';
        } elseif ($this->mediaService->isAudioFile($mimeType)) {
            $directory = '/media/tracks';
        } else {
            $directory = '/media/general';
        }
        
        $targetDirectory = $this->publicPath . $directory;
        if (!is_dir($targetDirectory)) {
            mkdir($targetDirectory, 0755, true);
        }
        
        $originalFileName = $file->getClientOriginalName();
        $fileName = uniqid() . '_' . $originalFileName;
        $filePath = $directory . '/' . $fileName;
        
        $file->move($targetDirectory, $fileName);
        
        $createRequest = new MediaCreateRequest();
        $createRequest->originalName = $originalFileName;
        $createRequest->fileName = $fileName;
        $createRequest->filePath = $filePath;
        $createRequest->mimeType = $mimeType;
        $createRequest->fileSize = filesize($this->publicPath . $filePath);
        
        return $this->mediaService->createMedia($createRequest);
    }

    public function deleteFile(Media $media): void
    {
        $fullPath = $this->publicPath . $media->getFilePath();
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
    }

    public function getFileUrl(Media $media): string
    {
        return $media->getFilePath();
    }
}