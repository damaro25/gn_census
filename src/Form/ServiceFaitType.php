<?php

namespace App\Form;

use App\Entity\ServiceFait;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ServiceFaitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('createAt')
            ->add('observation')
            ->add('etat')
            ->add('mois')
            ->add('agent')
            ->add('opSaisi')
            ->add('mandat')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ServiceFait::class,
        ]);
    }
}
