<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: "App\Repository\PlaylistRepository")]
#[ORM\Table(name: "playlist")]
class Playlist
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 100)]
    private string $name;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $createdAt;

    /*
     * Приватный или нет плейлист
     * */
    #[ORM\Column(type: 'boolean')]
    private bool $isPublic = false;

    // Связь многие-к-одному с сущностью User
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'playlists')]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    // Связь с сущностью Track
    #[ORM\ManyToMany(targetEntity: Track::class, inversedBy: 'playlists')]
    private Collection $tracks;

    public function __construct()
    {
        $this->tracks = new ArrayCollection();
        $this->createdAt = new \DateTime();
    }

    // Геттеры и сеттеры

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function isPublic(): bool
    {
        return $this->isPublic;
    }

    public function setIsPublic(bool $isPublic): self
    {
        $this->isPublic = $isPublic;
        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return Collection|Track[]
     */
    public function getSongs(): Track
    {
        return $this->tracks;
    }

    public function addSong(Track $track): self
    {
        if (!$this->tracks->contains($track)) {
            $this->tracks[] = $track;
        }
        return $this;
    }

    public function removeSong(Track $track): self
    {
        $this->tracks->removeElement($track);
        return $this;
    }
}
