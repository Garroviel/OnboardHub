<?php

namespace App\Controller;

use App\Entity\Onboarding;
use App\Repository\OnboardingRepository;
use App\Repository\TechArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\HackerNewsClient;
use App\Service\TechArticleCacheManager;
use App\Service\OnboardingProgressCalculator;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UserRepository;
use App\Enum\UserRole;

/**
 * Contrôleur principal pour l'affichage du tableau de bord (Dashboard).
 * 
 * Il gère la redirection de l'affichage selon le rôle de l'utilisateur (Manager/RH/Admin vs Collaborateur)
 * et assure la synchronisation / récupération du flux de veille technologique (HackerNews).
 */
final class DashboardController extends AbstractController
{
    /**
     * Point d'entrée principal de l'application (Route racine '/').
     *
     * Étapes réalisées :
     * 1. Vérification de l'authentification de l'utilisateur.
     * 2. Récupération et mise en cache des 5 meilleurs articles HackerNews (avec gestion en mode dégradé).
     * 3. Récupération des 5 derniers articles stockés en base de données.
     * 4. Affichage du tableau de bord adapté :
     *    - Vue Manager/RH/Admin : liste des 10 derniers onboardings de l'entreprise.
     *    - Vue Collaborateur : dernier onboarding de l'utilisateur avec recalcul automatique de sa progression.
     *
     * @param OnboardingRepository $onboardingRepository Répertoire pour accéder aux parcours d'onboarding
     * @param TechArticleRepository $techArticleRepository Répertoire pour accéder aux articles de veille
     * @param HackerNewsClient $hnClient Client HTTP pour interroger l'API HackerNews
     * @param TechArticleCacheManager $cacheManager Gestionnaire de persistance et de dédoublonnage des articles
     * @param OnboardingProgressCalculator $progressCalculator Service de calcul du pourcentage de complétion
     * @param EntityManagerInterface $entityManager Gestionnaire d'entités Doctrine
     * 
     * @return Response Réponse HTTP contenant le template HTML rendu
     * 
     * @throws AccessDeniedException Si l'utilisateur n'est pas connecté
     */
    #[Route('/', name: 'app_dashboard')]
    public function index(
        OnboardingRepository $onboardingRepository,
        TechArticleRepository $techArticleRepository,
		HackerNewsClient $hnClient,
    	TechArticleCacheManager $cacheManager,
		OnboardingProgressCalculator $progressCalculator,
    	EntityManagerInterface $entityManager,
		UserRepository $userRepository
    ): Response {
        // 1. Récupération de l'utilisateur connecté via le composant de sécurité Symfony
        $user = $this->getUser();

        // Sécurité : interruption si aucun utilisateur n'est authentifié dans la session
        if (!$user) {
            throw $this->createAccessDeniedException('Utilisateur non authentifié.');
        }
		
        // 2. Synchronisation des articles HackerNews (Veille technologique)
		try {
            // Récupère les identifiants des 5 meilleures stories actuelles
			$ids = $hnClient->getTopStoriesIds(5);

			foreach ($ids as $id) {
                // Récupère les détails de chaque article depuis l'API externe
				$item = $hnClient->getItem($id);

				if ($item) {
                    // Sauvegarde l'article en base s'il n'existe pas déjà
					$cacheManager->saveIfNotExists($item);
				}
			}

            // Exécute les requêtes d'insertion en base de données
			$cacheManager->flush();
		} catch (\Throwable) {
			// Mode dégradé : en cas d'échec de l'API externe (timeout, hors-ligne...), on poursuit l'affichage sans bloquer l'utilisateur
		}

        // 3. Récupération des 5 articles les plus récents enregistrés localement
        $latestArticles = $techArticleRepository->findBy([], ['publishedAt' => 'DESC'], 5);

        // 4. Aiguillage selon les rôles de l'utilisateur
        // Si l'utilisateur possède un rôle d'encadrement ou d'administration (Manager, RH ou Admin)
        if ($this->isGranted('ROLE_MANAGER') || $this->isGranted('ROLE_HR') || $this->isGranted('ROLE_ADMIN')) {
            // Récupère les 10 parcours d'intégration les plus récents pour vue globale
            $onboardings = $onboardingRepository->findBy([], ['createdAt' => 'DESC'], 10);

			$collaborators = $userRepository->findBy(
				['role' => UserRole::COLLABORATOR],
				['lastName' => 'ASC']
			);

            // Rendu de la vue dédiée aux gestionnaires
            return $this->render('dashboard/manager.html.twig', [
                'user' => $user,
				'collaborators' => $collaborators,
                'onboardings' => $onboardings,
                'latestArticles' => $latestArticles,
            ]);
        }

        // Cas d'un collaborateur standard : récupération de son parcours d'intégration le plus récent avec ses tâches (items)
        $onboarding = $onboardingRepository->findLatestForUserWithItems($user);

        // Si un parcours existe, on recalcule dynamiquement le taux de progression et on met à jour la base
		if ($onboarding) {
			$progressCalculator->recalculate($onboarding);
			$entityManager->flush();
		}

        // Rendu de la vue personnalisée collaborateur
        return $this->render('dashboard/collaborator.html.twig', [
            'user' => $user,
            'onboarding' => $onboarding,
            'latestArticles' => $latestArticles,
        ]);
    }
}