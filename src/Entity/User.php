<?php

namespace App\Entity;

use App\Enum\UserRole;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
#[ORM\UniqueConstraint(name: 'uq_users_company_email', columns: ['company_id', 'email'])]
#[ORM\UniqueConstraint(name: 'uq_users_provider_external', columns: ['provider', 'external_id'])]
class User implements UserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'users')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Company $company = null;

    #[ORM\ManyToOne(inversedBy: 'users')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Team $team = null;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'managedUsers')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?self $manager = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\OneToMany(targetEntity: self::class, mappedBy: 'manager')]
    private Collection $managedUsers;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Email]
    #[Assert\Length(max: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 120)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 120)]
    private ?string $firstName = null;

    #[ORM\Column(length: 120)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 120)]
    private ?string $lastName = null;

    #[ORM\Column(enumType: UserRole::class)]
    private ?UserRole $role = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 50)]
    private ?string $provider = null;

    #[ORM\Column(length: 128)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 128)]
    private ?string $externalId = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $hiredAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * @var Collection<int, Onboarding>
     */
    #[ORM\OneToMany(targetEntity: Onboarding::class, mappedBy: 'user')]
    private Collection $onboardings;

    /**
     * @var Collection<int, UserTechState>
     */
    #[ORM\OneToMany(targetEntity: UserTechState::class, mappedBy: 'user')]
    private Collection $techStates;

    public function __construct()
    {
        $this->managedUsers = new ArrayCollection();
        $this->onboardings = new ArrayCollection();
        $this->techStates = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getTeam(): ?Team
    {
        return $this->team;
    }

    public function setTeam(?Team $team): static
    {
        $this->team = $team;

        return $this;
    }

    public function getManager(): ?self
    {
        return $this->manager;
    }

    public function setManager(?self $manager): static
    {
        $this->manager = $manager;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getManagedUsers(): Collection
    {
        return $this->managedUsers;
    }

    public function addManagedUser(self $user): static
    {
        if (!$this->managedUsers->contains($user)) {
            $this->managedUsers->add($user);
            $user->setManager($this);
        }

        return $this;
    }

    public function removeManagedUser(self $user): static
    {
        if ($this->managedUsers->removeElement($user)) {
            if ($user->getManager() === $this) {
                $user->setManager(null);
            }
        }

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = mb_strtolower($email);

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getFullName(): string
    {
        return trim(($this->firstName ?? '') . ' ' . ($this->lastName ?? ''));
    }

    public function getRole(): ?UserRole
    {
        return $this->role;
    }

    public function setRole(UserRole $role): static
    {
        $this->role = $role;

        return $this;
    }

    public function getProvider(): ?string
    {
        return $this->provider;
    }

    public function setProvider(string $provider): static
    {
        $this->provider = $provider;

        return $this;
    }

    public function getExternalId(): ?string
    {
        return $this->externalId;
    }

    public function setExternalId(string $externalId): static
    {
        $this->externalId = $externalId;

        return $this;
    }

    public function getHiredAt(): ?\DateTimeImmutable
    {
        return $this->hiredAt;
    }

    public function setHiredAt(?\DateTimeImmutable $hiredAt): static
    {
        $this->hiredAt = $hiredAt;

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

    /**
     * @return Collection<int, Onboarding>
     */
    public function getOnboardings(): Collection
    {
        return $this->onboardings;
    }

    public function addOnboarding(Onboarding $onboarding): static
    {
        if (!$this->onboardings->contains($onboarding)) {
            $this->onboardings->add($onboarding);
            $onboarding->setUser($this);
        }

        return $this;
    }

    public function removeOnboarding(Onboarding $onboarding): static
    {
        if ($this->onboardings->removeElement($onboarding)) {
            if ($onboarding->getUser() === $this) {
                $onboarding->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, UserTechState>
     */
    public function getTechStates(): Collection
    {
        return $this->techStates;
    }

    public function addTechState(UserTechState $techState): static
    {
        if (!$this->techStates->contains($techState)) {
            $this->techStates->add($techState);
            $techState->setUser($this);
        }

        return $this;
    }

    public function removeTechState(UserTechState $techState): static
    {
        if ($this->techStates->removeElement($techState)) {
            if ($techState->getUser() === $this) {
                $techState->setUser(null);
            }
        }

        return $this;
    }

	public function getUserIdentifier(): string
	{
		return $this->email ?? '';
	}

	public function getRoles(): array
	{
		return match ($this->role) {
			UserRole::ADMIN => ['ROLE_ADMIN', 'ROLE_HR', 'ROLE_MANAGER', 'ROLE_COLLABORATOR'],
			UserRole::HR => ['ROLE_HR', 'ROLE_COLLABORATOR'],
			UserRole::MANAGER => ['ROLE_MANAGER', 'ROLE_COLLABORATOR'],
			UserRole::COLLABORATOR => ['ROLE_COLLABORATOR'],
			default => [],
		};
	}

	public function eraseCredentials(): void
	{
		// Rien à effacer pour le MVP mock SSO
	}
}
