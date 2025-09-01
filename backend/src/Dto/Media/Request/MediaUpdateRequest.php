<?php

namespace App\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

class MediaUpdateRequest
{
    #[Assert\Length(min: 1, max: 255)]
    public ?string $originalName = null;

    #[Assert\Length(min: 1, max: 255)]
    public ?string $fileName = null;

    #[Assert\Length(min: 1, max: 255)]
    public ?string $filePath = null;

    #[Assert\Length(min: 1, max: 255)]
    public ?string $mimeType = null;

    #[Assert\Positive]
    public ?int $fileSize = null;
}