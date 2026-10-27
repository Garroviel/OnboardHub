<?php

namespace App\Controller;

use App\Entity\OnboardingSoftware;
use App\Entity\OnboardingTask;
use App\Entity\OnboardingTraining;
use App\Enum\SoftwareStatus;
use App\Enum\TaskStatus;
use App\Enum\TrainingStatus;
use App\Service\OnboardingProgressCalculator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Contrôleur permettant aux collaborateurs de mettre à jour le statut des éléments de leur onboarding
 * (tâches, formations, logiciels) et déclenchant le recalcul de progression global.
 */
final class OnboardingItemController extends AbstractController
{
    /**
     * Met à jour le statut d'une tâche d'onboarding (`OnboardingTask`).
     *
     * Permissions requises : ROLE_COLLABORATOR + Propriétaire de l'onboarding parent.
     *
     * Étapes réalisées :
     * 1. Contrôle d'accès et vérification que la tâche appartient bien à l'utilisateur connecté.
     * 2. Lecture du nouveau statut passé en paramètre POST (TODO, IN_PROGRESS, DONE).
     * 3. Mise à jour de la date d'achèvement (`completedAt`) si le statut devient DONE (ou remise à null sinon).
     * 4. Recalcul automatique de la progression globale du parcours via `OnboardingProgressCalculator`.
     * 5. Enregistrement en base de données et redirection vers le tableau de bord.
     *
     * @param OnboardingTask $task Entité de la tâche résolue automatiquement
     * @param Request $request Requête HTTP entrante
     * @param EntityManagerInterface $entityManager Gestionnaire de persistance Doctrine
     * @param OnboardingProgressCalculator $progressCalculator Service métier de calcul de progression
     * 
     * @return RedirectResponse Redirection vers le tableau de bord collaborateur
     */
    #[Route('/onboarding/task/{id}/status', name: 'app_onboarding_task_update_status', methods: ['POST'])]
    public function updateTaskStatus(
        OnboardingTask $task,
        Request $request,
        EntityManagerInterface $entityManager,
        OnboardingProgressCalculator $progressCalculator
    ): RedirectResponse {
        // 1. Sécurité : seul le collaborateur concerné peut faire évoluer l'état de ses tâches
        $this->denyAccessUnlessGranted('ROLE_COLLABORATOR');
		$this->ensureOwnership($task->getOnboarding()->getUser()->getId());

        // 2. Extraction du statut soumis
        $status = (string) $request->request->get('status');

        // Mappage vers l'énumération TaskStatus
        $task->setStatus(match ($status) {
            'TODO' => TaskStatus::TODO,
            'IN_PROGRESS' => TaskStatus::IN_PROGRESS,
            'DONE' => TaskStatus::DONE,
            default => $task->getStatus(),
        });

        // 3. Gestion de l'horodatage de complétion
        $task->setCompletedAt($task->getStatus() === TaskStatus::DONE ? new \DateTimeImmutable() : null);

        // 4. Recalcul de l'avancement global de l'onboarding
        $progressCalculator->recalculate($task->getOnboarding());

        // 5. Sauvegarde en base
        $entityManager->flush();

        return $this->redirectToRoute('app_dashboard');
    }

    /**
     * Met à jour le statut d'une formation d'onboarding (`OnboardingTraining`).
     *
     * Permissions requises : ROLE_COLLABORATOR + Propriétaire de l'onboarding parent.
     *
     * @param OnboardingTraining $training Entité formation résolue automatiquement
     * @param Request $request Requête HTTP entrante
     * @param EntityManagerInterface $entityManager Gestionnaire de persistance Doctrine
     * @param OnboardingProgressCalculator $progressCalculator Service métier de calcul de progression
     * 
     * @return RedirectResponse Redirection vers le tableau de bord collaborateur
     */
    #[Route('/onboarding/training/{id}/status', name: 'app_onboarding_training_update_status', methods: ['POST'])]
    public function updateTrainingStatus(
        OnboardingTraining $training,
        Request $request,
        EntityManagerInterface $entityManager,
        OnboardingProgressCalculator $progressCalculator
    ): RedirectResponse {
        // 1. Sécurité
        $this->denyAccessUnlessGranted('ROLE_COLLABORATOR');
		$this->ensureOwnership($training->getOnboarding()->getUser()->getId());

        // 2. Extraction et assignation du statut
        $status = (string) $request->request->get('status');

        $training->setStatus(match ($status) {
            'TODO' => TrainingStatus::TODO,
            'IN_PROGRESS' => TrainingStatus::IN_PROGRESS,
            'DONE' => TrainingStatus::DONE,
            default => $training->getStatus(),
        });

        // 3. Horodatage si terminé
        $training->setCompletedAt($training->getStatus() === TrainingStatus::DONE ? new \DateTimeImmutable() : null);

        // 4. Recalcul de l'avancement global
        $progressCalculator->recalculate($training->getOnboarding());

        // 5. Sauvegarde
        $entityManager->flush();

        return $this->redirectToRoute('app_dashboard');
    }

    /**
     * Met à jour le statut d'un logiciel d'onboarding (`OnboardingSoftware`).
     *
     * Permissions requises : ROLE_COLLABORATOR + Propriétaire de l'onboarding parent.
     *
     * @param OnboardingSoftware $software Entité logiciel résolue automatiquement
     * @param Request $request Requête HTTP entrante
     * @param EntityManagerInterface $entityManager Gestionnaire de persistance Doctrine
     * @param OnboardingProgressCalculator $progressCalculator Service métier de calcul de progression
     * 
     * @return RedirectResponse Redirection vers le tableau de bord collaborateur
     */
    #[Route('/onboarding/software/{id}/status', name: 'app_onboarding_software_update_status', methods: ['POST'])]
    public function updateSoftwareStatus(
        OnboardingSoftware $software,
        Request $request,
        EntityManagerInterface $entityManager,
        OnboardingProgressCalculator $progressCalculator
    ): RedirectResponse {
        // 1. Sécurité
        $this->denyAccessUnlessGranted('ROLE_COLLABORATOR');
		$this->ensureOwnership($software->getOnboarding()->getUser()->getId());

        // 2. Extraction et assignation du statut
        $status = (string) $request->request->get('status');

        $software->setStatus(match ($status) {
            'TODO' => SoftwareStatus::TODO,
            'DONE' => SoftwareStatus::DONE,
            default => $software->getStatus(),
        });

        // 3. Horodatage si installé / validé
        $software->setCompletedAt($software->getStatus() === SoftwareStatus::DONE ? new \DateTimeImmutable() : null);

        // 4. Recalcul de l'avancement global
        $progressCalculator->recalculate($software->getOnboarding());

        // 5. Sauvegarde
        $entityManager->flush();

        return $this->redirectToRoute('app_dashboard');
    }

    /**
     * Vérifie que l'utilisateur connecté est bien le propriétaire de la ressource.
     *
     * @param int|null $ownerId Identifiant du propriétaire attendu
     * 
     * @throws AccessDeniedException Si l'utilisateur n'est pas le propriétaire
     */
	private function ensureOwnership(?int $ownerId): void
	{
		$user = $this->getUser();

		if (!$user || !method_exists($user, 'getId') || $user->getId() !== $ownerId) {
			throw $this->createAccessDeniedException('Vous ne pouvez modifier que vos propres données.');
		}
	}

}