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

/**
 * Contrôleur dédié à la gestion des collaborateurs (création, rattachement à une entreprise, etc.).
 */
#[Route('/collaborators')]
final class CollaboratorController extends AbstractController
{
    /**
     * Crée un nouveau collaborateur au sein de la même entreprise que l'utilisateur connecté.
     *
     * Permissions requises : ROLE_MANAGER, ROLE_HR ou ROLE_ADMIN.
     *
     * Étapes réalisées :
     * 1. Contrôle d'accès basé sur les rôles autorisés.
     * 2. Vérification de la présence de l'utilisateur connecté et de son entreprise de rattachement.
     * 3. Instanciation et pré-configuration d'un nouveau `User` (rôle COLLABORATOR, provider Keycloak avec externalId temporaire).
     * 4. Affectation automatique du manager si l'utilisateur créateur a le rôle ROLE_MANAGER.
     * 5. Traitement du formulaire de création :
     *    - Vérification de l'unicité de l'email au sein de l'entreprise.
     *    - Persistance et enregistrement en base de données.
     *    - Redirection vers le tableau de bord avec message flash de confirmation.
     * 6. Rendu de la vue du formulaire.
     *
     * @param Request $request Requête HTTP entrante
     * @param EntityManagerInterface $entityManager Gestionnaire de persistance Doctrine
     * @param UserRepository $userRepository Répertoire des utilisateurs pour vérification d'unicité
     * 
     * @return Response Réponse HTTP (rendu de template ou redirection)
     * 
     * @throws AccessDeniedException Si l'accès est refusé ou si les préconditions ne sont pas remplies
     */
    #[Route('/new', name: 'app_collaborator_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository
    ): Response {
        // 1. Restriction d'accès aux rôles de gestion
        $this->denyAccessUnlessGrantedAny(['ROLE_MANAGER', 'ROLE_HR', 'ROLE_ADMIN']);

        // 2. Récupération et validation de l'utilisateur courant
        $currentUser = $this->getUser();

        if (!$currentUser instanceof User) {
            throw $this->createAccessDeniedException('Utilisateur non authentifié.');
        }

        // Vérifie que l'utilisateur connecté est bien rattaché à une entreprise
        if (!$currentUser->getCompany()) {
            throw $this->createAccessDeniedException('Votre compte n’est associé à aucune entreprise.');
        }

        // 3. Initialisation du nouveau collaborateur avec les valeurs par défaut
        $collaborator = new User();
        $collaborator
            ->setCompany($currentUser->getCompany())
            ->setRole(UserRole::COLLABORATOR)
            ->setProvider('keycloak')
            ->setExternalId('pending-' . bin2hex(random_bytes(16))); // Identifiant temporaire en attente de la première connexion SSO

        // 4. Si le créateur est un manager, il devient automatiquement le manager direct du collaborateur
        if ($this->isGranted('ROLE_MANAGER')) {
            $collaborator->setManager($currentUser);
        }

        // 5. Création et liaison du formulaire avec la requête HTTP
        $form = $this->createForm(CollaboratorType::class, $collaborator);
        $form->handleRequest($request);

        // Si le formulaire a été soumis et que les contraintes de validation sont respectées
        if ($form->isSubmitted() && $form->isValid()) {
            // Contrôle d'unicité : vérifie si un utilisateur avec le même email existe déjà dans cette entreprise
            $existingUser = $userRepository->findOneBy([
                'company' => $currentUser->getCompany(),
                'email' => $collaborator->getEmail(),
            ]);

            if ($existingUser) {
                // Ajout d'une erreur sur le champ email en cas de doublon
                $form->get('email')->addError(new FormError('Un utilisateur existe déjà avec cet email dans cette entreprise.'));
            } else {
                // Enregistrement de l'entité en base de données
                $entityManager->persist($collaborator);
                $entityManager->flush();

                // Message flash de succès
                $this->addFlash('success', 'Collaborateur créé avec succès dans OnboardHub.');

                // Redirection vers le tableau de bord
                return $this->redirectToRoute('app_dashboard');
            }
        }

        // 6. Rendu du template Twig contenant le formulaire
        return $this->render('collaborator/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Méthode utilitaire interne pour vérifier si l'utilisateur possède au moins l'un des rôles spécifiés.
     *
     * @param array<string> $roles Liste des rôles autorisés (ex: ['ROLE_MANAGER', 'ROLE_HR'])
     * 
     * @throws AccessDeniedException Si l'utilisateur ne possède aucun des rôles fournis
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