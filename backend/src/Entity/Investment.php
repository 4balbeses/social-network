<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Repository\InvestmentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: InvestmentRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Post(),
    ],
    normalizationContext: ['groups' => ['investment:read']],
    denormalizationContext: ['groups' => ['investment:write']],
)]
class Investment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['investment:read'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::BIGINT)]
    #[Groups(['investment:read', 'investment:write'])]
    private ?string $amount = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['investment:read', 'investment:write'])]
    private ?string $investmentType = null; // equity, debt, convertible, etc.

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2, nullable: true)]
    #[Groups(['investment:read', 'investment:write'])]
    private ?string $equityPercentage = null;

    #[ORM\ManyToOne(inversedBy: 'investments')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['investment:read', 'investment:write'])]
    private ?User $investor = null;

    #[ORM\ManyToOne(inversedBy: 'investments')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['investment:read', 'investment:write'])]
    private ?Company $company = null;

    #[ORM\ManyToOne(inversedBy: 'investments')]
    #[Groups(['investment:read', 'investment:write'])]
    private ?Pitch $pitch = null;

    #[ORM\Column(length: 50)]
    #[Groups(['investment:read', 'investment:write'])]
    private ?string $status = 'pending'; // pending, accepted, declined, completed

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['investment:read', 'investment:write'])]
    private ?string $terms = null;

    #[ORM\Column]
    #[Groups(['investment:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['investment:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setAmount(string $amount): static
    {
        $this->amount = $amount;
        return $this;
    }

    public function getInvestmentType(): ?string
    {
        return $this->investmentType;
    }

    public function setInvestmentType(?string $investmentType): static
    {
        $this->investmentType = $investmentType;
        return $this;
    }

    public function getEquityPercentage(): ?string
    {
        return $this->equityPercentage;
    }

    public function setEquityPercentage(?string $equityPercentage): static
    {
        $this->equityPercentage = $equityPercentage;
        return $this;
    }

    public function getInvestor(): ?User
    {
        return $this->investor;
    }

    public function setInvestor(?User $investor): static
    {
        $this->investor = $investor;
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

    public function getPitch(): ?Pitch
    {
        return $this->pitch;
    }

    public function setPitch(?Pitch $pitch): static
    {
        $this->pitch = $pitch;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getTerms(): ?string
    {
        return $this->terms;
    }

    public function setTerms(?string $terms): static
    {
        $this->terms = $terms;
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