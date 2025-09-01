<?php

namespace App\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

class UserCreateRequest
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 180)]
    public string $username;

    #[Assert\NotBlank]
    #[Assert\Length(min: 6)]
    public string $password;

    #[Assert\NotBlank]
    #[Assert\Length(min: 1, max: 255)]
    public string $fullName;

    public array $roles = ['ROLE_USER'];
}