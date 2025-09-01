<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ApiResource]
#[UniqueEntity(fields: ['username'], message: 'There is already an account with this username')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $username = null;

    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $registeredAt = null;

    #[ORM\Column(length: 255)]
    private ?string $fullName = null;

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[ORM\OneToMany(targetEntity: Playlist::class, mappedBy: 'owner', orphanRemoval: true)]
    private Collection $playlists;

    #[ORM\OneToMany(targetEntity: Tag::class, mappedBy: 'author', orphanRemoval: true)]
    private Collection $tags;

    #[ORM\OneToMany(targetEntity: UserPlaylist::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $userPlaylists;

    #[ORM\OneToMany(targetEntity: PlaylistRating::class, mappedBy: 'ratingUser', orphanRemoval: true)]
    private Collection $playlistRatings;

    #[ORM\OneToMany(targetEntity: AlbumRating::class, mappedBy: 'ratingUser', orphanRemoval: true)]
    private Collection $albumRatings;

    #[ORM\OneToMany(targetEntity: TrackRating::class, mappedBy: 'ratingUser', orphanRemoval: true)]
    private Collection $trackRatings;

    public function __construct()
    {
        $this->playlists = new ArrayCollection();
        $this->tags = new ArrayCollection();
        $this->userPlaylists = new ArrayCollection();
        $this->playlistRatings = new ArrayCollection();
        $this->albumRatings = new ArrayCollection();
        $this->trackRatings = new ArrayCollection();
        $this->registeredAt = new \DateTime();
        $this->roles = ['ROLE_USER'];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getRegisteredAt(): ?\DateTimeInterface
    {
        return $this->registeredAt;
    }

    public function setRegisteredAt(\DateTimeInterface $registeredAt): static
    {
        $this->registeredAt = $registeredAt;

        return $this;
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(string $fullName): static
    {
        $this->fullName = $fullName;

        return $this;
    }

    public function getPlaylists(): Collection
    {
        return $this->playlists;
    }

    public function addPlaylist(Playlist $playlist): static
    {
        if (!$this->playlists->contains($playlist)) {
            $this->playlists->add($playlist);
            $playlist->setOwner($this);
        }

        return $this;
    }

    public function removePlaylist(Playlist $playlist): static
    {
        if ($this->playlists->removeElement($playlist)) {
            if ($playlist->getOwner() === $this) {
                $playlist->setOwner(null);
            }
        }

        return $this;
    }

    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): static
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
            $tag->setAuthor($this);
        }

        return $this;
    }

    public function removeTag(Tag $tag): static
    {
        if ($this->tags->removeElement($tag)) {
            if ($tag->getAuthor() === $this) {
                $tag->setAuthor(null);
            }
        }

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
            $userPlaylist->setUser($this);
        }

        return $this;
    }

    public function removeUserPlaylist(UserPlaylist $userPlaylist): static
    {
        if ($this->userPlaylists->removeElement($userPlaylist)) {
            if ($userPlaylist->getUser() === $this) {
                $userPlaylist->setUser(null);
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
            $playlistRating->setRatingUser($this);
        }

        return $this;
    }

    public function removePlaylistRating(PlaylistRating $playlistRating): static
    {
        if ($this->playlistRatings->removeElement($playlistRating)) {
            if ($playlistRating->getRatingUser() === $this) {
                $playlistRating->setRatingUser(null);
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
            $albumRating->setRatingUser($this);
        }

        return $this;
    }

    public function removeAlbumRating(AlbumRating $albumRating): static
    {
        if ($this->albumRatings->removeElement($albumRating)) {
            if ($albumRating->getRatingUser() === $this) {
                $albumRating->setRatingUser(null);
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
            $trackRating->setRatingUser($this);
        }

        return $this;
    }

    public function removeTrackRating(TrackRating $trackRating): static
    {
        if ($this->trackRatings->removeElement($trackRating)) {
            if ($trackRating->getRatingUser() === $this) {
                $trackRating->setRatingUser(null);
            }
        }

        return $this;
    }
}