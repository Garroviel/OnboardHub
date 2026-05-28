<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class HackerNewsClient
{
    private const BASE_URL = 'https://hacker-news.firebaseio.com/v0';

    public function __construct(
        private HttpClientInterface $httpClient
    ) {
    }

    public function getTopStoriesIds(int $limit = 10): array
    {
        $response = $this->httpClient->request('GET', self::BASE_URL . '/topstories.json');

        $ids = $response->toArray();

        return array_slice($ids, 0, $limit);
    }

    public function getItem(int $id): ?array
    {
        try {
            $response = $this->httpClient->request('GET', self::BASE_URL . "/item/{$id}.json");

            return $response->toArray();
        } catch (\Throwable) {
            return null;
        }
    }
}