<?php

namespace App\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Event;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options) : void
    {
        $builder
            ->add('campus', ChoiceType::class, [
                'label' => 'Site :',
                'choices' => [
                    'Nantes' => 'Nantes',
                    'Rennes' => 'Rennes',
                    'Quimper' => 'Quimper',
                    'Niort' => 'Niort',
                    'En ligne' => 'En ligne',
                ]
            ])
            ->add('event', EntityType::class, [
                'class' => Event::class,
                'choice_label' => 'name', // Remplace 'name' par le champ de l'entité Event
                'attr' => ['placeholder' => 'search'],
                'label' => 'Le nom de la sortie contient',
            ])
            ->add('date', DateType::class, [])
            ->add('event_checkb', ChoiceType::class, [
                'label' => 'Options',
                'choices' => [
                    'Option 1' => 'option_1',
                    'Option 2' => 'option_2',
                ],
                'expanded' => true,
                'multiple' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver) : void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
            'method' => 'GET',
        ]);
    }
}
