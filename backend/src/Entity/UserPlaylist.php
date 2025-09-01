<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\UserPlaylistRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserPlaylistRepository::class)]
#[ApiResource]
class UserPlaylist
{
    #[ORM\Id]
    #[ORM\ManyToOne(inversedBy: 'userPlaylists')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Id]
    #[ORM\ManyToOne(inversedBy: 'userPlaylists')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Playlist $playlist = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $addedAt = null;

    public function __construct()
    {
        $this->addedAt = new \DateTime();
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

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