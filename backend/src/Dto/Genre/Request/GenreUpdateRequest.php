<?php

namespace App\Dto\Genre\Request;

use Symfony\Component\Validator\Constraints as Assert;

class GenreUpdateRequest
{
    #[Assert\Length(min: 1, max: 255)]
    public ?string $name = null;

    public ?string $description = null;
}