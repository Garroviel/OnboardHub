<?php

namespace App\Controller;

use App\Entity\OnboardingComment;
use App\Entity\OnboardingSoftware;
use App\Entity\OnboardingTask;
use App\Entity\OnboardingTraining;
use App\Entity\User;
use App\Form\OnboardingCommentType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Contrôleur permettant d'ajouter des commentaires de suivi sur les différents éléments d'un onboarding
 * (tâche, formation ou logiciel).
 */
final class OnboardingCommentController extends AbstractController
{
    /**
     * Ajoute un commentaire sur une tâche d'onboarding (`OnboardingTask`).
     *
     * Permissions requises : Rôles d'encadrement (Manager, RH, Admin) OU Collaborateur propriétaire du parcours.
     *
     * @param OnboardingTask $task Entité tâche concernée
     * @param Request $request Requête HTTP entrante
     * @param EntityManagerInterface $entityManager Gestionnaire de persistance Doctrine
     * 
     * @return Response Redirection vers la vue appropriée
     */
    #[Route('/onboarding/task/{id}/comment', name: 'app_onboarding_task_comment', methods: ['POST'])]
    public function commentTask(
        OnboardingTask $task,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        // 1. Vérification des droits de commentaire
        $this->ensureCanComment($task->getOnboarding()->getUser()->getId());

        // 2. Instanciation du commentaire et rattachement à la tâche
        $comment = new OnboardingComment();
        $comment->setTask($task);

        // 3. Traitement mutualisé du formulaire
        return $this->handleCommentForm($comment, $request, $entityManager, $task->getOnboarding()->getId());
    }

    /**
     * Ajoute un commentaire sur une formation d'onboarding (`OnboardingTraining`).
     *
     * Permissions requises : Rôles d'encadrement (Manager, RH, Admin) OU Collaborateur propriétaire du parcours.
     *
     * @param OnboardingTraining $training Entité formation concernée
     * @param Request $request Requête HTTP entrante
     * @param EntityManagerInterface $entityManager Gestionnaire de persistance Doctrine
     * 
     * @return Response Redirection vers la vue appropriée
     */
    #[Route('/onboarding/training/{id}/comment', name: 'app_onboarding_training_comment', methods: ['POST'])]
    public function commentTraining(
        OnboardingTraining $training,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        // 1. Vérification des droits de commentaire
        $this->ensureCanComment($training->getOnboarding()->getUser()->getId());

        // 2. Instanciation du commentaire et rattachement à la formation
        $comment = new OnboardingComment();
        $comment->setTraining($training);

        // 3. Traitement mutualisé du formulaire
        return $this->handleCommentForm($comment, $request, $entityManager, $training->getOnboarding()->getId());
    }

    /**
     * Ajoute un commentaire sur un logiciel d'onboarding (`OnboardingSoftware`).
     *
     * Permissions requises : Rôles d'encadrement (Manager, RH, Admin) OU Collaborateur propriétaire du parcours.
     *
     * @param OnboardingSoftware $software Entité logiciel concernée
     * @param Request $request Requête HTTP entrante
     * @param EntityManagerInterface $entityManager Gestionnaire de persistance Doctrine
     * 
     * @return Response Redirection vers la vue appropriée
     */
    #[Route('/onboarding/software/{id}/comment', name: 'app_onboarding_software_comment', methods: ['POST'])]
    public function commentSoftware(
        OnboardingSoftware $software,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        // 1. Vérification des droits de commentaire
        $this->ensureCanComment($software->getOnboarding()->getUser()->getId());

        // 2. Instanciation du commentaire et rattachement au logiciel
        $comment = new OnboardingComment();
        $comment->setSoftware($software);

        // 3. Traitement mutualisé du formulaire
        return $this->handleCommentForm($comment, $request, $entityManager, $software->getOnboarding()->getId());
    }

    /**
     * Méthode générique de traitement et de validation du formulaire de commentaire.
     *
     * Étapes réalisées :
     * 1. Vérification de l'utilisateur connecté.
     * 2. Traitement du formulaire `OnboardingCommentType`.
     * 3. Nettoyage du texte et vérification qu'il n'est pas vide.
     * 4. Enregistrement en base de données avec assignation de l'auteur.
     * 5. Redirection ciblée (vers la page show pour les managers/RH/admins ou vers le dashboard pour les collaborateurs).
     *
     * @param OnboardingComment $comment Entité commentaire pré-associée à l'élément cible
     * @param Request $request Requête HTTP entrante
     * @param EntityManagerInterface $entityManager Gestionnaire de persistance Doctrine
     * @param int $onboardingId Identifiant du parcours d'onboarding parent pour la redirection
     * 
     * @return Response Redirection HTTP
     */
    private function handleCommentForm(
		OnboardingComment $comment,
		Request $request,
		EntityManagerInterface $entityManager,
		int $onboardingId
	): Response {
        // 1. Contrôle d'authentification
		$user = $this->getUser();

		if (!$user instanceof User) {
			throw $this->createAccessDeniedException('Utilisateur non authentifié.');
		}

        // 2. Création et soumission du formulaire
		$form = $this->createForm(OnboardingCommentType::class, $comment);
		$form->handleRequest($request);

		if (!$form->isSubmitted() || !$form->isValid()) {
			$this->addFlash('error', 'Le commentaire est invalide.');
		} else {
            // 3. Nettoyage et vérification de la non-vacuité du message
			$message = trim((string) $comment->getMessage());

			if ($message === '') {
				$this->addFlash('error', 'Le commentaire ne peut pas être vide.');
			} else {
                // 4. Définition du texte nettoyé et de l'auteur, puis persistance
				$comment->setMessage($message);
				$comment->setAuthor($user);

				$entityManager->persist($comment);
				$entityManager->flush();

				$this->addFlash('success', 'Commentaire ajouté.');
			}
		}

        // 5. Redirection intelligente selon le profil de l'utilisateur
		if ($this->isGranted('ROLE_MANAGER') || $this->isGranted('ROLE_HR') || $this->isGranted('ROLE_ADMIN')) {
			return $this->redirectToRoute('app_onboarding_show', ['id' => $onboardingId]);
		}

		return $this->redirectToRoute('app_dashboard');
	}

    /**
     * Contrôle d'autorisation spécifique pour les commentaires :
     * Autorise les Managers, RH, Admins ou le propriétaire direct de l'onboarding.
     *
     * @param int|null $ownerId Identifiant du collaborateur propriétaire
     * 
     * @throws AccessDeniedException Si l'accès est interdit
     */
    private function ensureCanComment(?int $ownerId): void
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('Utilisateur non authentifié.');
        }

        // Les encadrants (Manager, RH, Admin) ont le droit de commenter n'importe quel élément
        if (
            $this->isGranted('ROLE_MANAGER')
            || $this->isGranted('ROLE_HR')
            || $this->isGranted('ROLE_ADMIN')
        ) {
            return;
        }

        // Un collaborateur ne peut commenter que ses propres tâches/formations/logiciels
        if ($user->getId() !== $ownerId) {
            throw $this->createAccessDeniedException('Vous ne pouvez commenter que vos propres éléments.');
        }
    }
}