<?php

namespace App\Form;

use App\Form\ApplicationType;
use App\Entity\PigorApprovisionnementAccessoires;
use App\Entity\PigorAccessoireMateriels;
use App\Entity\PigorFournisseurMateriel;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PigorApprovisionnementAccessoireType extends ApplicationType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // ->add('date', DateType::class, $this->getOperation('Date approvisionnement', ''))
            ->add('fournisseur', EntityType::class,[
                'class' => PigorFournisseurMateriel::class,
                'choice_label' => 'nom',
                'attr' => [
                    'class' => 'select2',
                ]
            ])
            ->add('accessoire', EntityType::class,[
                'class' => PigorAccessoireMateriels::class,
                'choice_label' => 'libelle',
                'attr' => [
                    'class' => 'select2',
                ]
            ])
            ->add('quantite_recu', IntegerType::class, $this->getOperation('Quantité reçue', 'quantité'))
            ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PigorApprovisionnementAccessoires::class,
        ]);
    }
}
