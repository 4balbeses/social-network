<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\PitchRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PitchRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Post(),
        new Put(),
        new Patch(),
    ],
    normalizationContext: ['groups' => ['pitch:read']],
    denormalizationContext: ['groups' => ['pitch:write']],
)]
class Pitch
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['pitch:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['pitch:read', 'pitch:write'])]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['pitch:read', 'pitch:write'])]
    private ?string $description = null;

    #[ORM\Column(type: Types::BIGINT, nullable: true)]
    #[Groups(['pitch:read', 'pitch:write'])]
    private ?string $fundingGoal = null;

    #[ORM\Column(type: Types::BIGINT, nullable: true)]
    #[Groups(['pitch:read', 'pitch:write'])]
    private ?string $currentFunding = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['pitch:read', 'pitch:write'])]
    private ?string $deckUrl = null; // URL to pitch deck PDF/presentation

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['pitch:read', 'pitch:write'])]
    private ?string $videoUrl = null; // URL to pitch video

    #[ORM\Column]
    #[Groups(['pitch:read', 'pitch:write'])]
    private ?bool $isActive = true;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups(['pitch:read', 'pitch:write'])]
    private ?\DateTimeImmutable $deadline = null;

    #[ORM\ManyToOne(inversedBy: 'pitches')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['pitch:read', 'pitch:write'])]
    private ?Company $company = null;

    #[ORM\OneToMany(mappedBy: 'pitch', targetEntity: Investment::class)]
    #[Groups(['pitch:read'])]
    private Collection $investments;

    #[ORM\OneToMany(mappedBy: 'pitch', targetEntity: PitchComment::class, orphanRemoval: true)]
    #[Groups(['pitch:read'])]
    private Collection $comments;

    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'likedPitches')]
    #[Groups(['pitch:read'])]
    private Collection $likedBy;

    #[ORM\Column]
    #[Groups(['pitch:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['pitch:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->investments = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->likedBy = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getFundingGoal(): ?string
    {
        return $this->fundingGoal;
    }

    public function setFundingGoal(?string $fundingGoal): static
    {
        $this->fundingGoal = $fundingGoal;
        return $this;
    }

    public function getCurrentFunding(): ?string
    {
        return $this->currentFunding;
    }

    public function setCurrentFunding(?string $currentFunding): static
    {
        $this->currentFunding = $currentFunding;
        return $this;
    }

    public function getDeckUrl(): ?string
    {
        return $this->deckUrl;
    }

    public function setDeckUrl(?string $deckUrl): static
    {
        $this->deckUrl = $deckUrl;
        return $this;
    }

    public function getVideoUrl(): ?string
    {
        return $this->videoUrl;
    }

    public function setVideoUrl(?string $videoUrl): static
    {
        $this->videoUrl = $videoUrl;
        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function setActive(bool $isActive): static
    {
        $this->isActive = $isActive;
        return $this;
    }

    public function getDeadline(): ?\DateTimeImmutable
    {
        return $this->deadline;
    }

    public function setDeadline(?\DateTimeImmutable $deadline): static
    {
        $this->deadline = $deadline;
        return $this;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): static
    {
        $this->company = $company;
        return $this;
    }

    public function getInvestments(): Collection
    {
        return $this->investments;
    }

    public function addInvestment(Investment $investment): static
    {
        if (!$this->investments->contains($investment)) {
            $this->investments->add($investment);
            $investment->setPitch($this);
        }

        return $this;
    }

    public function removeInvestment(Investment $investment): static
    {
        if ($this->investments->removeElement($investment)) {
            if ($investment->getPitch() === $this) {
                $investment->setPitch(null);
            }
        }

        return $this;
    }

    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(PitchComment $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setPitch($this);
        }

        return $this;
    }

    public function removeComment(PitchComment $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            if ($comment->getPitch() === $this) {
                $comment->setPitch(null);
            }
        }

        return $this;
    }

    public function getLikedBy(): Collection
    {
        return $this->likedBy;
    }

    public function addLikedBy(User $likedBy): static
    {
        if (!$this->likedBy->contains($likedBy)) {
            $this->likedBy->add($likedBy);
            $likedBy->addLikedPitch($this);
        }

        return $this;
    }

    public function removeLikedBy(User $likedBy): static
    {
        if ($this->likedBy->removeElement($likedBy)) {
            $likedBy->removeLikedPitch($this);
        }

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }
}