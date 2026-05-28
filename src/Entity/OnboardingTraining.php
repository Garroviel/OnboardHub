<?php

namespace App\Entity;

use App\Enum\TrainingStatus;
use App\Repository\OnboardingTrainingRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: OnboardingTrainingRepository::class)]
#[ORM\Table(name: 'onboarding_training')]
class OnboardingTraining
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'trainings')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Onboarding $onboarding = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 2048, nullable: true)]
    #[Assert\Url]
    #[Assert\Length(max: 2048)]
    private ?string $url = null;

    #[ORM\Column(enumType: TrainingStatus::class)]
    private ?TrainingStatus $status = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $completedAt = null;

    #[ORM\Column(options: ['unsigned' => true])]
    private ?int $position = null;

    public function __construct()
    {
        $this->status = TrainingStatus::TODO;
        $this->position = 0;
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

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function getStatus(): ?TrainingStatus
    {
        return $this->status;
    }

    public function setStatus(TrainingStatus $status): static
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
}
