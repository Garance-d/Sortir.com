<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Filter;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;


class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('campus', EntityType::class, [
                'class' => Filter::class,
                'label' => 'Site : ',
                'placeholder' => 'Sélectionnez votre campus',
                'attr' => ['class' => 'placeholder'],
            ])
            ->add('event', SearchType::class, [
                'class' => Filter::class,
                'attr' => ['class' => 'search'],
                'label' => 'Le nom de la sortie contient',
            ])
            ->add('eventCheckb', ChoiceType::class, [
                'class' => Filter::class,
                'multiple' => true,
                'expanded' => true, // Afficher sous forme de cases à cocher
                'choices_label' => [
                    'Sorties dont je suis l organisteur/trice'  => 'value1',
                    'Sorties auquelles je suis inscrit/e' => 'value2',
                    'Sorties auquelles je ne suis pas inscrit/e' => 'value3',
                    'Sorties passee' => 'value4',
                ],
                'attr' => [
                    'class' => 'container',
                ]
            ])
            ->add('search', SubmitType::class, [
                'label' => 'Rechercher',
                'attr' => ['class' => 'btn btn-primary'],
            ]);
    }
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
