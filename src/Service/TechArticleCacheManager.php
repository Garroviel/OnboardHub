<?php

namespace App\Service;

use App\Entity\TechArticle;
use App\Repository\TechArticleRepository;
use Doctrine\ORM\EntityManagerInterface;

class TechArticleCacheManager
{
    public function __construct(
        private TechArticleRepository $repository,
        private EntityManagerInterface $em
    ) {
    }

    public function saveIfNotExists(array $data): void
    {
        if (!isset($data['id'], $data['title'], $data['url'])) {
            return;
        }

        $existing = $this->repository->findOneBy([
            'source' => 'HN',
            'externalId' => (string) $data['id'],
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