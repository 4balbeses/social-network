<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: "App\Repository\ArtistRepository")]
#[ORM\Table(name: "artist")]
class Artist
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(Types::STRING, length: 255)]
    private string $fullName;

    /*Описание артиста*/
    #[ORM\Column(Types::TEXT, nullable: true)]
    private ?string $description = null;

    /* Обратная связь OneToMany с сущностью Track */
    #[ORM\OneToMany(targetEntity: Track::class, mappedBy: 'artist')]
    private Collection $tracks;

    public function __construct()
    {
        $this->tracks = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFullName(): string
    {
        return $this->fullName;
    }

    public function setFullName(string $fullName): self
    {
        $this->fullName = $fullName;

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

    /**
     * @return Collection|Track[]
     */
    public function getTracks(): Collection
    {
        return $this->tracks;
    }

    public function addTrack(Track $track): self
    {
        if (!$this->tracks->contains($track)) {
            $this->tracks[] = $track;
            $track->setArtist($this);
        }
        return $this;
    }

    public function removeTrack(Track $track, $default = null): self
    {
        if ($this->tracks->removeElement($track)) {
            // set the owning side to null (unless already changed)
            if ($track->getArtist() === $this) {
                $track->setArtist(null);
            }
        }
        return $this;
    }

    public function __toString(): string
    {
        return $this->fullName;
    }
}

