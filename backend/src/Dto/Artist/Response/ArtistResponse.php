<?php

namespace App\DTO\Response;

class ArtistResponse
{
    public int $id;
    public string $fullName;
    public ?string $description;
    public array $albums = [];
}