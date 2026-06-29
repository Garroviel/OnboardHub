<?php

namespace App\Form;

use App\Entity\OnboardingComment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OnboardingCommentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('message', TextareaType::class, [
            'label' => false,
            'required' => true,
            'attr' => [
                'rows' => 2,
                'maxlength' => 2000,
                'placeholder' => 'Ajouter un commentaire...',
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => OnboardingComment::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'onboarding_comment',
        ]);
    }
}