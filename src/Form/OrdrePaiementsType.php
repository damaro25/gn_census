<?php

namespace App\Form;

use App\Entity\OrdrePaiements;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrdrePaiementsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('numero')
            ->add('datePaiement')
            ->add('modeReglement')
            ->add('beneficiaires')
            ->add('montant')
            ->add('statut')
            ->add('mandat')
            ->add('opSaisi')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => OrdrePaiements::class,
        ]);
    }
}
