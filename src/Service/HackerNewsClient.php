<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class HackerNewsClient
{
    private const BASE_URL = 'https://hacker-news.firebaseio.com/v0';

    public function __construct(
        private HttpClientInterface $httpClient // HttpClientInterface injecte automatiquement le client http configuré par le framework
		// cette syntaxe combine : déclaration du paramètre du constructeur ; créer une proprété privée ; affecter le paramètre à cette propriété
		// propriété est ensuite accessible via $this->httpClient
	) {
    }

	// Récupère les identitfiants des meilleures actualités (top stories)
    public function getTopStoriesIds(int $limit = 10): array // int $limit = 10  sert à dire que le paramètre doit être un entier dont la valeur par defaut est 10, : array garantit que la méthode retourne un tableau
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
        } catch (\Throwable) {	// le / devant Throwable indique qu'il faut chercher cette interface dans le namespace global de PHP et non dans App/Service
            return null; // Si une erreur survient, la méthode retourne null au lieu de faire planter l'application
        }
    }
}