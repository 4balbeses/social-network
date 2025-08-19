<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: "App\Repository\UserRepository")]
#[ORM\Table(name: "user")]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    private string $email;

    #[ORM\Column(type: 'string', length: 100, unique: true)]
    private string $password;

    #[ORM\Column(type: 'string', length: 50, unique: true)]
    private string $username;

    // Дата и время создания пользователя
    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $createdAt;

    // Связь с сущностью Playlist
    // Один пользователь может иметь много плейлистов
    #[ORM\OneToMany(targetEntity: Playlist::class, mappedBy: 'user')]
    private Collection $playlists;

    public function __construct()
    {
        // Инициализация коллекций при создании объекта
        $this->playlists = new ArrayCollection();

        // Установка текущей даты и времени при создании пользователя
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;
        return $this;
    }

    /*
     * Возвращает дату и время создания пользователя
     */
    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    // Методы для работы со связями и другими сущностями

    public function getPlaylists(): Collection
    {
        return $this->playlists;
    }

    /*
     * Добавляет плейлист пользователю
     */
    public function addPlaylist(Playlist $playlist): self
    {
        if (!$this->playlists->contains($playlist)) {
            $this->playlists[] = $playlist;
            $playlist->setUser($this);
        }
        return $this;
    }

    /*
     * Удаляет плейлист у пользователя
     */
    public function removePlaylist(Playlist $playlist, $default = null): self
    {
        if ($this->playlists->removeElement($playlist)) {
            if ($playlist->getUser() === $this) {
                $playlist->setUser($default);
            }
        }
        return $this;
    }

}
