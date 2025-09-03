<?php

namespace App\Dto\Artist\Response;

class ArtistResponse
{
    public int $id;
    public string $fullName;
    public ?string $description;
    public ?array $profileImage = null;
    public array $albums = [];
}