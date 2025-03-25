<?php

namespace App\Form;

use App\Form\ApplicationType;
use App\Entity\PigorApprovisionnementMateriel;
use App\Entity\PigorFournisseurMateriel;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PigorApprovisionnementMaterielType extends ApplicationType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('numero_bon_livraison', TextType::class, $this->getOperation('Numéro bon de livraison', 'Bon de livraison'))
            //->add('date', DateType::class, $this->getOperation('Date de livraison', ''))
            ->add('fournisseur', EntityType::class,[
                'class' => PigorFournisseurMateriel::class,
                'choice_label' => 'nom',
                'attr' => [
                    'class' => 'select2',
                ]
            ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PigorApprovisionnementMateriel::class,
        ]);
    }
}
