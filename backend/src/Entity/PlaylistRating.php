<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\PlaylistRatingRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlaylistRatingRepository::class)]
#[ApiResource]
class PlaylistRating
{
    #[ORM\Id]
    #[ORM\ManyToOne(inversedBy: 'playlistRatings')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $ratingUser = null;

    #[ORM\Id]
    #[ORM\ManyToOne(inversedBy: 'playlistRatings')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Playlist $ratedPlaylist = null;

    #[ORM\Column(length: 50)]
    private ?string $rateType = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $ratedAt = null;

    public function __construct()
    {
        $this->ratedAt = new \DateTime();
    }

    public function getRatingUser(): ?User
    {
        return $this->ratingUser;
    }

    public function setRatingUser(?User $ratingUser): static
    {
        $this->ratingUser = $ratingUser;

        return $this;
    }

    public function getRatedPlaylist(): ?Playlist
    {
        return $this->ratedPlaylist;
    }

    public function setRatedPlaylist(?Playlist $ratedPlaylist): static
    {
        $this->ratedPlaylist = $ratedPlaylist;

        return $this;
    }

    public function getRateType(): ?string
    {
        return $this->rateType;
    }

    public function setRateType(string $rateType): static
    {
        $this->rateType = $rateType;

        return $this;
    }

    public function getRatedAt(): ?\DateTimeInterface
    {
        return $this->ratedAt;
    }

    public function setRatedAt(\DateTimeInterface $ratedAt): static
    {
        $this->ratedAt = $ratedAt;

        return $this;
    }
}