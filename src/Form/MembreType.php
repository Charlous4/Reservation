<?php

namespace App\Form;

use App\Entity\Membre;
use App\Entity\Roles;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class MembreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('prenom')
            ->add('login')
            ->add('mdp', PasswordType::class, [
        'label' => 'Mot de passe',
        'attr' => [
            'class' => 'form-control',
            'placeholder' => '********'
        ],
    ])
            ->add('mail')
            ->add('numTel')
            ->add('role', EntityType::class, [
                'class' => Roles::class,
                'choice_label' => 'Lib',
            ])
        ; 
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Membre::class,
        ]);
    }
}
