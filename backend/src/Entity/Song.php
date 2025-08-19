<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: "App\Repository\SongRepository")]
class Song
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $title;

    #[ORM\Column(type: 'string', length: 255)]
    private string $artist;

    /*Продолжительность песни*/
    #[ORM\Column(type: 'integer')]
    private int $duration; // в секундах

    /*Дата добавлении песни*/
    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $filePath = null;

    // Связь с сущностью Playlist
    #[ORM\ManyToMany(targetEntity: Playlist::class, mappedBy: 'songs')]
    private Collection $playlists;

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

    public function getArtist(): string
    {
        return $this->artist;
    }

    public function setArtist(string $artist): self
    {
        $this->artist = $artist;
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
        // Делит общее кол-во времени на 60 - получаем минуты
        // floor() - округляет вниз до целого числа
        $minutes = floor($this->duration / 60);

        // Дальше делим на 60 - получим оставшиеся секунды
        $seconds = $this->duration % 60;

        // Форматируем строку:
        // %d - целое число (минуты)
        // %02d - целое число (секунды), добавив 0 для 2 цифр
        return sprintf('%d:%02d', $minutes, $seconds);
    }
}
