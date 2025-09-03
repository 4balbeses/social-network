<?php

namespace App\Dto\Genre\Response;

class GenreResponse
{
    public int $id;
    public string $name;
    public ?string $description;
    public array $tracks = [];
}