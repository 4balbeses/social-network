<?php

namespace App\Dto\Playlist\Response;

class PlaylistResponse
{
    public int $id;
    public string $name;
    public ?string $description;
    public bool $isPublic;
    public \DateTimeInterface $createdAt;
    public array $owner;
    public array $tracks = [];
    public float $averageRating = 0.0;
    public int $ratingsCount = 0;
}