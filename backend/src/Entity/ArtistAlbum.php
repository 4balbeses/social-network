<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\ArtistAlbumRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ArtistAlbumRepository::class)]
#[ApiResource]
class ArtistAlbum
{
    #[ORM\Id]
    #[ORM\ManyToOne(inversedBy: 'artistAlbums')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Artist $artist = null;

    #[ORM\Id]
    #[ORM\ManyToOne(inversedBy: 'artistAlbums')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Album $album = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $addedAt = null;

    public function __construct()
    {
        $this->addedAt = new \DateTime();
    }

    public function getArtist(): ?Artist
    {
        return $this->artist;
    }

    public function setArtist(?Artist $artist): static
    {
        $this->artist = $artist;

        return $this;
    }

    public function getAlbum(): ?Album
    {
        return $this->album;
    }

    public function setAlbum(?Album $album): static
    {
        $this->album = $album;

        return $this;
    }

    public function getAddedAt(): ?\DateTimeInterface
    {
        return $this->addedAt;
    }

    public function setAddedAt(\DateTimeInterface $addedAt): static
    {
        $this->addedAt = $addedAt;

        return $this;
    }
}