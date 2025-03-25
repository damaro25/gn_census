<?php

namespace App\Form;

use App\Entity\PigorApprovisionnement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;


class PigorApprovisionnementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fournisseurs',null ,['label' => 'Fournisseurs' ,'attr'=> []])
            //->add('dateOper')
            ->add('dateOper',null ,['label' => false ,'attr'=> ['style'=>'display:none;' ]])
            ->add('bonLivraison',null ,['label' => 'Bon de livraison' ,'attr'=> []])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PigorApprovisionnement::class,
        ]);
    }
}
