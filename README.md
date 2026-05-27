![CI](https://github.com/TON_USER/TON_REPO/actions/workflows/ci.yml/badge.svg)

# OnboardHub

Application web Symfony pour gérer l'onboarding collaborateur.

## Stack

- Symfony 7.4
- PHP 8.3+
- Twig
- Doctrine ORM + migrations
- MariaDB/MySQL
- Docker Compose
- Auth mock SSO
- HackerNews API + cache DB

## Lancer avec Docker

```bash
docker compose up -d --build
docker compose exec app composer install
docker compose exec app php bin/console doctrine:migrations:migrate
docker compose exec app php bin/console doctrine:fixtures:load
```

## Application 

http://127.0.0.1:8000


## Comptes de test

collab@onboardhub.local
manager@onboardhub.local
rh@onboardhub.local

## Commandes utiles

docker compose exec app php bin/console doctrine:schema:validate
docker compose exec app php bin/console lint:container
docker compose exec app php bin/console debug:router
docker compose exec app php bin/console doctrine:fixtures:load

## Fonctionnalités MVP

- Auth mock SSO par email
- Dashboard collaborateur
- Dashboard Manager/RH
- Création manuelle d'un onboarding
- Ajout de tâches, formations et logiciels
- Mise à jour des statuts par collaborateur
- Recalcul automatique de progression
- Veille HackerNews avec cache DB
- Docker Compose

## Hors scope Release 1

Templates onboarding
Notifications
Historique d’audit avancé


## Authentification Keycloak / OpenID Connect

L'application utilise Keycloak comme fournisseur d'identité OpenID Connect.

### Lancer l’environnement

```bash
docker compose up -d --build
```

Provider local :

- URL : http://127.0.0.1:8080
- Realm : onboardhub
- Client : onboardhub-symfony

La connexion Symfony se fait via le champ OIDC `sub`, stocké dans `users.external_id`.

## Configuration Keycloak locale

URL admin :

```text
http://127.0.0.1:8080

compte admin locale admin / admin

Realm

Créer un realm :

onboardhub
Client

Créer un client OpenID Connect :

Client ID: onboardhub-symfony
Client authentication: ON
Standard flow: ON
Direct access grants: OFF

Redirect URIs :

http://127.0.0.1:8000/connect/keycloak/check
http://localhost:8000/connect/keycloak/check

Post logout redirect URIs :

http://127.0.0.1:8000/login
http://localhost:8000/login

Web origins :

http://127.0.0.1:8000
http://localhost:8000
Users de test

Créer les utilisateurs suivants dans Keycloak :

collab@onboardhub.local
manager@onboardhub.local
rh@onboardhub.local

Pour chaque user :

Email verified: ON
mot de passe non temporaire

---

# Étape 19 — Ajouter `.env` propre

Dans `.env`, garde des valeurs génériques :

```env
KEYCLOAK_CLIENT_ID=onboardhub-symfony
KEYCLOAK_CLIENT_SECRET=change_me
KEYCLOAK_AUTH_SERVER_URL=http://127.0.0.1:8080
KEYCLOAK_REALM=onboardhub
```


## Résultats actuels

- Tests automatisés : OK
- Nombre de tests : 4
- PHPUnit : 12.5.13
- CI GitHub Actions : OK
- Workflow : `.github/workflows/ci.yml`
- Tests exécutés automatiquement à chaque push / pull request