<?php

namespace App\Form;

use App\Entity\Membre;
use App\Entity\Roles;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Validator\Constraints\Length;

class MembreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
{
    $builder
        ->add('login')
        ->add('email')
        ->add('nom')
        ->add('prenom')
        // 👇 On remplace 'mdp' par ça :
        ->add('plainPassword', PasswordType::class, [
            'label' => 'Nouveau mot de passe (laisser vide pour ne pas changer)',
            'mapped' => false,    // Ce champ n'est pas lié direct à l'entité
            'required' => false,  // Pas obligatoire en modification
            'attr' => ['autocomplete' => 'new-password'],
            'constraints' => [
                new Length([
                    'min' => 6,
                    'minMessage' => 'Ton mot de passe doit faire au moins {{ limit }} caractères',
                    'max' => 4096,
                ]),
            ],
        ])
        // 👇 Si tu veux pouvoir changer le rôle ici aussi
        ->add('role', null, [
            'choice_label' => 'lib',
            'label' => 'Rôle'
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
