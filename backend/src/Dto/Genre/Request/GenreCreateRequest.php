<?php

namespace App\Dto\Genre\Request;

use Symfony\Component\Validator\Constraints as Assert;

class GenreCreateRequest
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 1, max: 255)]
    public string $name;

    public ?string $description = null;
}