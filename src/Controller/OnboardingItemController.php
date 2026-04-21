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

final class OnboardingItemController extends AbstractController
{
    #[Route('/onboarding/task/{id}/status', name: 'app_onboarding_task_update_status', methods: ['POST'])]
    public function updateTaskStatus(
        OnboardingTask $task,
        Request $request,
        EntityManagerInterface $entityManager,
        OnboardingProgressCalculator $progressCalculator
    ): RedirectResponse {
        $this->denyAccessUnlessGranted('ROLE_COLLABORATOR');

        $this->ensureOwnership($task->getOnboarding()->getUser()->getId());

        $status = (string) $request->request->get('status');

        $task->setStatus(match ($status) {
            'TODO' => TaskStatus::TODO,
            'IN_PROGRESS' => TaskStatus::IN_PROGRESS,
            'DONE' => TaskStatus::DONE,
            default => $task->getStatus(),
        });

        $task->setCompletedAt($task->getStatus() === TaskStatus::DONE ? new \DateTimeImmutable() : null);

        $progressCalculator->recalculate($task->getOnboarding());

        $entityManager->flush();

        return $this->redirectToRoute('app_dashboard');
    }

    #[Route('/onboarding/training/{id}/status', name: 'app_onboarding_training_update_status', methods: ['POST'])]
    public function updateTrainingStatus(
        OnboardingTraining $training,
        Request $request,
        EntityManagerInterface $entityManager,
        OnboardingProgressCalculator $progressCalculator
    ): RedirectResponse {
        $this->denyAccessUnlessGranted('ROLE_COLLABORATOR');

        $this->ensureOwnership($training->getOnboarding()->getUser()->getId());

        $status = (string) $request->request->get('status');

        $training->setStatus(match ($status) {
            'TODO' => TrainingStatus::TODO,
            'IN_PROGRESS' => TrainingStatus::IN_PROGRESS,
            'DONE' => TrainingStatus::DONE,
            default => $training->getStatus(),
        });

        $training->setCompletedAt($training->getStatus() === TrainingStatus::DONE ? new \DateTimeImmutable() : null);

        $progressCalculator->recalculate($training->getOnboarding());

        $entityManager->flush();

        return $this->redirectToRoute('app_dashboard');
    }

    #[Route('/onboarding/software/{id}/status', name: 'app_onboarding_software_update_status', methods: ['POST'])]
    public function updateSoftwareStatus(
        OnboardingSoftware $software,
        Request $request,
        EntityManagerInterface $entityManager,
        OnboardingProgressCalculator $progressCalculator
    ): RedirectResponse {
        $this->denyAccessUnlessGranted('ROLE_COLLABORATOR');

        $this->ensureOwnership($software->getOnboarding()->getUser()->getId());

        $status = (string) $request->request->get('status');

        $software->setStatus(match ($status) {
            'TODO' => SoftwareStatus::TODO,
            'DONE' => SoftwareStatus::DONE,
            default => $software->getStatus(),
        });

        $software->setCompletedAt($software->getStatus() === SoftwareStatus::DONE ? new \DateTimeImmutable() : null);

        $progressCalculator->recalculate($software->getOnboarding());

        $entityManager->flush();

        return $this->redirectToRoute('app_dashboard');
    }

    private function ensureOwnership(?int $ownerId): void
    {
        $user = $this->getUser();

        if (!$user || !method_exists($user, 'getId') || $user->getId() !== $ownerId) {
            throw $this->createAccessDeniedException('Vous ne pouvez modifier que vos propres données.');
        }
    }
}