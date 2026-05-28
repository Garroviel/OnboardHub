<?php

namespace App\DataFixtures;

use App\Entity\Company;
use App\Entity\Onboarding;
use App\Entity\OnboardingSoftware;
use App\Entity\OnboardingTask;
use App\Entity\OnboardingTraining;
use App\Entity\Team;
use App\Entity\TechArticle;
use App\Entity\User;
use App\Enum\OnboardingStatus;
use App\Enum\SoftwareStatus;
use App\Enum\TaskStatus;
use App\Enum\TrainingStatus;
use App\Enum\UserRole;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $company = (new Company())
            ->setName('Azium-Enterprise')
            ->setSlug('Azium-enterprise');

        $engineeringTeam = (new Team())
            ->setName('Engineering')
            ->setCompany($company);

        $hrTeam = (new Team())
            ->setName('HR')
            ->setCompany($company);

        $hr = (new User())
            ->setEmail('rh@onboardhub.local')
            ->setFirstName('Alice')
            ->setLastName('Martin')
            ->setRole(UserRole::HR)
            ->setProvider('mock_sso')
            ->setExternalId('hr-alice-martin')
            ->setCompany($company)
            ->setTeam($hrTeam)
            ->setHiredAt(new \DateTimeImmutable('2024-01-15'));

        $managerUser = (new User())
            ->setEmail('manager@onboardhub.local')
            ->setFirstName('Marc')
            ->setLastName('Dupont')
            ->setRole(UserRole::MANAGER)
            ->setProvider('mock_sso')
            ->setExternalId('manager-marc-dupont')
            ->setCompany($company)
            ->setTeam($engineeringTeam)
            ->setHiredAt(new \DateTimeImmutable('2023-09-01'));

        $collaborator = (new User())
            ->setEmail('collab@onboardhub.local')
            ->setFirstName('Lina')
            ->setLastName('Bernard')
            ->setRole(UserRole::COLLABORATOR)
            ->setProvider('mock_sso')
            ->setExternalId('collab-lina-bernard')
            ->setCompany($company)
            ->setTeam($engineeringTeam)
            ->setManager($managerUser)
            ->setHiredAt(new \DateTimeImmutable('2026-04-01'));

        $onboarding = (new Onboarding())
            ->setUser($collaborator)
            ->setStartDate(new \DateTimeImmutable('today'))
            ->setDueDate(new \DateTimeImmutable('+30 days'))
            ->setStatus(OnboardingStatus::ACTIVE)
            ->setProgressPercent(0);

        $task1 = (new OnboardingTask())
            ->setOnboarding($onboarding)
            ->setAssignedBy($managerUser)
            ->setTitle('Créer et configurer le compte mail')
            ->setDescription('Configurer la messagerie professionnelle et tester l’accès.')
            ->setStatus(TaskStatus::TODO)
            ->setPriority(1)
            ->setDueDate(new \DateTimeImmutable('+2 days'))
            ->setPosition(1);

        $task2 = (new OnboardingTask())
            ->setOnboarding($onboarding)
            ->setAssignedBy($managerUser)
            ->setTitle('Lire le guide d’équipe')
            ->setDescription('Lire la documentation d’équipe et les règles de fonctionnement.')
            ->setStatus(TaskStatus::IN_PROGRESS)
            ->setPriority(2)
            ->setDueDate(new \DateTimeImmutable('+5 days'))
            ->setPosition(2);

        $training1 = (new OnboardingTraining())
            ->setOnboarding($onboarding)
            ->setTitle('Formation sécurité interne')
            ->setUrl('https://example.com/training/security')
            ->setStatus(TrainingStatus::TODO)
            ->setPosition(1);

        $training2 = (new OnboardingTraining())
            ->setOnboarding($onboarding)
            ->setTitle('Formation produit OnboardHub')
            ->setUrl('https://example.com/training/onboardhub')
            ->setStatus(TrainingStatus::TODO)
            ->setPosition(2);

        $software1 = (new OnboardingSoftware())
            ->setOnboarding($onboarding)
            ->setName('VSCode')
            ->setInstallUrl('https://code.visualstudio.com/')
            ->setStatus(SoftwareStatus::TODO)
            ->setPosition(1);

        $software2 = (new OnboardingSoftware())
            ->setOnboarding($onboarding)
            ->setName('Docker Desktop')
            ->setInstallUrl('https://www.docker.com/products/docker-desktop/')
            ->setStatus(SoftwareStatus::TODO)
            ->setPosition(2);

        $article1 = (new TechArticle())
            ->setSource('HN')
            ->setExternalId('100001')
            ->setTitle('Symfony 7 Best Practices for Scalable Apps')
            ->setUrl('https://example.com/articles/symfony-7-best-practices')
            ->setAuthor('tech_writer')
            ->setScore(120)
            ->setPublishedAt(new \DateTimeImmutable('-1 day'));

        $article2 = (new TechArticle())
            ->setSource('HN')
            ->setExternalId('100002')
            ->setTitle('Doctrine ORM Performance Tips')
            ->setUrl('https://example.com/articles/doctrine-performance')
            ->setAuthor('db_expert')
            ->setScore(85)
            ->setPublishedAt(new \DateTimeImmutable('-2 days'));

        $manager->persist($company);
        $manager->persist($engineeringTeam);
        $manager->persist($hrTeam);

        $manager->persist($hr);
        $manager->persist($managerUser);
        $manager->persist($collaborator);

        $manager->persist($onboarding);

        $manager->persist($task1);
        $manager->persist($task2);

        $manager->persist($training1);
        $manager->persist($training2);

        $manager->persist($software1);
        $manager->persist($software2);

        $manager->persist($article1);
        $manager->persist($article2);

        $manager->flush();
    }
}