<?php

namespace App\Dto\Album\Request;

use Symfony\Component\Validator\Constraints as Assert;

class AlbumUpdateRequest
{
    #[Assert\Length(min: 1, max: 255)]
    public ?string $name = null;

    #[Assert\Length(max: 10000)]
    public ?string $description = null;

    #[Assert\Type('integer')]
    public ?int $artistId = null;
}