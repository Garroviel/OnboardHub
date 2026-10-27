<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Contrôleur gérant la sécurité de l'application (connexion et déconnexion SSO Keycloak).
 */
class SecurityController extends AbstractController
{
    /**
     * Affiche le formulaire de connexion ou la page d'accueil d'authentification.
     *
     * @param AuthenticationUtils $authenticationUtils Utilitaire Symfony pour récupérer les erreurs d'authentification et le dernier identifiant saisi
     * 
     * @return Response Rendu de la page de login
     */
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Récupère l'erreur d'authentification s'il y en a eu une lors de la dernière tentative
        $error = $authenticationUtils->getLastAuthenticationError();

        // Récupère le dernier identifiant/nom d'utilisateur saisi pour pré-remplir le champ
        $lastUsername = $authenticationUtils->getLastUsername();

        // Rendu de la vue de connexion
        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    /**
     * Gère la déconnexion complète de l'application et du serveur SSO Keycloak (Single Sign-Out).
     *
     * Étapes réalisées :
     * 1. Récupération du `id_token` Keycloak stocké en session lors du login.
     * 2. Construction de l'URL de déconnexion OpenID Connect (OIDC) de Keycloak avec redirection post-logout.
     * 3. Nettoyage du token de sécurité Symfony et invalidation de la session locale.
     * 4. Redirection de l'utilisateur vers Keycloak pour clôturer sa session SSO globale.
     *
     * @param RequestStack $requestStack Pile de requêtes HTTP permettant d'accéder à la session
     * @param TokenStorageInterface $tokenStorage Gestionnaire de token de sécurité Symfony
     * 
     * @return Response Redirection HTTP vers l'endpoint de logout Keycloak
     */
    #[Route('/logout', name: 'app_logout')]
    public function logout(
        RequestStack $requestStack,
        TokenStorageInterface $tokenStorage
    ): Response {
        // 1. Récupération de la session utilisateur
        $session = $requestStack->getSession();

        // Récupération du jeton d'identité OIDC précédemment mis en session
        $idToken = $session->get('keycloak_id_token');

        // 2. Construction de l'URL de fin de session OpenID Connect de Keycloak
        $logoutUrl = $_ENV['KEYCLOAK_AUTH_SERVER_URL']
            . '/realms/'
            . $_ENV['KEYCLOAK_REALM']
            . '/protocol/openid-connect/logout'
            . '?client_id='
            . urlencode($_ENV['KEYCLOAK_CLIENT_ID'])
            . '&post_logout_redirect_uri='
            . urlencode('http://127.0.0.1:8000/login');

        // Ajout du paramètre id_token_hint si présent pour éviter la demande de confirmation Keycloak
        if ($idToken) {
            $logoutUrl .= '&id_token_hint=' . urlencode($idToken);
        }

        // 3. Suppression du token de sécurité de l'application Symfony
        $tokenStorage->setToken(null);
        // Destruction de la session locale de l'application
        $session->invalidate();

        // 4. Redirection vers Keycloak
        return $this->redirect($logoutUrl);
    }
}
