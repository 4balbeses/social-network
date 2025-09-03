<?php

namespace App\Dto\Tag\Request;

use Symfony\Component\Validator\Constraints as Assert;

class TagUpdateRequest
{
    #[Assert\Length(min: 1, max: 255)]
    public ?string $name = null;
}