<?php

namespace App\Controller;

use App\Entity\User;
use App\Enum\UserRole;
use App\Form\CollaboratorType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/collaborators')]
final class CollaboratorController extends AbstractController
{
    #[Route('/new', name: 'app_collaborator_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository
    ): Response {
        $this->denyAccessUnlessGrantedAny(['ROLE_MANAGER', 'ROLE_HR', 'ROLE_ADMIN']);

        $currentUser = $this->getUser();

        if (!$currentUser instanceof User) {
            throw $this->createAccessDeniedException('Utilisateur non authentifié.');
        }

        if (!$currentUser->getCompany()) {
            throw $this->createAccessDeniedException('Votre compte n’est associé à aucune entreprise.');
        }

        $collaborator = new User();
        $collaborator
            ->setCompany($currentUser->getCompany())
            ->setRole(UserRole::COLLABORATOR)
            ->setProvider('keycloak')
            ->setExternalId('pending-' . bin2hex(random_bytes(16)));

        if ($this->isGranted('ROLE_MANAGER')) {
            $collaborator->setManager($currentUser);
        }

        $form = $this->createForm(CollaboratorType::class, $collaborator);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $existingUser = $userRepository->findOneBy([
                'company' => $currentUser->getCompany(),
                'email' => $collaborator->getEmail(),
            ]);

            if ($existingUser) {
                $form->get('email')->addError(new FormError('Un utilisateur existe déjà avec cet email dans cette entreprise.'));
            } else {
                $entityManager->persist($collaborator);
                $entityManager->flush();

                $this->addFlash('success', 'Collaborateur créé avec succès dans OnboardHub.');

                return $this->redirectToRoute('app_dashboard');
            }
        }

        return $this->render('collaborator/new.html.twig', [
            'form' => $form->createView(),
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