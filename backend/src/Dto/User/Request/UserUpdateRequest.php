<?php

namespace App\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

class UserUpdateRequest
{
    #[Assert\Length(min: 3, max: 180)]
    public ?string $username = null;

    #[Assert\Length(min: 6)]
    public ?string $password = null;

    #[Assert\Length(min: 1, max: 255)]
    public ?string $fullName = null;

    public ?array $roles = null;
}