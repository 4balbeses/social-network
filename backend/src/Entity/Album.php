<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\AlbumRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AlbumRepository::class)]
#[ApiResource]
class Album
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\OneToMany(targetEntity: ArtistAlbum::class, mappedBy: 'album', orphanRemoval: true)]
    private Collection $artistAlbums;

    #[ORM\OneToMany(targetEntity: TrackAlbum::class, mappedBy: 'album', orphanRemoval: true)]
    private Collection $trackAlbums;

    #[ORM\OneToMany(targetEntity: AlbumRating::class, mappedBy: 'ratedAlbum', orphanRemoval: true)]
    private Collection $albumRatings;

    public function __construct()
    {
        $this->artistAlbums = new ArrayCollection();
        $this->trackAlbums = new ArrayCollection();
        $this->albumRatings = new ArrayCollection();
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getArtistAlbums(): Collection
    {
        return $this->artistAlbums;
    }

    public function addArtistAlbum(ArtistAlbum $artistAlbum): static
    {
        if (!$this->artistAlbums->contains($artistAlbum)) {
            $this->artistAlbums->add($artistAlbum);
            $artistAlbum->setAlbum($this);
        }

        return $this;
    }

    public function removeArtistAlbum(ArtistAlbum $artistAlbum): static
    {
        if ($this->artistAlbums->removeElement($artistAlbum)) {
            if ($artistAlbum->getAlbum() === $this) {
                $artistAlbum->setAlbum(null);
            }
        }

        return $this;
    }

    public function getTrackAlbums(): Collection
    {
        return $this->trackAlbums;
    }

    public function addTrackAlbum(TrackAlbum $trackAlbum): static
    {
        if (!$this->trackAlbums->contains($trackAlbum)) {
            $this->trackAlbums->add($trackAlbum);
            $trackAlbum->setAlbum($this);
        }

        return $this;
    }

    public function removeTrackAlbum(TrackAlbum $trackAlbum): static
    {
        if ($this->trackAlbums->removeElement($trackAlbum)) {
            if ($trackAlbum->getAlbum() === $this) {
                $trackAlbum->setAlbum(null);
            }
        }

        return $this;
    }

    public function getAlbumRatings(): Collection
    {
        return $this->albumRatings;
    }

    public function addAlbumRating(AlbumRating $albumRating): static
    {
        if (!$this->albumRatings->contains($albumRating)) {
            $this->albumRatings->add($albumRating);
            $albumRating->setRatedAlbum($this);
        }

        return $this;
    }

    public function removeAlbumRating(AlbumRating $albumRating): static
    {
        if ($this->albumRatings->removeElement($albumRating)) {
            if ($albumRating->getRatedAlbum() === $this) {
                $albumRating->setRatedAlbum(null);
            }
        }

        return $this;
    }
}