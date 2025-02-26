<?php

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventType extends AbstractType
{

    public function buildForm(\Symfony\Component\Form\FormBuilderInterface $builder, array $options) : void
    {
        $builder
            ->add('campus', ChoiceType::class, [
                'label' => 'Site :',
                'choices' => [
                    'Nantes',
                    'Rennes',
                    'Quimmper',
                    'Niort',
                    'En ligne',
                ]
            ])
            ->add('event', EntityType::class, [
                'attr' => ['placeholder' => 'search'],
                'label' => 'Le nom de la sortie contient',
            ])
            ->add('date',   DateType::class, [])
            ->add ('event_checkb', CheckboxType::class, [
                'expanded' => true,
                'multiple' => true,

            ]);

    }
    public function configureOptions(OptionsResolver $resolver) : void {

        $resolver->setDefaults([
            'data_class' => \App\Entity\Event::class,
            'method' => 'GET',
            // 'csrf_protection' => false
        ]);
    }

}