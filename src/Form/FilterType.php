<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Filter;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Event;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('campus', EntityType::class, [
                'class' => Campus::class,
                'choice_label' => 'name',
                'label' => 'Campus',
                'placeholder' => 'Sélectionnez votre campus',
            ])
            ->add('event', EntityType::class, [
                'class' => Event::class, // Vous n'avez pas besoin du "\" ici si vous avez importé `App\Entity\Event`
                'choice_label' => 'name',
                'attr' => ['placeholder' => 'search'],
                'label' => 'Le nom de la sortie contient',
                'required' => false,
            ])
            ->add('date', DateType::class, [])
            ->add('eventCheckb', CheckboxType::class, [
                'label' => 'Événements auxquels je suis inscrit/e',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Filter::class,
            'method' => 'GET',
        ]);
    }
}
