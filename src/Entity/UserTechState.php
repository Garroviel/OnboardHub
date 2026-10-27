<?php

namespace App\Entity;

use App\Repository\UserTechStateRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserTechStateRepository::class)]
#[ORM\Table(name: 'user_tech_state')]
#[ORM\UniqueConstraint(name: 'uq_state_user_article', columns: ['user_id', 'article_id'])]
class UserTechState

// joue aussi le rôle d’entité d’association entre User et TechArticle.
// Cette entité mémorise l’état d’un article pour un utilisateur : lu ou non, sauvegardé ou non, avec les dates correspondantes.
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'techStates')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'userTechStates')]
    #[ORM\JoinColumn(name: 'article_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?TechArticle $article = null;

    #[ORM\Column]
    private ?bool $isRead = false;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $readAt = null;

    #[ORM\Column]
    private ?bool $isSaved = false;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $savedAt = null;

    public function __construct()
    {
        $this->isRead = false;
        $this->isSaved = false;
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

    public function getArticle(): ?TechArticle
    {
        return $this->article;
    }

    public function setArticle(?TechArticle $article): static
    {
        $this->article = $article;

        return $this;
    }

    public function isRead(): ?bool
    {
        return $this->isRead;
    }

    public function setIsRead(bool $isRead): static
    {
        $this->isRead = $isRead;

        return $this;
    }

    public function getReadAt(): ?\DateTimeImmutable
    {
        return $this->readAt;
    }

    public function setReadAt(?\DateTimeImmutable $readAt): static
    {
        $this->readAt = $readAt;

        return $this;
    }

    public function isSaved(): ?bool
    {
        return $this->isSaved;
    }

    public function setIsSaved(bool $isSaved): static
    {
        $this->isSaved = $isSaved;

        return $this;
    }

    public function getSavedAt(): ?\DateTimeImmutable
    {
        return $this->savedAt;
    }

    public function setSavedAt(?\DateTimeImmutable $savedAt): static
    {
        $this->savedAt = $savedAt;

        return $this;
    }
}


// Lorsque la relation entre deux entités contient elle-même des informations métier, on la transforme généralement en véritable entité.
// Le modèle est donc :
// User
//   1
//   |
//   N
// UserTechState
//   N
//   |
//   1
// TechArticle

// Cela revient conceptuellement à une relation plusieurs-à-plusieurs entre utilisateurs et articles
