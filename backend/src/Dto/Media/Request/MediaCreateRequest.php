<?php

namespace App\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

class MediaCreateRequest
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 1, max: 255)]
    public string $originalName;

    #[Assert\NotBlank]
    #[Assert\Length(min: 1, max: 255)]
    public string $fileName;

    #[Assert\NotBlank]
    #[Assert\Length(min: 1, max: 255)]
    public string $filePath;

    #[Assert\NotBlank]
    #[Assert\Length(min: 1, max: 255)]
    public string $mimeType;

    #[Assert\NotBlank]
    #[Assert\Positive]
    public int $fileSize;
}