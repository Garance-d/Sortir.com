<?php

namespace App\Form;

use App\Entity\Location;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LocationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom du lieu',
            ])
            ->add('street', TextType::class, [
                'label' => 'Adresse',
                'attr' => ['id' => 'address', 'placeholder' => 'Saisissez une adresse']
            ])
            ->add('latitude', HiddenType::class, [
                'attr' => ['id' => 'latitude']
            ])
            ->add('longitude', HiddenType::class, [
                'attr' => ['id' => 'longitude']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Location::class,
        ]);
    }
}
