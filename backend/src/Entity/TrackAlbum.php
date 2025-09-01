<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\TrackAlbumRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TrackAlbumRepository::class)]
#[ApiResource]
class TrackAlbum
{
    #[ORM\Id]
    #[ORM\ManyToOne(inversedBy: 'trackAlbums')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Track $track = null;

    #[ORM\Id]
    #[ORM\ManyToOne(inversedBy: 'trackAlbums')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Album $album = null;

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

    public function getAlbum(): ?Album
    {
        return $this->album;
    }

    public function setAlbum(?Album $album): static
    {
        $this->album = $album;

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