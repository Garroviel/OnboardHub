<?php

namespace App\Controller;

use App\Entity\Onboarding;
use App\Entity\OnboardingSoftware;
use App\Entity\OnboardingTask;
use App\Entity\OnboardingTraining;
use App\Enum\OnboardingStatus;
use App\Form\OnboardingSoftwareType;
use App\Form\OnboardingTaskType;
use App\Form\OnboardingTrainingType;
use App\Form\OnboardingType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/onboarding')]
final class OnboardingController extends AbstractController
{
    #[Route('/new', name: 'app_onboarding_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGrantedAny(['ROLE_MANAGER', 'ROLE_HR', 'ROLE_ADMIN']);

        $onboarding = new Onboarding();
        $onboarding->setStartDate(new \DateTimeImmutable('today'));
        $onboarding->setDueDate(new \DateTimeImmutable('+30 days'));
        $onboarding->setStatus(OnboardingStatus::ACTIVE);

        $form = $this->createForm(OnboardingType::class, $onboarding);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($onboarding);
            $entityManager->flush();

            $this->addFlash('success', 'Onboarding créé avec succès.');

            return $this->redirectToRoute('app_onboarding_show', ['id' => $onboarding->getId()]);
        }

        return $this->render('onboarding/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_onboarding_show', methods: ['GET'])]
    public function show(Onboarding $onboarding): Response
    {
        $this->denyAccessUnlessGrantedAny(['ROLE_MANAGER', 'ROLE_HR', 'ROLE_ADMIN']);

        return $this->render('onboarding/show.html.twig', [
            'onboarding' => $onboarding,
        ]);
    }

    #[Route('/{id}/task/new', name: 'app_onboarding_task_new', methods: ['GET', 'POST'])]
    public function newTask(Onboarding $onboarding, Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGrantedAny(['ROLE_MANAGER', 'ROLE_HR', 'ROLE_ADMIN']);

        $task = new OnboardingTask();
        $task->setOnboarding($onboarding);

        $form = $this->createForm(OnboardingTaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($task);
            $entityManager->flush();

            $this->addFlash('success', 'Tâche ajoutée avec succès.');

            return $this->redirectToRoute('app_onboarding_show', ['id' => $onboarding->getId()]);
        }

        return $this->render('onboarding/task_new.html.twig', [
            'form' => $form->createView(),
            'onboarding' => $onboarding,
        ]);
    }

    #[Route('/{id}/training/new', name: 'app_onboarding_training_new', methods: ['GET', 'POST'])]
    public function newTraining(Onboarding $onboarding, Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGrantedAny(['ROLE_MANAGER', 'ROLE_HR', 'ROLE_ADMIN']);

        $training = new OnboardingTraining();
        $training->setOnboarding($onboarding);

        $form = $this->createForm(OnboardingTrainingType::class, $training);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($training);
            $entityManager->flush();

            $this->addFlash('success', 'Formation ajoutée avec succès.');

            return $this->redirectToRoute('app_onboarding_show', ['id' => $onboarding->getId()]);
        }

        return $this->render('onboarding/training_new.html.twig', [
            'form' => $form->createView(),
            'onboarding' => $onboarding,
        ]);
    }

    #[Route('/{id}/software/new', name: 'app_onboarding_software_new', methods: ['GET', 'POST'])]
    public function newSoftware(Onboarding $onboarding, Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGrantedAny(['ROLE_MANAGER', 'ROLE_HR', 'ROLE_ADMIN']);

        $software = new OnboardingSoftware();
        $software->setOnboarding($onboarding);

        $form = $this->createForm(OnboardingSoftwareType::class, $software);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($software);
            $entityManager->flush();

            $this->addFlash('success', 'Logiciel ajouté avec succès.');

            return $this->redirectToRoute('app_onboarding_show', ['id' => $onboarding->getId()]);
        }

        return $this->render('onboarding/software_new.html.twig', [
            'form' => $form->createView(),
            'onboarding' => $onboarding,
        ]);
    }

    private function denyAccessUnlessGrantedAny(array $roles): void
    {
        foreach ($roles as $role) {
            if ($this->isGranted($role)) {
                return;
            }
        }

        throw $this->createAccessDeniedException('Accès refusé.');
    }
}