<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\TagRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TagRepository::class)]
#[ApiResource]
class Tag
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'tags')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $author = null;

    #[ORM\OneToMany(targetEntity: TrackTag::class, mappedBy: 'tag', orphanRemoval: true)]
    private Collection $trackTags;

    public function __construct()
    {
        $this->trackTags = new ArrayCollection();
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

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): static
    {
        $this->author = $author;

        return $this;
    }

    public function getTrackTags(): Collection
    {
        return $this->trackTags;
    }

    public function addTrackTag(TrackTag $trackTag): static
    {
        if (!$this->trackTags->contains($trackTag)) {
            $this->trackTags->add($trackTag);
            $trackTag->setTag($this);
        }

        return $this;
    }

    public function removeTrackTag(TrackTag $trackTag): static
    {
        if ($this->trackTags->removeElement($trackTag)) {
            if ($trackTag->getTag() === $this) {
                $trackTag->setTag(null);
            }
        }

        return $this;
    }
}