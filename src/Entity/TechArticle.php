<?php

namespace App\Entity;

use App\Repository\TechArticleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TechArticleRepository::class)]
#[ORM\Table(name: 'tech_article')]
#[ORM\UniqueConstraint(name: 'uq_article_source_external', columns: ['source', 'external_id'])]
class TechArticle
{

	// ORM	Mapping entre PHP et la base
	// Assert	Validation des données dans Symfony

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 32)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 32)]
    private ?string $source = null;

    #[ORM\Column(length: 64)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 64)]
    private ?string $externalId = null;

    #[ORM\Column(length: 512)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 512)]
    private ?string $title = null;

    #[ORM\Column(length: 2048)]
    #[Assert\NotBlank]
    #[Assert\Url]
    #[Assert\Length(max: 2048)]
    private ?string $url = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $author = null;

    #[ORM\Column(nullable: true)]
    private ?int $score = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $publishedAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $fetchedAt = null;

    /**
     * @var Collection<int, UserTechState>
     */
    #[ORM\OneToMany(targetEntity: UserTechState::class, mappedBy: 'article')]
    private Collection $userTechStates;

    public function __construct()
    {
        $this->userTechStates = new ArrayCollection();
        $this->fetchedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSource(): ?string
    {
        return $this->source;
    }

    public function setSource(string $source): static
    {
        $this->source = $source;

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

    public function setUrl(string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(?string $author): static
    {
        $this->author = $author;

        return $this;
    }

    public function getScore(): ?int
    {
        return $this->score;
    }

    public function setScore(?int $score): static
    {
        $this->score = $score;

        return $this;
    }

    public function getPublishedAt(): ?\DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(?\DateTimeImmutable $publishedAt): static
    {
        $this->publishedAt = $publishedAt;

        return $this;
    }

    public function getFetchedAt(): ?\DateTimeImmutable
    {
        return $this->fetchedAt;
    }

    public function setFetchedAt(\DateTimeImmutable $fetchedAt): static
    {
        $this->fetchedAt = $fetchedAt;

        return $this;
    }

    /**
     * @return Collection<int, UserTechState>
     */
    public function getUserTechStates(): Collection
    {
        return $this->userTechStates;
    }

    public function addUserTechState(UserTechState $userTechState): static
    {
        if (!$this->userTechStates->contains($userTechState)) {
            $this->userTechStates->add($userTechState);
            $userTechState->setArticle($this);
        }

        return $this;
    }

    public function removeUserTechState(UserTechState $userTechState): static
    {
        $this->userTechStates->removeElement($userTechState);

        return $this;
    }

	// TechArticle est une entité Doctrine liée à la table tech_article. Les attributs ORM définissent le mapping de la base, tandis que les attributs Assert définissent la validation Symfony. Un article peut être associé à plusieurs UserTechState, ce qui permet de mémoriser séparément pour chaque utilisateur si l’article est lu ou sauvegardé.
}
