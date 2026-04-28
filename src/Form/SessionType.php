<?php

namespace App\Form;

use App\Entity\Activite;
use App\Entity\Membre;
use App\Entity\Session;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SessionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nbPlace')
            ->add('heureDeb', TimeType::class, [
                'widget' => 'single_text',
                'html5' => true,
                'with_seconds' => false,
            ])
            ->add('heureFin', TimeType::class, [
                'widget' => 'single_text',
                'html5' => true,
                'with_seconds' => false,
            ])
            ->add('dateDeb', DateType::class, [
                'widget' => 'single_text',
                'html5' => true,
            ])
            ->add('dateFin', DateType::class, [
                'widget' => 'single_text',
                'html5' => true,
            ])
            ->add('activite', EntityType::class, [
                'class' => Activite::class,
                'choice_label' => 'nom',
            ])

            // 👇 CHAMP UNIQUE (Menu déroulant) 👇
            ->add('entraineur', EntityType::class, [
                'class' => Membre::class,
                'label' => 'Entraîneur',
                
                'multiple' => false, // 👈 UN SEUL CHOIX
                'expanded' => false, // 👈 MENU DÉROULANT
                
                'attr' => ['class' => 'form-select'],
                'placeholder' => 'Choisir un entraîneur...', // Option vide par défaut

                'choice_label' => function (Membre $membre) {
                    return $membre->getNom() . ' ' . $membre->getPrenom();
                },

                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('m')
                        ->leftJoin('m.role', 'r') 
                        ->where('r.lib = :titre') 
                        // 👇 J'ai mis l'accent ici puisque c'était ça le problème !
                        ->setParameter('titre', 'Entraîneur')
                        ->orderBy('m.nom', 'ASC');
                },
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Session::class,
        ]);
    }
}