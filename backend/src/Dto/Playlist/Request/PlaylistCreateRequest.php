<?php

namespace App\Dto\Playlist\Request;

use Symfony\Component\Validator\Constraints as Assert;

class PlaylistCreateRequest
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 1, max: 255)]
    public string $name;

    #[Assert\Length(max: 10000)]
    public ?string $description = null;

    #[Assert\Type('boolean')]
    public bool $isPublic = false;
}