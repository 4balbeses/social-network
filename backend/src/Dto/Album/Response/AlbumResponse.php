<?php

namespace App\Dto\Album\Response;

class AlbumResponse
{
    public int $id;
    public string $name;
    public ?string $description;
    public \DateTimeInterface $createdAt;
    public array $artist;
    public array $tracks = [];
    public float $averageRating = 0.0;
    public int $ratingsCount = 0;
}