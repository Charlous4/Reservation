<?php

namespace App\Form;

use App\Entity\Activite;
use App\Entity\Type;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;



class ActiviteType extends AbstractType
{
    
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        
        $builder
            ->add('nom')
            ->add('description')
            ->add('capaciteMax')
            ->add('prix', NumberType::class, [
                'label' => 'Prix (€)',
                'scale' => 2, // nombre de décimales
                'html5' => true,
                'attr' => [
                    'step' => '0.01',
                    'min' => 0,
                    'class' => 'form-control',
                ],
            ])
            ->add('nvDifficulte', IntegerType::class, [
                'label' => 'Niveau de difficulté',
                'attr' => [
                'min' => 1,
                'max' => 3,
                'class' => 'form-control',
    ],
])

            ->add('type', EntityType::class, [
                'class' => Type::class,
                'choice_label' => 'lib',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Activite::class,
        ]);
    }

    
}
