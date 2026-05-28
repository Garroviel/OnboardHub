<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

	// #[Route('/logout', name: 'app_logout')]
	// public function logout(): void
	// {
	// 	throw new \LogicException('Intercepted by Symfony logout.');
	// }

    #[Route('/logout', name: 'app_logout')]
    public function logout(
        RequestStack $requestStack,
        TokenStorageInterface $tokenStorage
    ): Response {
        $session = $requestStack->getSession();

        $idToken = $session->get('keycloak_id_token');

        $logoutUrl = $_ENV['KEYCLOAK_AUTH_SERVER_URL']
            . '/realms/'
            . $_ENV['KEYCLOAK_REALM']
            . '/protocol/openid-connect/logout'
            . '?client_id='
            . urlencode($_ENV['KEYCLOAK_CLIENT_ID'])
            . '&post_logout_redirect_uri='
            . urlencode('http://127.0.0.1:8000/login');

        if ($idToken) {
            $logoutUrl .= '&id_token_hint=' . urlencode($idToken);
        }

        $tokenStorage->setToken(null);
        $session->invalidate();

        return $this->redirect($logoutUrl);
    }
}
