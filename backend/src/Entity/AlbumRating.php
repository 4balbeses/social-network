<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\AlbumRatingRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AlbumRatingRepository::class)]
#[ApiResource]
class AlbumRating
{
    #[ORM\Id]
    #[ORM\ManyToOne(inversedBy: 'albumRatings')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $ratingUser = null;

    #[ORM\Id]
    #[ORM\ManyToOne(inversedBy: 'albumRatings')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Album $ratedAlbum = null;

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

    public function getRatedAlbum(): ?Album
    {
        return $this->ratedAlbum;
    }

    public function setRatedAlbum(?Album $ratedAlbum): static
    {
        $this->ratedAlbum = $ratedAlbum;

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