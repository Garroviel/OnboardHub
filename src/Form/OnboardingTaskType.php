<?php

namespace App\Form;

use App\Entity\OnboardingTask;
use App\Entity\User;
use App\Enum\TaskStatus;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OnboardingTaskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('description', TextareaType::class, [
                'required' => false,
            ])
            ->add('status', EnumType::class, [
                'class' => TaskStatus::class,
            ])
            ->add('priority', IntegerType::class)
            ->add('dueDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('position', IntegerType::class)
            ->add('assignedBy', EntityType::class, [
                'class' => User::class,
                'choice_label' => fn(User $user) => $user->getFullName() . ' <' . $user->getEmail() . '>',
                'required' => false,
                'placeholder' => 'Aucun assignateur',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => OnboardingTask::class,
        ]);
    }
}