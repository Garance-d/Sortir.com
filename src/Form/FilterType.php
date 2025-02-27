<?php

namespace App\Form;

use App\Entity\Campus;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilterType extends AbstractType
{

    public function buildForm(\Symfony\Component\Form\FormBuilderInterface $builder, array $options) : void
    {
        $builder
            ->add('campus', EntityType::class, [
                'class' => Campus::class,
                'choice_label' => 'name',
                'label' => 'Campus',
                'placeholder' => 'Sélectionnez votre campus',
            ])
            ->add('event', EntityType::class, [
                'class' => \App\Entity\Event::class, // Assurez-vous de spécifier la bonne entité
                'choice_label' => 'name', // Le champ qui sera affiché dans le formulaire
                'attr' => ['placeholder' => 'search'],
                'label' => 'Le nom de la sortie contient',
                'required' => false, // Ce champ peut être facultatif
            ])

            ->add('date',   DateType::class, [])
            ->add('eventCheckb', CheckboxType::class, [
                'label' => 'Événements auxquels je suis inscrit/e',
                'required' => false,
            ]);
    }
    public function configureOptions(OptionsResolver $resolver) : void {

        $resolver->setDefaults([
            'data_class' => \App\Entity\Filter::class,
            'method' => 'GET',
            // 'csrf_protection' => false
        ]);
    }
}