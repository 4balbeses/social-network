<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\CompanyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: CompanyRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Post(),
        new Put(),
        new Patch(),
    ],
    normalizationContext: ['groups' => ['company:read']],
    denormalizationContext: ['groups' => ['company:write']],
)]
class Company
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['company:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['company:read', 'company:write'])]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['company:read', 'company:write'])]
    private ?string $description = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['company:read', 'company:write'])]
    private ?string $industry = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['company:read', 'company:write'])]
    private ?string $stage = null; // seed, series-a, series-b, etc.

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['company:read', 'company:write'])]
    private ?string $website = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['company:read', 'company:write'])]
    private ?string $location = null;

    #[ORM\Column]
    #[Groups(['company:read'])]
    private ?\DateTimeImmutable $foundedAt = null;

    #[ORM\ManyToOne(inversedBy: 'companies')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['company:read', 'company:write'])]
    private ?User $founder = null;

    #[ORM\OneToMany(mappedBy: 'company', targetEntity: Pitch::class, orphanRemoval: true)]
    #[Groups(['company:read'])]
    private Collection $pitches;

    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'followedCompanies')]
    #[Groups(['company:read'])]
    private Collection $followers;

    #[ORM\OneToMany(mappedBy: 'company', targetEntity: Investment::class)]
    #[Groups(['company:read'])]
    private Collection $investments;

    #[ORM\Column(type: Types::BIGINT, nullable: true)]
    #[Groups(['company:read', 'company:write'])]
    private ?string $valuation = null;

    #[ORM\Column]
    #[Groups(['company:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['company:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->pitches = new ArrayCollection();
        $this->followers = new ArrayCollection();
        $this->investments = new ArrayCollection();
        $this->foundedAt = new \DateTimeImmutable();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getIndustry(): ?string
    {
        return $this->industry;
    }

    public function setIndustry(?string $industry): static
    {
        $this->industry = $industry;
        return $this;
    }

    public function getStage(): ?string
    {
        return $this->stage;
    }

    public function setStage(?string $stage): static
    {
        $this->stage = $stage;
        return $this;
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(?string $website): static
    {
        $this->website = $website;
        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): static
    {
        $this->location = $location;
        return $this;
    }

    public function getFoundedAt(): ?\DateTimeImmutable
    {
        return $this->foundedAt;
    }

    public function setFoundedAt(\DateTimeImmutable $foundedAt): static
    {
        $this->foundedAt = $foundedAt;
        return $this;
    }

    public function getFounder(): ?User
    {
        return $this->founder;
    }

    public function setFounder(?User $founder): static
    {
        $this->founder = $founder;
        return $this;
    }

    public function getPitches(): Collection
    {
        return $this->pitches;
    }

    public function addPitch(Pitch $pitch): static
    {
        if (!$this->pitches->contains($pitch)) {
            $this->pitches->add($pitch);
            $pitch->setCompany($this);
        }

        return $this;
    }

    public function removePitch(Pitch $pitch): static
    {
        if ($this->pitches->removeElement($pitch)) {
            if ($pitch->getCompany() === $this) {
                $pitch->setCompany(null);
            }
        }

        return $this;
    }

    public function getFollowers(): Collection
    {
        return $this->followers;
    }

    public function addFollower(User $follower): static
    {
        if (!$this->followers->contains($follower)) {
            $this->followers->add($follower);
            $follower->addFollowedCompany($this);
        }

        return $this;
    }

    public function removeFollower(User $follower): static
    {
        if ($this->followers->removeElement($follower)) {
            $follower->removeFollowedCompany($this);
        }

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
            $investment->setCompany($this);
        }

        return $this;
    }

    public function removeInvestment(Investment $investment): static
    {
        if ($this->investments->removeElement($investment)) {
            if ($investment->getCompany() === $this) {
                $investment->setCompany(null);
            }
        }

        return $this;
    }

    public function getValuation(): ?string
    {
        return $this->valuation;
    }

    public function setValuation(?string $valuation): static
    {
        $this->valuation = $valuation;
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