<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

final class KeycloakAuthenticator extends OAuth2Authenticator
{
    use TargetPathTrait;

    public function __construct(
        private ClientRegistry $clientRegistry,
        private UserRepository $userRepository,
        private EntityManagerInterface $entityManager,
        private UrlGeneratorInterface $urlGenerator
    ) {
    }

    public function supports(Request $request): ?bool
    {
        return $request->attributes->get('_route') === 'connect_keycloak_check';
    }

    public function authenticate(Request $request): Passport
    {
        $client = $this->clientRegistry->getClient('keycloak');
        $accessToken = $this->fetchAccessToken($client);
		$request->getSession()->set('keycloak_id_token', $accessToken->getValues()['id_token'] ?? null);

        return new SelfValidatingPassport(
            new UserBadge($accessToken->getToken(), function () use ($client, $accessToken): User {
                /** @var ResourceOwnerInterface $keycloakUser */
                $keycloakUser = $client->fetchUserFromToken($accessToken);

                $data = $keycloakUser->toArray();

                $externalId = $data['sub'] ?? null;
                $email = $data['email'] ?? null;

                if (!$externalId || !$email) {
                    throw new CustomUserMessageAuthenticationException('Identité Keycloak incomplète.');
                }

                $user = $this->userRepository->findOneBy([
                    'provider' => 'keycloak',
                    'externalId' => $externalId,
                ]);

                if (!$user) {
                    $user = $this->userRepository->findOneBy([
                        'email' => mb_strtolower($email),
                    ]);
                }

                if (!$user) {
                    throw new CustomUserMessageAuthenticationException('Aucun utilisateur OnboardHub associé à ce compte Keycloak.');
                }

                $user
                    ->setProvider('keycloak')
                    ->setExternalId($externalId);

                if (isset($data['given_name'])) {
                    $user->setFirstName($data['given_name']);
                }

                if (isset($data['family_name'])) {
                    $user->setLastName($data['family_name']);
                }

                $this->entityManager->flush();

                return $user;
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, $token, string $firewallName): ?Response
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        return new RedirectResponse($this->urlGenerator->generate('app_dashboard'));
    }

    public function onAuthenticationFailure(Request $request, \Symfony\Component\Security\Core\Exception\AuthenticationException $exception): Response
    {
        return new RedirectResponse($this->urlGenerator->generate('app_login'));
    }
}