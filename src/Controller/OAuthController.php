<?php

namespace App\Controller;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Contrôleur orchestrant le flux d'authentification OAuth2 / OpenID Connect avec Keycloak.
 */
final class OAuthController extends AbstractController
{
    /**
     * Initialise le flux d'autorisation OAuth2 vers Keycloak.
     *
     * Redirige le navigateur de l'utilisateur vers la page de connexion SSO de Keycloak
     * en demandant les scopes d'accès nécessaires (openid, profile, email).
     *
     * @param ClientRegistry $clientRegistry Registre des clients OAuth2 configurés dans Symfony
     * 
     * @return RedirectResponse Redirection vers le serveur d'authentification Keycloak
     */
    #[Route('/connect/keycloak', name: 'connect_keycloak_start')]
    public function connect(ClientRegistry $clientRegistry): RedirectResponse
    {
        // Récupère le client 'keycloak' et génère la redirection avec les scopes demandés
        return $clientRegistry
            ->getClient('keycloak')
            ->redirect(['openid', 'profile', 'email']);
    }

    /**
     * Route de retour (callback) après l'authentification sur Keycloak.
     *
     * Cette action ne contient pas de code métier direct car la requête HTTP entrante
     * est interceptée automatiquement par l'authentificateur Symfony (KeycloakAuthenticator).
     */
    #[Route('/connect/keycloak/check', name: 'connect_keycloak_check')]
    public function check(): void
    {
        // Intercepté par le système de sécurité Symfony (OAuth authenticator).
    }
}