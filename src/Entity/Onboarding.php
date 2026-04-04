<?php

namespace App\Entity;

use App\Enum\OnboardingStatus;
use App\Repository\OnboardingRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: OnboardingRepository::class)]
#[ORM\Table(name: 'onboarding')]
class Onboarding
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'onboardings')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $startDate = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $dueDate = null;

    #[ORM\Column(enumType: OnboardingStatus::class)]
    private ?OnboardingStatus $status = null;

    #[ORM\Column(type: Types::SMALLINT, options: ['unsigned' => true])]
    #[Assert\Range(min: 0, max: 100)]
    private ?int $progressPercent = 0;

    #[ORM\Column(nullable: true, options: ['unsigned' => true])]
    private ?int $templateId = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * @var Collection<int, OnboardingTask>
     */
    #[ORM\OneToMany(targetEntity: OnboardingTask::class, mappedBy: 'onboarding')]
    private Collection $onboardingTasks;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->status = OnboardingStatus::ACTIVE;
        $this->progressPercent = 0;
        $this->onboardingTasks = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getStartDate(): ?\DateTimeImmutable
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeImmutable $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getDueDate(): ?\DateTimeImmutable
    {
        return $this->dueDate;
    }

    public function setDueDate(?\DateTimeImmutable $dueDate): static
    {
        $this->dueDate = $dueDate;

        return $this;
    }

    public function getStatus(): ?OnboardingStatus
    {
        return $this->status;
    }

    public function setStatus(OnboardingStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getProgressPercent(): ?int
    {
        return $this->progressPercent;
    }

    public function setProgressPercent(int $progressPercent): static
    {
        $this->progressPercent = $progressPercent;

        return $this;
    }

    public function getTemplateId(): ?int
    {
        return $this->templateId;
    }

    public function setTemplateId(?int $templateId): static
    {
        $this->templateId = $templateId;

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
     * @return Collection<int, OnboardingTask>
     */
    public function getOnboardingTasks(): Collection
    {
        return $this->onboardingTasks;
    }

    public function addOnboardingTask(OnboardingTask $onboardingTask): static
    {
        if (!$this->onboardingTasks->contains($onboardingTask)) {
            $this->onboardingTasks->add($onboardingTask);
            $onboardingTask->setOnboarding($this);
        }

        return $this;
    }

    public function removeOnboardingTask(OnboardingTask $onboardingTask): static
    {
        if ($this->onboardingTasks->removeElement($onboardingTask)) {
            // set the owning side to null (unless already changed)
            if ($onboardingTask->getOnboarding() === $this) {
                $onboardingTask->setOnboarding(null);
            }
        }

        return $this;
    }
}