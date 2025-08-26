<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: "App\Repository\GenreRepository")]
#[ORM\Table(name: "genre")]
class Genre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(Types::STRING, length: 255)]
    private string $name;

    #[ORM\Column(Types::STRING, length: 255, nullable: true)]
    private string $description;

    #[ORM\OneToMany(targetEntity: Track::class, mappedBy: 'genre')]
    private Collection $tracks;

    public function __construct()
    {
        $this->tracks = new ArrayCollection();
    }

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

    /**
     * @return Collection|Track[]
     */
    public function getTracks(): Collection
    {
        return $this->tracks;
    }

    public function addSong(Track $track): self
    {
        if (!$this->tracks->contains($track)) {
            $this->tracks[] = $track;
            $track->setGenre($this);
        }
        return $this;
    }

    public function removeSong(Track $track): self
    {
        if ($this->tracks->removeElement($track)) {
            if ($track->getGenre() === $this) {
                $track->setGenre(null);
            }
        }
        return $this;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
