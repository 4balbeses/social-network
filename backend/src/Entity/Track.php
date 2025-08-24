<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: "App\Repository\TrackRepository")]
class Track
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $title;

    /*Описание трека*/
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    /*Продолжительность песни*/
    #[ORM\Column(type: 'integer')]
    private int $duration; // в секундах

    /*Дата добавления песни*/
    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $filePath = null;

    // Связь с сущностью Genre
    #[ORM\ManyToOne(targetEntity: Genre::class, inversedBy: 'tracks')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Genre $genre = null;

    // Связь с сущностью Playlist
    #[ORM\ManyToMany(targetEntity: Playlist::class, mappedBy: 'tracks')]
    private Collection $playlists;

    #[ORM\ManyToOne(targetEntity: Artist::class, inversedBy: 'tracks')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Artist $artist = null;

    public function __construct()
    {
        $this->playlists = new ArrayCollection();
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getArtist(): ?Artist
    {
        return $this->artist;
    }

    public function setArtist(?Artist $artist): self
    {
        $this->artist = $artist;
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

    public function getDuration(): int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): self
    {
        $this->duration = $duration;
        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    public function setFilePath(?string $filePath): self
    {
        $this->filePath = $filePath;
        return $this;
    }

    public function getGenre(): ?Genre
    {
        return $this->genre;
    }

    public function setGenre(?Genre $genre): self
    {
        $this->genre = $genre;
        return $this;
    }

    /**
     * @return Collection|Playlist[]
     */
    public function getPlaylists(): Playlist
    {
        return $this->playlists;
    }

    public function addPlaylist(Playlist $playlist): self
    {
        if (!$this->playlists->contains($playlist)) {
            $this->playlists[] = $playlist;
            $playlist->addSong($this);
        }
        return $this;
    }

    public function removePlaylist(Playlist $playlist): self
    {
        if ($this->playlists->removeElement($playlist)) {
            $playlist->removeSong($this);
        }
        return $this;
    }

    /* Метод преобразует длительность песни в секундах в формат
     * М:СС
     * Например:
    * - 125 секунд → "2:05"
    * - 30 секунд → "0:30"
    * - 360 секунд → "6:00"
     * */
    public function getFormattedDuration(): string
    {

        $hours = floor($this->duration / 3600);
        $minutes = floor(($this->duration % 3600) / 60);
        $seconds = $this->duration % 60;

        if ($hours > 0) {
            return sprintf('%d:%02d:%02d', $hours, $minutes, $seconds);
        }
        return sprintf('%d:%02d', $minutes, $seconds);
    }
}
