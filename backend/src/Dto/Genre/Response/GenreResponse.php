<?php

namespace App\DTO\Response;

class GenreResponse
{
    public int $id;
    public string $name;
    public ?string $description;
    public array $tracks = [];
}