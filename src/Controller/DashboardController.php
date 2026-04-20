<?php

namespace App\Controller;

use App\Entity\Onboarding;
use App\Repository\OnboardingRepository;
use App\Repository\TechArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DashboardController extends AbstractController
{
    #[Route('/', name: 'app_dashboard')]
    public function index(
        OnboardingRepository $onboardingRepository,
        TechArticleRepository $techArticleRepository
    ): Response {
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('Utilisateur non authentifié.');
        }

        $latestArticles = $techArticleRepository->findBy([], ['publishedAt' => 'DESC'], 5);

        if ($this->isGranted('ROLE_MANAGER') || $this->isGranted('ROLE_HR') || $this->isGranted('ROLE_ADMIN')) {
            $onboardings = $onboardingRepository->findBy([], ['createdAt' => 'DESC'], 10);

            return $this->render('dashboard/manager.html.twig', [
                'user' => $user,
                'onboardings' => $onboardings,
                'latestArticles' => $latestArticles,
            ]);
        }

        $onboarding = $onboardingRepository->findLatestForUserWithItems($user);

        return $this->render('dashboard/collaborator.html.twig', [
            'user' => $user,
            'onboarding' => $onboarding,
            'latestArticles' => $latestArticles,
        ]);
    }
}