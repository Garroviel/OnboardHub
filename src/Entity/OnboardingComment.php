<?php

namespace App\Entity;

use App\Repository\OnboardingCommentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OnboardingCommentRepository::class)]
class OnboardingComment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $author = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $message = null;

    #[ORM\ManyToOne(inversedBy: 'comments')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    private ?OnboardingTask $task = null;

    #[ORM\ManyToOne(inversedBy: 'comments')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    private ?OnboardingTraining $training = null;

    #[ORM\ManyToOne(inversedBy: 'comments')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    private ?OnboardingSoftware $software = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): static
    {
        $this->author = $author;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): static
    {
        $this->message = trim($message);

        return $this;
    }

    public function getTask(): ?OnboardingTask
    {
        return $this->task;
    }

    public function setTask(?OnboardingTask $task): static
    {
        $this->task = $task;

        return $this;
    }

    public function getTraining(): ?OnboardingTraining
    {
        return $this->training;
    }

    public function setTraining(?OnboardingTraining $training): static
    {
        $this->training = $training;

        return $this;
    }

    public function getSoftware(): ?OnboardingSoftware
    {
        return $this->software;
    }

    public function setSoftware(?OnboardingSoftware $software): static
    {
        $this->software = $software;

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
}
