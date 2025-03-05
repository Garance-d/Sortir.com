<?php

namespace App\Form;

use App\Entity\Campus;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
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
                'placeholder' => 'Sélectionnez le campus',
                'required' => false,
            ])
            ->add('eventName', TextType::class, [ // Correction ici
                'label' => 'Le nom de la sortie contient',
                'required' => false,
                'attr' => ['placeholder' => 'Rechercher un événement'],
            ])
            ->add('date', DateType::class, [
                'label' => 'Date de l\'événement',
                'widget' => 'single_text', // Permet un affichage propre en input HTML5
                'required' => false,
            ])
            ->add('eventCheckbUser', CheckboxType::class, [
                'label' => 'Événements auxquels je suis inscrit(e)',
                'required' => false,
            ])
            ->add('eventCheckbHost', CheckboxType::class, [
                'label' => 'Événements pour lesquels je suis l\'organisateur.rice',
                'required' => false,
            ])
            ->add('eventCheckbArchive', CheckboxType::class, [
                'label' => 'Événements archivés',
                'required' => false,
            ]);
    }
    public function configureOptions(OptionsResolver $resolver) : void {

        $resolver->setDefaults([
            'data_class' => null,
            'method' => 'GET',
            // 'csrf_protection' => false
        ]);
    }
}