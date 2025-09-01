<?php

namespace App\DTO\Response;

class UserResponse
{
    public int $id;
    public string $username;
    public string $fullName;
    public array $roles;
    public \DateTimeInterface $registeredAt;
    public array $playlists = [];
    public array $tags = [];
}