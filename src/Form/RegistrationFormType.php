<?php

namespace App\Form;

use App\Entity\Membre;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use App\Entity\Roles; // 👈 Vérifie si ta classe s'appelle Role ou Roles
    use Symfony\Bridge\Doctrine\Form\Type\EntityType;
    use Symfony\Component\Form\Extension\Core\Type\TextType;
    use Symfony\Component\Form\Extension\Core\Type\EmailType;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('login') // Ton identifiant
            ->add('email', EmailType::class, [
            'label' => 'Adresse Email',
        ])
            // 👇 AJOUTE LE NOM ET PRÉNOM (Indispensable pour un Membre)
            ->add('nom', TextType::class, [
                'label' => 'Nom'
            ])
            ->add('prenom', TextType::class, [
                'label' => 'Prénom'
            ])

            // 👇 AJOUTE LE MENU DÉROULANT DES RÔLES
            ->add('role', EntityType::class, [
                'class' => Roles::class, // ⚠️ Mets le nom exact de ton entité (Role ou Roles)
                'choice_label' => 'lib', // Le champ texte de ta table Role (ex: "Entraîneur")
                'label' => 'Je suis un :',
                'placeholder' => 'Choisir un rôle...',
            ])

            ->add('plainPassword', PasswordType::class, [
                // ... laisse le code du mot de passe tel quel ...
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        'max' => 4096,
                    ]),
                ],
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
