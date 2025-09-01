<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\TrackTagRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TrackTagRepository::class)]
#[ApiResource]
class TrackTag
{
    #[ORM\Id]
    #[ORM\ManyToOne(inversedBy: 'trackTags')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Track $track = null;

    #[ORM\Id]
    #[ORM\ManyToOne(inversedBy: 'trackTags')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Tag $tag = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $addedAt = null;

    public function __construct()
    {
        $this->addedAt = new \DateTime();
    }

    public function getTrack(): ?Track
    {
        return $this->track;
    }

    public function setTrack(?Track $track): static
    {
        $this->track = $track;

        return $this;
    }

    public function getTag(): ?Tag
    {
        return $this->tag;
    }

    public function setTag(?Tag $tag): static
    {
        $this->tag = $tag;

        return $this;
    }

    public function getAddedAt(): ?\DateTimeInterface
    {
        return $this->addedAt;
    }

    public function setAddedAt(\DateTimeInterface $addedAt): static
    {
        $this->addedAt = $addedAt;

        return $this;
    }
}