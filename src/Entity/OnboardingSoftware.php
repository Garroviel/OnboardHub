<?php

namespace App\Entity;

use App\Enum\SoftwareStatus;
use App\Repository\OnboardingSoftwareRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: OnboardingSoftwareRepository::class)]
#[ORM\Table(name: 'onboarding_software')]
class OnboardingSoftware
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'softwareItems')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Onboarding $onboarding = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 2048, nullable: true)]
    #[Assert\Url]
    #[Assert\Length(max: 2048)]
    private ?string $installUrl = null;

    #[ORM\Column(enumType: SoftwareStatus::class)]
    private ?SoftwareStatus $status = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $completedAt = null;

    #[ORM\Column(options: ['unsigned' => true])]
    private ?int $position = null;

	/**
	 * @var Collection<int, OnboardingComment>
	 */
	#[ORM\OneToMany(targetEntity: OnboardingComment::class, mappedBy: 'software', orphanRemoval: true)]
	private Collection $comments;

    public function __construct()
    {
        $this->status = SoftwareStatus::TODO;
        $this->position = 0;
        $this->comments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOnboarding(): ?Onboarding
    {
        return $this->onboarding;
    }

    public function setOnboarding(?Onboarding $onboarding): static
    {
        $this->onboarding = $onboarding;

        return $this;
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

    public function getInstallUrl(): ?string
    {
        return $this->installUrl;
    }

    public function setInstallUrl(?string $installUrl): static
    {
        $this->installUrl = $installUrl;

        return $this;
    }

    public function getStatus(): ?SoftwareStatus
    {
        return $this->status;
    }

    public function setStatus(SoftwareStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getCompletedAt(): ?\DateTimeImmutable
    {
        return $this->completedAt;
    }

    public function setCompletedAt(?\DateTimeImmutable $completedAt): static
    {
        $this->completedAt = $completedAt;

        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;

        return $this;
    }

	/**
	 * @return Collection<int, OnboardingComment>
	 */
	public function getComments(): Collection
	{
		return $this->comments;
	}

	public function addComment(OnboardingComment $comment): static
	{
		if (!$this->comments->contains($comment)) {
			$this->comments->add($comment);
			$comment->setSoftware($this);
		}

		return $this;
	}

	public function removeComment(OnboardingComment $comment): static
	{
		if ($this->comments->removeElement($comment)) {
			if ($comment->getSoftware() === $this) {
				$comment->setSoftware(null);
			}
		}

		return $this;
	}
}