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