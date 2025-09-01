<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\PlaylistRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlaylistRepository::class)]
#[ApiResource]
class Playlist
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

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $isPublic = false;

    #[ORM\ManyToOne(inversedBy: 'playlists')]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $owner = null;

    #[ORM\OneToMany(targetEntity: UserPlaylist::class, mappedBy: 'playlist', orphanRemoval: true)]
    private Collection $userPlaylists;

    #[ORM\OneToMany(targetEntity: TrackPlaylist::class, mappedBy: 'playlist', orphanRemoval: true)]
    private Collection $trackPlaylists;

    #[ORM\OneToMany(targetEntity: PlaylistRating::class, mappedBy: 'ratedPlaylist', orphanRemoval: true)]
    private Collection $playlistRatings;

    public function __construct()
    {
        $this->userPlaylists = new ArrayCollection();
        $this->trackPlaylists = new ArrayCollection();
        $this->playlistRatings = new ArrayCollection();
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

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;

        return $this;
    }

    public function getUserPlaylists(): Collection
    {
        return $this->userPlaylists;
    }

    public function addUserPlaylist(UserPlaylist $userPlaylist): static
    {
        if (!$this->userPlaylists->contains($userPlaylist)) {
            $this->userPlaylists->add($userPlaylist);
            $userPlaylist->setPlaylist($this);
        }

        return $this;
    }

    public function removeUserPlaylist(UserPlaylist $userPlaylist): static
    {
        if ($this->userPlaylists->removeElement($userPlaylist)) {
            if ($userPlaylist->getPlaylist() === $this) {
                $userPlaylist->setPlaylist(null);
            }
        }

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
            $trackPlaylist->setPlaylist($this);
        }

        return $this;
    }

    public function removeTrackPlaylist(TrackPlaylist $trackPlaylist): static
    {
        if ($this->trackPlaylists->removeElement($trackPlaylist)) {
            if ($trackPlaylist->getPlaylist() === $this) {
                $trackPlaylist->setPlaylist(null);
            }
        }

        return $this;
    }

    public function getPlaylistRatings(): Collection
    {
        return $this->playlistRatings;
    }

    public function addPlaylistRating(PlaylistRating $playlistRating): static
    {
        if (!$this->playlistRatings->contains($playlistRating)) {
            $this->playlistRatings->add($playlistRating);
            $playlistRating->setRatedPlaylist($this);
        }

        return $this;
    }

    public function removePlaylistRating(PlaylistRating $playlistRating): static
    {
        if ($this->playlistRatings->removeElement($playlistRating)) {
            if ($playlistRating->getRatedPlaylist() === $this) {
                $playlistRating->setRatedPlaylist(null);
            }
        }

        return $this;
    }

    public function isPublic(): bool
    {
        return $this->isPublic;
    }

    public function setIsPublic(bool $isPublic): static
    {
        $this->isPublic = $isPublic;

        return $this;
    }
}