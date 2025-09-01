<?php

namespace App\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

class ArtistUpdateRequest
{
    #[Assert\Length(min: 1, max: 255)]
    public ?string $fullName = null;

    public ?string $description = null;
}