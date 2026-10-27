<?php

namespace App\Form;

use App\Entity\User;
use App\Enum\UserRole;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserRoleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('role', EnumType::class, [
            'class' => UserRole::class,
            'choices' => [
                UserRole::COLLABORATOR,
                UserRole::MANAGER,
            ],
            'choice_label' => static fn (UserRole $role) => match ($role) {
                UserRole::COLLABORATOR => 'Collaborateur',
                UserRole::MANAGER => 'Manager',
                default => $role->value,
            },
            'label' => 'Rôle',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}