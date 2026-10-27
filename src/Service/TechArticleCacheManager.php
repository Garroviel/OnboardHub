<?php

namespace App\Service;

use App\Entity\TechArticle; // l'entité représent un article en base
use App\Repository\TechArticleRepository; // recherche les articles existants
use Doctrine\ORM\EntityManagerInterface; // gère l'enregistrement et la persistance des entités en base avec Doctrine ORM

class TechArticleCacheManager
{
    public function __construct(
        private TechArticleRepository $repository,
        private EntityManagerInterface $em
    ) {
    }
	// Reçoit les données Hacker News sous la forme d'un tableau associatif
    public function saveIfNotExists(array $data): void // paramètre doit être un tableau, void : la méthode ne retourne aucune valeur, elle prépare eventuellement un article pour son enregistrement en base
    {
		// isset vérifie que trois vlauer existe et ne valent pas null 
		// ! inverse le résultat 
		// donc Si au moins l’une de ces données est absente, arrêter la méthode.
        if (!isset($data['id'], $data['title'], $data['url'])) {
            return;
        }

        $existing = $this->repository->findOneBy([
            'source' => 'HN',	// doit venir de Hacker News
            'externalId' => (string) $data['id'],	// Son identifiant externe doit correspondre à l’identifiant renvoyé par l’API.
        ]);

        if ($existing) {
            return;
        }

        $article = new TechArticle();
        $article
            ->setSource('HN')
            ->setExternalId((string) $data['id'])
            ->setTitle($data['title'])
            ->setUrl($data['url'])
            ->setAuthor($data['by'] ?? null)
            ->setScore($data['score'] ?? null)
            ->setPublishedAt(isset($data['time']) ? (new \DateTimeImmutable())->setTimestamp($data['time']) : null);

        $this->em->persist($article);
    }

    public function flush(): void
    {
        $this->em->flush();
    }
}