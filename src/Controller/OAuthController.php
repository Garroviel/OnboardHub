<?php

namespace App\Controller;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;

final class OAuthController extends AbstractController
{
    #[Route('/connect/keycloak', name: 'connect_keycloak_start')]
    public function connect(ClientRegistry $clientRegistry): RedirectResponse
    {
        return $clientRegistry
            ->getClient('keycloak')
            ->redirect(['openid', 'profile', 'email']);
    }

    #[Route('/connect/keycloak/check', name: 'connect_keycloak_check')]
    public function check(): void
    {
        // Intercepted by OAuth authenticator.
    }
}