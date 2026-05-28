<?php

namespace App\Form;

use App\Entity\OnboardingTraining;
use App\Enum\TrainingStatus;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OnboardingTrainingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('url')
            ->add('status', EnumType::class, [
                'class' => TrainingStatus::class,
            ])
            ->add('position', IntegerType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => OnboardingTraining::class,
        ]);
    }
}