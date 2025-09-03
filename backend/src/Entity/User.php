<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ApiResource]
#[UniqueEntity(fields: ['username'], message: 'There is already an account with this username')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $username = null;

    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $registeredAt = null;

    #[ORM\Column(length: 255)]
    private ?string $fullName = null;

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $userType = 'entrepreneur'; // entrepreneur, investor, mentor, advisor

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $bio = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $location = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $linkedin = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $twitter = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $industry = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $expertise = null;

    #[ORM\OneToMany(targetEntity: Company::class, mappedBy: 'founder', orphanRemoval: true)]
    private Collection $companies;

    #[ORM\OneToMany(targetEntity: Investment::class, mappedBy: 'investor')]
    private Collection $investments;

    #[ORM\OneToMany(targetEntity: PitchComment::class, mappedBy: 'author', orphanRemoval: true)]
    private Collection $pitchComments;

    #[ORM\ManyToMany(targetEntity: Company::class, inversedBy: 'followers')]
    private Collection $followedCompanies;

    #[ORM\ManyToMany(targetEntity: Pitch::class, inversedBy: 'likedBy')]
    private Collection $likedPitches;

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'followers')]
    #[ORM\JoinTable(name: 'user_followers')]
    private Collection $following;

    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'following')]
    private Collection $followers;

    public function __construct()
    {
        $this->companies = new ArrayCollection();
        $this->investments = new ArrayCollection();
        $this->pitchComments = new ArrayCollection();
        $this->followedCompanies = new ArrayCollection();
        $this->likedPitches = new ArrayCollection();
        $this->following = new ArrayCollection();
        $this->followers = new ArrayCollection();
        $this->registeredAt = new \DateTime();
        $this->roles = ['ROLE_USER'];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getRegisteredAt(): ?\DateTimeInterface
    {
        return $this->registeredAt;
    }

    public function setRegisteredAt(\DateTimeInterface $registeredAt): static
    {
        $this->registeredAt = $registeredAt;

        return $this;
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

    public function getUserType(): ?string
    {
        return $this->userType;
    }

    public function setUserType(?string $userType): static
    {
        $this->userType = $userType;
        return $this;
    }

    public function getBio(): ?string
    {
        return $this->bio;
    }

    public function setBio(?string $bio): static
    {
        $this->bio = $bio;
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

    public function getLinkedin(): ?string
    {
        return $this->linkedin;
    }

    public function setLinkedin(?string $linkedin): static
    {
        $this->linkedin = $linkedin;
        return $this;
    }

    public function getTwitter(): ?string
    {
        return $this->twitter;
    }

    public function setTwitter(?string $twitter): static
    {
        $this->twitter = $twitter;
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

    public function getExpertise(): ?string
    {
        return $this->expertise;
    }

    public function setExpertise(?string $expertise): static
    {
        $this->expertise = $expertise;
        return $this;
    }

    public function getCompanies(): Collection
    {
        return $this->companies;
    }

    public function addCompany(Company $company): static
    {
        if (!$this->companies->contains($company)) {
            $this->companies->add($company);
            $company->setFounder($this);
        }
        return $this;
    }

    public function removeCompany(Company $company): static
    {
        if ($this->companies->removeElement($company)) {
            if ($company->getFounder() === $this) {
                $company->setFounder(null);
            }
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
            $investment->setInvestor($this);
        }
        return $this;
    }

    public function removeInvestment(Investment $investment): static
    {
        if ($this->investments->removeElement($investment)) {
            if ($investment->getInvestor() === $this) {
                $investment->setInvestor(null);
            }
        }
        return $this;
    }

    public function getPitchComments(): Collection
    {
        return $this->pitchComments;
    }

    public function addPitchComment(PitchComment $pitchComment): static
    {
        if (!$this->pitchComments->contains($pitchComment)) {
            $this->pitchComments->add($pitchComment);
            $pitchComment->setAuthor($this);
        }
        return $this;
    }

    public function removePitchComment(PitchComment $pitchComment): static
    {
        if ($this->pitchComments->removeElement($pitchComment)) {
            if ($pitchComment->getAuthor() === $this) {
                $pitchComment->setAuthor(null);
            }
        }
        return $this;
    }

    public function getFollowedCompanies(): Collection
    {
        return $this->followedCompanies;
    }

    public function addFollowedCompany(Company $company): static
    {
        if (!$this->followedCompanies->contains($company)) {
            $this->followedCompanies->add($company);
        }
        return $this;
    }

    public function removeFollowedCompany(Company $company): static
    {
        $this->followedCompanies->removeElement($company);
        return $this;
    }

    public function getLikedPitches(): Collection
    {
        return $this->likedPitches;
    }

    public function addLikedPitch(Pitch $pitch): static
    {
        if (!$this->likedPitches->contains($pitch)) {
            $this->likedPitches->add($pitch);
        }
        return $this;
    }

    public function removeLikedPitch(Pitch $pitch): static
    {
        $this->likedPitches->removeElement($pitch);
        return $this;
    }

    public function getFollowing(): Collection
    {
        return $this->following;
    }

    public function addFollowing(User $user): static
    {
        if (!$this->following->contains($user)) {
            $this->following->add($user);
        }
        return $this;
    }

    public function removeFollowing(User $user): static
    {
        $this->following->removeElement($user);
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
            $follower->addFollowing($this);
        }
        return $this;
    }

    public function removeFollower(User $follower): static
    {
        if ($this->followers->removeElement($follower)) {
            $follower->removeFollowing($this);
        }
        return $this;
    }
}