<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\ArtistRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ArtistRepository::class)]
#[ApiResource]
class Artist
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $fullName = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;


    #[ORM\OneToMany(targetEntity: ArtistAlbum::class, mappedBy: 'artist', orphanRemoval: true)]
    private Collection $artistAlbums;

    public function __construct()
    {
        $this->artistAlbums = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

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
            $artistAlbum->setArtist($this);
        }

        return $this;
    }

    public function removeArtistAlbum(ArtistAlbum $artistAlbum): static
    {
        if ($this->artistAlbums->removeElement($artistAlbum)) {
            if ($artistAlbum->getArtist() === $this) {
                $artistAlbum->setArtist(null);
            }
        }

        return $this;
    }
}