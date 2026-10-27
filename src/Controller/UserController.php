<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserRoleType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Enum\UserRole;
use App\Repository\UserRepository;

class UserController extends AbstractController
{
    #[Route('/une-route', name: 'une_route')]
    public function existingMethod(): Response
    {
        // ...
        
        return $this->render('...');
    } // <-- cette accolade doit bien être présente

	#[Route('/users/roles', name: 'app_user_role_index')]
	public function roleIndex(
		UserRepository $userRepository
	): Response {
		$this->denyAccessUnlessGranted('ROLE_HR');

		$users = $userRepository->findBy(
			[
				'role' => [
					UserRole::COLLABORATOR,
					UserRole::MANAGER,
				],
			],
			[
				'lastName' => 'ASC',
			]
		);

		return $this->render('user/role_index.html.twig', [
			'users' => $users,
		]);
	}


    #[Route('/users/{id}/role', name: 'app_user_role_edit')]
	public function editRole(
		int $id,
		UserRepository $userRepository,
		Request $request,
		EntityManagerInterface $entityManager
	): Response {
		if (
			!$this->isGranted('ROLE_HR')
			&& !$this->isGranted('ROLE_ADMIN')
			&& !$this->isGranted('ROLE_MANAGER')
		) {
			throw $this->createAccessDeniedException(
				'Vous ne pouvez pas modifier les rôles.'
			);
		}

		$targetUser = $userRepository->find($id);

		if (!$targetUser) {
			throw $this->createNotFoundException(
				'Utilisateur introuvable.'
			);
		}

		$form = $this->createForm(
			UserRoleType::class,
			$targetUser
		);

		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$entityManager->flush();

			$this->addFlash(
				'success',
				'Le rôle de l’utilisateur a été modifié.'
			);

			return $this->redirectToRoute('app_user_role_index');
		}

		return $this->render('user/edit_role.html.twig', [
			'user' => $targetUser,
			'form' => $form,
		]);
	}
}