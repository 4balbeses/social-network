<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity]
#[ORM\Table(name: '`user`')]
#[UniqueEntity(fields: ['email'], message: 'This email is already used.')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[ORM\Column(length: 255)]
    private string $password = '';

    public function getId(): ?int { return $this->id; }

    public function getEmail(): ?string { return $this->email; }
    public function setEmail(string $email): self { $this->email = $email; return $this; }

    public function getUserIdentifier(): string { return (string)($this->email ?? ''); }
    public function getUsername(): string { return $this->getUserIdentifier(); }

    /** @return list<string> */
    public function getRoles(): array {
        $roles = $this->roles;
        if (!in_array('ROLE_USER', $roles, true)) { $roles[] = 'ROLE_USER'; }
        return array_values(array_unique($roles));
    }
    /** @param list<string> $roles */
    public function setRoles(array $roles): self { $this->roles = array_values(array_unique($roles)); return $this; }

    public function getPassword(): string { return $this->password; }
    public function setPassword(string $hashedPassword): self { $this->password = $hashedPassword; return $this; }

    public function eraseCredentials(): void {}
    public function getSalt(): ?string { return null; }
}
