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

final class OnboardingCommentController extends AbstractController
{
    #[Route('/onboarding/task/{id}/comment', name: 'app_onboarding_task_comment', methods: ['POST'])]
    public function commentTask(
        OnboardingTask $task,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $this->ensureCanComment($task->getOnboarding()->getUser()->getId());

        $comment = new OnboardingComment();
        $comment->setTask($task);

        return $this->handleCommentForm($comment, $request, $entityManager, $task->getOnboarding()->getId());
    }

    #[Route('/onboarding/training/{id}/comment', name: 'app_onboarding_training_comment', methods: ['POST'])]
    public function commentTraining(
        OnboardingTraining $training,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $this->ensureCanComment($training->getOnboarding()->getUser()->getId());

        $comment = new OnboardingComment();
        $comment->setTraining($training);

        return $this->handleCommentForm($comment, $request, $entityManager, $training->getOnboarding()->getId());
    }

    #[Route('/onboarding/software/{id}/comment', name: 'app_onboarding_software_comment', methods: ['POST'])]
    public function commentSoftware(
        OnboardingSoftware $software,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $this->ensureCanComment($software->getOnboarding()->getUser()->getId());

        $comment = new OnboardingComment();
        $comment->setSoftware($software);

        return $this->handleCommentForm($comment, $request, $entityManager, $software->getOnboarding()->getId());
    }

    private function handleCommentForm(
		OnboardingComment $comment,
		Request $request,
		EntityManagerInterface $entityManager,
		int $onboardingId
	): Response {
		$user = $this->getUser();

		if (!$user instanceof User) {
			throw $this->createAccessDeniedException('Utilisateur non authentifié.');
		}

		$form = $this->createForm(OnboardingCommentType::class, $comment);
		$form->handleRequest($request);

		if (!$form->isSubmitted() || !$form->isValid()) {
			$this->addFlash('error', 'Le commentaire est invalide.');
		} else {
			$message = trim((string) $comment->getMessage());

			if ($message === '') {
				$this->addFlash('error', 'Le commentaire ne peut pas être vide.');
			} else {
				$comment->setMessage($message);
				$comment->setAuthor($user);

				$entityManager->persist($comment);
				$entityManager->flush();

				$this->addFlash('success', 'Commentaire ajouté.');
			}
		}

		if ($this->isGranted('ROLE_MANAGER') || $this->isGranted('ROLE_HR') || $this->isGranted('ROLE_ADMIN')) {
			return $this->redirectToRoute('app_onboarding_show', ['id' => $onboardingId]);
		}

		return $this->redirectToRoute('app_dashboard');
	}

    private function ensureCanComment(?int $ownerId): void
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('Utilisateur non authentifié.');
        }

        if (
            $this->isGranted('ROLE_MANAGER')
            || $this->isGranted('ROLE_HR')
            || $this->isGranted('ROLE_ADMIN')
        ) {
            return;
        }

        if ($user->getId() !== $ownerId) {
            throw $this->createAccessDeniedException('Vous ne pouvez commenter que vos propres éléments.');
        }
    }
}