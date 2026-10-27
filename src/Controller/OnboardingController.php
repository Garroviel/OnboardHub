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

/**
 * Contrôleur gérant les parcours d'onboarding et l'ajout d'éléments associés (tâches, formations, logiciels).
 */
#[Route('/onboarding')]
final class OnboardingController extends AbstractController
{
    /**
     * Crée un nouveau parcours d'onboarding.
     *
     * Permissions requises : ROLE_MANAGER, ROLE_HR ou ROLE_ADMIN.
     *
     * Étapes réalisées :
     * 1. Contrôle des autorisations d'accès.
     * 2. Initialisation de l'entité `Onboarding` avec des valeurs par défaut (date de début au jour même, échéance à J+30, statut ACTIF).
     * 3. Traitement du formulaire `OnboardingType`.
     * 4. Enregistrement en base de données et redirection vers la vue détaillée du parcours créé.
     *
     * @param Request $request Requête HTTP entrante
     * @param EntityManagerInterface $entityManager Gestionnaire d'entités Doctrine
     * 
     * @return Response Rendu du formulaire ou redirection vers la page de détail
     */
    #[Route('/new', name: 'app_onboarding_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        // 1. Restriction d'accès aux gestionnaires
        $this->denyAccessUnlessGrantedAny(['ROLE_MANAGER', 'ROLE_HR', 'ROLE_ADMIN']);

        // 2. Initialisation du parcours avec des dates et statut par défaut
        $onboarding = new Onboarding();
        $onboarding->setStartDate(new \DateTimeImmutable('today'));
        $onboarding->setDueDate(new \DateTimeImmutable('+30 days'));
        $onboarding->setStatus(OnboardingStatus::ACTIVE);

        // 3. Gestion du formulaire
        $form = $this->createForm(OnboardingType::class, $onboarding);
        $form->handleRequest($request);

        // 4. Si soumis et valide, persistance en base
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

    /**
     * Affiche les détails d'un parcours d'onboarding spécifique (tâches, formations, logiciels, commentaires).
     *
     * Permissions requises :
     * - Managers, RH, Admins ont accès à tous les onboardings.
     * - Un collaborateur n'a accès qu'à son propre parcours d'onboarding.
     *
     * @param Onboarding $onboarding Entité résolue automatiquement via l'identifiant dans l'URL (ParamConverter)
     * 
     * @return Response Rendu de la vue détaillée de l'onboarding
     * 
     * @throws AccessDeniedException Si l'utilisateur n'est pas autorisé à consulter cet onboarding
     */
    #[Route('/{id}', name: 'app_onboarding_show', methods: ['GET'])]
	public function show(Onboarding $onboarding): Response
	{
        // Vérification de sécurité : l'utilisateur doit avoir un rôle d'encadrement OU être le propriétaire du parcours
		if (
			!$this->isGranted('ROLE_MANAGER')
			&& !$this->isGranted('ROLE_HR')
			&& !$this->isGranted('ROLE_ADMIN')
			&& $onboarding->getUser()?->getId() !== $this->getUser()?->getId()
		) {
			throw $this->createAccessDeniedException('Accès refusé.');
		}

		return $this->render('onboarding/show.html.twig', [
			'onboarding' => $onboarding,
		]);
	}

    /**
     * Ajoute une nouvelle tâche (`OnboardingTask`) à un parcours d'onboarding existant.
     *
     * Permissions requises : ROLE_MANAGER, ROLE_HR ou ROLE_ADMIN.
     *
     * @param Onboarding $onboarding Parcours d'onboarding parent
     * @param Request $request Requête HTTP entrante
     * @param EntityManagerInterface $entityManager Gestionnaire de persistance Doctrine
     * 
     * @return Response Rendu du formulaire ou redirection vers l'onboarding
     */
    #[Route('/{id}/task/new', name: 'app_onboarding_task_new', methods: ['GET', 'POST'])]
    public function newTask(Onboarding $onboarding, Request $request, EntityManagerInterface $entityManager): Response
    {
        // 1. Restriction d'accès
        $this->denyAccessUnlessGrantedAny(['ROLE_MANAGER', 'ROLE_HR', 'ROLE_ADMIN']);

        // 2. Instanciation de la tâche et liaison avec le parcours parent
        $task = new OnboardingTask();
        $task->setOnboarding($onboarding);

        // 3. Traitement du formulaire
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

    /**
     * Ajoute une nouvelle formation (`OnboardingTraining`) à un parcours d'onboarding existant.
     *
     * Permissions requises : ROLE_MANAGER, ROLE_HR ou ROLE_ADMIN.
     *
     * @param Onboarding $onboarding Parcours d'onboarding parent
     * @param Request $request Requête HTTP entrante
     * @param EntityManagerInterface $entityManager Gestionnaire de persistance Doctrine
     * 
     * @return Response Rendu du formulaire ou redirection vers l'onboarding
     */
    #[Route('/{id}/training/new', name: 'app_onboarding_training_new', methods: ['GET', 'POST'])]
    public function newTraining(Onboarding $onboarding, Request $request, EntityManagerInterface $entityManager): Response
    {
        // 1. Restriction d'accès
        $this->denyAccessUnlessGrantedAny(['ROLE_MANAGER', 'ROLE_HR', 'ROLE_ADMIN']);

        // 2. Instanciation de la formation et liaison avec le parcours parent
        $training = new OnboardingTraining();
        $training->setOnboarding($onboarding);

        // 3. Traitement du formulaire
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

    /**
     * Ajoute un nouveau logiciel à installer/configurer (`OnboardingSoftware`) à un parcours d'onboarding.
     *
     * Permissions requises : ROLE_MANAGER, ROLE_HR ou ROLE_ADMIN.
     *
     * @param Onboarding $onboarding Parcours d'onboarding parent
     * @param Request $request Requête HTTP entrante
     * @param EntityManagerInterface $entityManager Gestionnaire de persistance Doctrine
     * 
     * @return Response Rendu du formulaire ou redirection vers l'onboarding
     */
    #[Route('/{id}/software/new', name: 'app_onboarding_software_new', methods: ['GET', 'POST'])]
    public function newSoftware(Onboarding $onboarding, Request $request, EntityManagerInterface $entityManager): Response
    {
        // 1. Restriction d'accès
        $this->denyAccessUnlessGrantedAny(['ROLE_MANAGER', 'ROLE_HR', 'ROLE_ADMIN']);

        // 2. Instanciation du logiciel et liaison avec le parcours parent
        $software = new OnboardingSoftware();
        $software->setOnboarding($onboarding);

        // 3. Traitement du formulaire
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

    /**
     * Méthode utilitaire interne pour vérifier si l'utilisateur possède au moins l'un des rôles spécifiés.
     *
     * @param array<string> $roles Liste des rôles autorisés
     * 
     * @throws AccessDeniedException Si aucun rôle correspondant n'est détenu
     */
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