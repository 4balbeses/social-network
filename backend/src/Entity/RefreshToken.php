<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\User;
use DateTimeImmutable;

#[ORM\Entity(repositoryClass: \App\Repository\RefreshTokenRepository::class)]
#[ORM\Table(name: "refresh_tokens")]
#[ORM\UniqueConstraint(name: "UNIQ_REFRESH_TOKEN", columns: ["token"])]
class RefreshToken
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private User $user;

    #[ORM\Column(length: 128)]
    private string $token;

    #[ORM\Column(type: "datetime_immutable")]
    private DateTimeImmutable $expiresAt;

    #[ORM\Column(type: "datetime_immutable")]
    private DateTimeImmutable $createdAt;

    public function getId(): ?int { return $this->id; }

    public function getUser(): User { return $this->user; }
    public function setUser(User $user): void { $this->user = $user; }

    public function getToken(): string { return $this->token; }
    public function setToken(string $token): void { $this->token = $token; }

    public function getExpiresAt(): DateTimeImmutable { return $this->expiresAt; }
    public function setExpiresAt(DateTimeImmutable $dt): void { $this->expiresAt = $dt; }

    public function getCreatedAt(): DateTimeImmutable { return $this->createdAt; }
    public function setCreatedAt(DateTimeImmutable $dt): void { $this->createdAt = $dt; }

    public function isExpired(): bool { return $this->expiresAt <= new DateTimeImmutable(); }
}
