<?php

namespace App\DTO\Response;

class MediaResponse
{
    public int $id;
    public string $originalName;
    public string $fileName;
    public string $filePath;
    public string $mimeType;
    public int $fileSize;
    public \DateTimeInterface $uploadedAt;
}