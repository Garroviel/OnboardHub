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
    #[ORM\OneToMany(mappedBy: 'onboarding', targetEntity: OnboardingTask::class)]
    private Collection $tasks;

    /**
     * @var Collection<int, OnboardingTraining>
     */
    #[ORM\OneToMany(mappedBy: 'onboarding', targetEntity: OnboardingTraining::class)]
    private Collection $trainings;

    /**
     * @var Collection<int, OnboardingSoftware>
     */
    #[ORM\OneToMany(mappedBy: 'onboarding', targetEntity: OnboardingSoftware::class)]
    private Collection $softwareItems;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->status = OnboardingStatus::ACTIVE;
        $this->progressPercent = 0;
        $this->tasks = new ArrayCollection();
        $this->trainings = new ArrayCollection();
        $this->softwareItems = new ArrayCollection();
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
    public function getTasks(): Collection
    {
        return $this->tasks;
    }

    public function addTask(OnboardingTask $task): static
    {
        if (!$this->tasks->contains($task)) {
            $this->tasks->add($task);
            $task->setOnboarding($this);
        }

        return $this;
    }

    public function removeTask(OnboardingTask $task): static
    {
        if ($this->tasks->removeElement($task)) {
            if ($task->getOnboarding() === $this) {
                $task->setOnboarding(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, OnboardingTraining>
     */
    public function getTrainings(): Collection
    {
        return $this->trainings;
    }

    public function addTraining(OnboardingTraining $training): static
    {
        if (!$this->trainings->contains($training)) {
            $this->trainings->add($training);
            $training->setOnboarding($this);
        }

        return $this;
    }

    public function removeTraining(OnboardingTraining $training): static
    {
        if ($this->trainings->removeElement($training)) {
            if ($training->getOnboarding() === $this) {
                $training->setOnboarding(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, OnboardingSoftware>
     */
    public function getSoftwareItems(): Collection
    {
        return $this->softwareItems;
    }

    public function addSoftwareItem(OnboardingSoftware $softwareItem): static
    {
        if (!$this->softwareItems->contains($softwareItem)) {
            $this->softwareItems->add($softwareItem);
            $softwareItem->setOnboarding($this);
        }

        return $this;
    }

    public function removeSoftwareItem(OnboardingSoftware $softwareItem): static
    {
        if ($this->softwareItems->removeElement($softwareItem)) {
            if ($softwareItem->getOnboarding() === $this) {
                $softwareItem->setOnboarding(null);
            }
        }

        return $this;
    }
}