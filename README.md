![CI](https://github.com/TON_USER/TON_REPO/actions/workflows/ci.yml/badge.svg)

# OnboardHub

Application web Symfony pour gérer l'onboarding collaborateur.

## Stack technique

- Symfony 7.4
- PHP 8.3+
- Twig
- Doctrine ORM + migrations
- MariaDB / MySQL
- Docker Compose
- Keycloak + OpenID Connect
- HackerNews API + cache DB
- PHPUnit
- GitHub Actions

## Lancer avec Docker

À la racine du projet :
``` bash
docker compose up -d --build
```

Installer les dépendances PHP dans le conteneur si nécessaire :
``` bash
docker compose exec app composer install
```

Initialiser la base de données :

``` bash
docker compose exec app php bin/console doctrine:migrations:migrate
docker compose exec app php bin/console doctrine:fixtures:load
```


## Application 

http://127.0.0.1:8000

Keycloak :

http://keycloak.127.0.0.1.nip.io:8080

## Authentification Keycloak / OpenID Connect

L’application utilise Keycloak comme fournisseur d’identité OpenID Connect.

La connexion Symfony se fait via l’identifiant OIDC sub, stocké dans le champ :

users.external_id

Le provider utilisé en base est :

keycloak

## Configuration attendue dans .env.local

KEYCLOAK_AUTH_SERVER_URL=http://keycloak.127.0.0.1.nip.io:8080
KEYCLOAK_REALM=onboardhub
KEYCLOAK_CLIENT_ID=onboardhub-symfony
KEYCLOAK_CLIENT_SECRET=your_keycloak_client_secret

## Configuration Keycloak locale

Accéder à l’administration Keycloak :

http://keycloak.127.0.0.1.nip.io:8080/admin

Compte administrateur local :

admin / admin
Realm

Créer un realm :

onboardhub
Client OpenID Connect

Créer un client :

Client type: OpenID Connect
Client ID: onboardhub-symfony
Client authentication: ON
Standard flow: ON
Direct access grants: OFF

## URLs du client

Valid redirect URIs :

http://127.0.0.1:8000/connect/keycloak/check
http://localhost:8000/connect/keycloak/check

Valid post logout redirect URIs :

http://127.0.0.1:8000/login
http://localhost:8000/login

Web origins :

http://127.0.0.1:8000
http://localhost:8000

Après création du client, copier le Client secret dans .env.local.

## Comptes de test

collab@onboardhub.local
manager@onboardhub.local
rh@onboardhub.local

Pour chaque utilisateur :

Username = email
Email = email
Email verified = ON
Enabled = ON
Mot de passe non temporaire

Mot de passe de test utilisé en local :

password

Ces emails doivent correspondre aux utilisateurs créés par les fixtures Symfony.

## Commandes utiles

Valider le schéma Doctrine :

docker compose exec app php bin/console doctrine:schema:validate

Valider le container Symfony :

docker compose exec app php bin/console lint:container

Lister les routes :

docker compose exec app php bin/console debug:router

Rejouer les fixtures :

docker compose exec app php bin/console doctrine:fixtures:load

Vider le cache Symfony :

docker compose exec app php bin/console cache:clear

## Fonctionnalités MVP

- Authentification via Keycloak / OpenID Connect
- Dashboard collaborateur
- Dashboard Manager/RH
- Création manuelle d'un onboarding
- Ajout de tâches, formations et logiciels
- Mise à jour des statuts par collaborateur
- Suivi des onboardings côté Manager / RH
- Recalcul automatique de progression
- Veille HackerNews avec cache DB
- Docker Compose
- tests PHPUnit
- CI GitHub Actions

## Hors scope Release 1

Templates onboarding
Notifications
Historique d’audit avancé

## TEST

Lancer les tests automatisés :

docker compose exec app php bin/phpunit

## Résultats actuels

- Tests automatisés : OK
- Nombre de tests : 4
- PHPUnit : 12.5.13
- CI GitHub Actions : OK
- Workflow : `.github/workflows/ci.yml`
- Tests exécutés automatiquement à chaque push / pull request

## Notes Docker / Keycloak

Pour permettre à la fois au navigateur et au conteneur Symfony d’accéder à Keycloak avec la même URL, l’application utilise :

http://keycloak.127.0.0.1.nip.io:8080

Cette URL permet de faire fonctionner le flux OpenID Connect complet avec Docker Compose, sans modification du fichier hosts Windows.