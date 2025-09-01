<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\TrackPlaylistRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TrackPlaylistRepository::class)]
#[ApiResource]
class TrackPlaylist
{
    #[ORM\Id]
    #[ORM\ManyToOne(inversedBy: 'trackPlaylists')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Track $track = null;

    #[ORM\Id]
    #[ORM\ManyToOne(inversedBy: 'trackPlaylists')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Playlist $playlist = null;

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

    public function getPlaylist(): ?Playlist
    {
        return $this->playlist;
    }

    public function setPlaylist(?Playlist $playlist): static
    {
        $this->playlist = $playlist;

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