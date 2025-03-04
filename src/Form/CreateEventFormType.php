<?php

namespace App\Form;

use App\Entity\EventStatus;
use App\Entity\Location;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Event;

class CreateEventFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
            ])
            ->add('startAt', DateTimeType::class, [
                'widget' => 'single_text',
            ])
            ->add('registrationEndsAt', DateTimeType::class, [
                'widget' => 'single_text',
            ])
            ->add('duration', IntegerType::class, [
                'label' => 'Durée (minutes)',
            ])
            ->add('maxUsers', IntegerType::class, [
                'label' => 'Nombre max de participants',
            ])
            ->add('location', LocationType::class, [
                'label' => false,
            ])
            ->add('status', EntityType::class, [
                'class' => EventStatus::class,
                'choice_label' => 'label',
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Enregistrer',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}
