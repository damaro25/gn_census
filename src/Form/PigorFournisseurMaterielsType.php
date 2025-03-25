<?php

namespace App\Form;

use App\Form\ApplicationType;
use App\Entity\PigorFournisseurMateriel;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class PigorFournisseurMaterielsType extends ApplicationType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', TextType::class, $this->getOperation('Nom', 'Nom du fournisseur...'))
            ->add('telephone', TextType::class, $this->getOperation('Téléphone', 'N° de telephon du fournisseur...'))
            ->add('adresse', TextType::class, $this->getOperation('Adresse', 'Adresse du fournisseur...'))
            ->add('email', TextType::class, $this->getOperation('E-mail', 'Adresse mail du fournisseur...'))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PigorFournisseurMateriel::class,
        ]);
    }
}
