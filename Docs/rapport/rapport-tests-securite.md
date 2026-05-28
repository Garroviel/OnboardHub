# Rapport Tests & Sécurité — OnboardHub bêta

## 1. Politique de tests

### Tests unitaires
- Service testé : `OnboardingProgressCalculator`
- Objectif : vérifier le calcul de progression à partir des tâches, formations et logiciels.

### Tests fonctionnels
- Accès anonyme à `/` : redirection vers `/login`
- Accessibilité de la page `/login`

### Outils
- PHPUnit
- Symfony WebTestCase
- GitHub Actions

### Résultats actuels
À compléter après exécution CI.

## 2. Analyse sécurité

### Injection SQL
Doctrine ORM est utilisé pour l’accès aux données. Les entrées utilisateur ne sont pas concaténées dans des requêtes SQL.

### XSS
Twig échappe les variables par défaut. Aucun affichage utilisateur n’utilise `|raw`.

### CSRF
Les formulaires Symfony utilisent les protections CSRF intégrées. Les actions POST de changement de statut devront recevoir une protection CSRF explicite avant livraison finale.

### Authentification
L’authentification est déléguée à Keycloak via OpenID Connect. OnboardHub ne stocke pas de mots de passe locaux.

### Contrôle d’accès
Les routes sont protégées via Symfony Security, rôles `ROLE_COLLABORATOR`, `ROLE_MANAGER`, `ROLE_HR`, `ROLE_ADMIN`.

### RGPD
Les données personnelles stockées sont limitées aux informations nécessaires à l’onboarding : email, prénom, nom, société, équipe, manager.

### Points à finaliser
- Ajouter CSRF sur les actions de changement de statut.
- Ajouter tests fonctionnels avec utilisateur authentifié.
- Ajouter tests de l’intégration HackerNews avec mock HTTP.