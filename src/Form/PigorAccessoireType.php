<?php

namespace App\Form;

use App\Form\ApplicationType;
use App\Entity\PigorAccessoireMateriels;
use App\Entity\PigorTypeMateriels;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PigorAccessoireType extends ApplicationType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('libelle', TextType::class, $this->getOperation('Libelle', '(...)'))
            ->add('model', TextType::class, $this->getOperation('Modéle', 'modéle de l\'accessoire'))
            ->add('marque', TextType::class, $this->getOperation('Marque', 'marque'))
            ->add('type_materiel', EntityType::class,[
                'class' => PigorTypeMateriels::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('tm')
                    ->where('tm.identifiable = :hasIdent')
                    ->setParameter('hasIdent', FALSE);
                },
                'choice_label' => 'libelle',
                'attr' => [
                    'class' => 'select2',
                ]
            ])
            ->add('description', TextareaType::class, $this->getOperation('Description', 'Description du matériel'), [
                'required' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PigorAccessoireMateriels::class,
        ]);
    }
}
