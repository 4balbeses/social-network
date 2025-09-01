<?php

namespace App\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

class ArtistCreateRequest
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 1, max: 255)]
    public string $fullName;

    public ?string $description = null;
}