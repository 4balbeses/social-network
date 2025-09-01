<?php

namespace App\DTO\Response;

class TrackResponse
{
    public int $id;
    public string $name;
    public ?string $description;
    public array $trackFile;
    public array $genre;
    public array $albums = [];
    public array $playlists = [];
    public array $tags = [];
    public float $averageRating = 0.0;
    public int $ratingsCount = 0;
}