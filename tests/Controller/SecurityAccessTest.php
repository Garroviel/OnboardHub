<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class SecurityAccessTest extends WebTestCase
{
    public function testHomeIsProtectedForAnonymousUser(): void
    {
        $client = static::createClient();

        $client->request('GET', '/');

        self::assertResponseStatusCodeSame(401);
    }

    public function testLoginPageIsAccessible(): void
    {
        $client = static::createClient();

        $client->request('GET', '/login');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Connexion');
    }
}