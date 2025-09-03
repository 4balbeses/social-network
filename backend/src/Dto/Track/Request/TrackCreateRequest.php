<?php

namespace App\Dto\Track\Request;

use Symfony\Component\Validator\Constraints as Assert;

class TrackCreateRequest
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 1, max: 255)]
    public string $name;

    #[Assert\Length(max: 10000)]
    public ?string $description = null;

    #[Assert\NotBlank]
    #[Assert\Type('integer')]
    public int $trackFileId;

    #[Assert\NotBlank]
    #[Assert\Type('integer')]
    public int $genreId;
}