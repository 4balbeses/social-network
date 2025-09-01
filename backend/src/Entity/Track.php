<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\TrackRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TrackRepository::class)]
#[ApiResource]
class Track
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'tracks')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Media $trackFile = null;

    #[ORM\ManyToOne(inversedBy: 'tracks')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Genre $genre = null;

    #[ORM\OneToMany(targetEntity: TrackPlaylist::class, mappedBy: 'track', orphanRemoval: true)]
    private Collection $trackPlaylists;

    #[ORM\OneToMany(targetEntity: TrackAlbum::class, mappedBy: 'track', orphanRemoval: true)]
    private Collection $trackAlbums;

    #[ORM\OneToMany(targetEntity: TrackTag::class, mappedBy: 'track', orphanRemoval: true)]
    private Collection $trackTags;

    #[ORM\OneToMany(targetEntity: TrackRating::class, mappedBy: 'ratedTrack', orphanRemoval: true)]
    private Collection $trackRatings;

    public function __construct()
    {
        $this->trackPlaylists = new ArrayCollection();
        $this->trackAlbums = new ArrayCollection();
        $this->trackTags = new ArrayCollection();
        $this->trackRatings = new ArrayCollection();
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

    public function getTrackFile(): ?Media
    {
        return $this->trackFile;
    }

    public function setTrackFile(?Media $trackFile): static
    {
        $this->trackFile = $trackFile;

        return $this;
    }

    public function getGenre(): ?Genre
    {
        return $this->genre;
    }

    public function setGenre(?Genre $genre): static
    {
        $this->genre = $genre;

        return $this;
    }

    public function getTrackPlaylists(): Collection
    {
        return $this->trackPlaylists;
    }

    public function addTrackPlaylist(TrackPlaylist $trackPlaylist): static
    {
        if (!$this->trackPlaylists->contains($trackPlaylist)) {
            $this->trackPlaylists->add($trackPlaylist);
            $trackPlaylist->setTrack($this);
        }

        return $this;
    }

    public function removeTrackPlaylist(TrackPlaylist $trackPlaylist): static
    {
        if ($this->trackPlaylists->removeElement($trackPlaylist)) {
            if ($trackPlaylist->getTrack() === $this) {
                $trackPlaylist->setTrack(null);
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
            $trackAlbum->setTrack($this);
        }

        return $this;
    }

    public function removeTrackAlbum(TrackAlbum $trackAlbum): static
    {
        if ($this->trackAlbums->removeElement($trackAlbum)) {
            if ($trackAlbum->getTrack() === $this) {
                $trackAlbum->setTrack(null);
            }
        }

        return $this;
    }

    public function getTrackTags(): Collection
    {
        return $this->trackTags;
    }

    public function addTrackTag(TrackTag $trackTag): static
    {
        if (!$this->trackTags->contains($trackTag)) {
            $this->trackTags->add($trackTag);
            $trackTag->setTrack($this);
        }

        return $this;
    }

    public function removeTrackTag(TrackTag $trackTag): static
    {
        if ($this->trackTags->removeElement($trackTag)) {
            if ($trackTag->getTrack() === $this) {
                $trackTag->setTrack(null);
            }
        }

        return $this;
    }

    public function getTrackRatings(): Collection
    {
        return $this->trackRatings;
    }

    public function addTrackRating(TrackRating $trackRating): static
    {
        if (!$this->trackRatings->contains($trackRating)) {
            $this->trackRatings->add($trackRating);
            $trackRating->setRatedTrack($this);
        }

        return $this;
    }

    public function removeTrackRating(TrackRating $trackRating): static
    {
        if ($this->trackRatings->removeElement($trackRating)) {
            if ($trackRating->getRatedTrack() === $this) {
                $trackRating->setRatedTrack(null);
            }
        }

        return $this;
    }
}