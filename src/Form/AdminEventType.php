<?php

namespace App\Form;

use App\Entity\Event;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdminEventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
            ])
            ->add('location', TextType::class, [
                'label' => 'Lieu',
            ])
            ->add('startDate', DateTimeType::class, [
                'label' => 'Date de début',
                'widget' => 'single_text',
            ])
            ->add('endDate', DateTimeType::class, [
                'label' => 'Date de fin',
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Type d\'événement',
                'choices' => [
                    'Communautaire' => 'Communautaire',
                    'Charpentier' => 'Charpentier',
                    'Maçon' => 'Maçon',
                    'Serrurier' => 'Serrurier',
                    'Couvreur' => 'Couvreur',
                    'Menuisier' => 'Menuisier',
                    'Chaudronnier' => 'Chaudronnier',
                    'Mécanicien' => 'Mécanicien',
                    'Peintre' => 'Peintre',
                    'Boulanger' => 'Boulanger',
                    'Patissier' => 'Patissier',
                    'Plombier' => 'Plombier',
                    'Tapissier' => 'Tapissier',
                    'Charcutier' => 'Charcutier',
                ],
                'placeholder' => 'Choisir un type',
            ])
            ->add('isValidated', CheckboxType::class, [
                'label' => 'Événement validé',
                'required' => false,
            ]);
    
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}