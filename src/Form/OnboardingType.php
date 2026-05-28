<?php

namespace App\Form;

use App\Entity\Onboarding;
use App\Entity\User;
use App\Enum\OnboardingStatus;
use App\Enum\UserRole;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OnboardingType extends AbstractType
{
    public function __construct(
        private UserRepository $userRepository
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $collaborators = $this->userRepository->findBy(
            ['role' => UserRole::COLLABORATOR],
            ['lastName' => 'ASC', 'firstName' => 'ASC']
        );

        $builder
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choices' => $collaborators,
                'choice_label' => fn(User $user) => $user->getFullName() . ' <' . $user->getEmail() . '>',
                'label' => 'Collaborateur',
                'placeholder' => 'Sélectionner un collaborateur',
            ])
            ->add('startDate', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date de début',
            ])
            ->add('dueDate', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date cible',
                'required' => false,
            ])
            ->add('status', EnumType::class, [
                'class' => OnboardingStatus::class,
                'label' => 'Statut',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Onboarding::class,
        ]);
    }
}