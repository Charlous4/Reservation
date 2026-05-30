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
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;


class MembreType extends AbstractType
{
    public function __construct(private AuthorizationCheckerInterface $auth) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('login')
            ->add('email')
            ->add('nom')
            ->add('prenom')
            ->add('plainPassword', PasswordType::class, [
                'label' => 'Nouveau mot de passe (laisser vide pour ne pas changer)',
                'mapped' => false,
                'required' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Ton mot de passe doit faire au moins {{ limit }} caractères',
                        'max' => 4096,
                    ]),
                ],
            ])
            ->add('role', EntityType::class, [
                'class' => Roles::class,
                'choice_label' => 'lib',
                'label' => 'Rôle',
                'placeholder' => 'Choisir un rôle...',
                'query_builder' => function (EntityRepository $er) {
                    // Si admin → tous les rôles, sinon → pas Administrateur
                    if ($this->auth->isGranted('ROLE_ADMIN')) {
                        return $er->createQueryBuilder('r');
                    }
                    return $er->createQueryBuilder('r')
                        ->where('r.lib != :admin')
                        ->setParameter('admin', 'Administrateur');
                },
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Membre::class,
        ]);
    }
}
