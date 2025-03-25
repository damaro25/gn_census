<?php

namespace App\Form;

use App\Entity\Mandat;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MandatType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('numero')
            ->add('createAt')
            ->add('datePriseEnCharge')
            ->add('objetDepense')
            ->add('montant')
            ->add('observations')
            ->add('beneficiaires')
            ->add('opSaisi')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Mandat::class,
        ]);
    }
}
