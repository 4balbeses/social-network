<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\TrackRatingRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TrackRatingRepository::class)]
#[ApiResource]
class TrackRating
{
    #[ORM\Id]
    #[ORM\ManyToOne(inversedBy: 'trackRatings')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $ratingUser = null;

    #[ORM\Id]
    #[ORM\ManyToOne(inversedBy: 'trackRatings')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Track $ratedTrack = null;

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

    public function getRatedTrack(): ?Track
    {
        return $this->ratedTrack;
    }

    public function setRatedTrack(?Track $ratedTrack): static
    {
        $this->ratedTrack = $ratedTrack;

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