<?php

namespace App\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

class TagCreateRequest
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 1, max: 255)]
    public string $name;
}