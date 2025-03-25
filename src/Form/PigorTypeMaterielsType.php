<?php

namespace App\Form;

use App\Form\ApplicationType;
use Symfony\Component\Form\AbstractType;
use App\Entity\PigorTypeMateriels;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class PigorTypeMaterielsType extends ApplicationType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('libelle', TextType::class, $this->getOperation('Libelle', 'Nom du type de matériel...'))
            
            ->add('identifiable', ChoiceType::class, [
                'label' => 'Identification',
                'choices'  => [
                    'Matériel (Si chaque matériel de ce type à un code unique)' => true,
                    'Accessoire' => false,
                ],
            ])
            ->add('description', TextareaType::class, $this->getOperation('Description', 'Description du type de matériel'), [
                'required' => false
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PigorTypeMateriels::class,
        ]);
    }
}
