<?php

namespace App\Form;

use App\Entity\Event;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => '🎯 Titre de l\'événement',
                'required' => true,
                'attr' => [
                    'id' => 'event_title',
                    'placeholder' => 'Ex : Fête de quartier, Conférence, etc.',
                    'class' => 'form-control',
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => '📝 Description',
                'required' => true,
                'attr' => [
                    'id' => 'event_description',
                    'placeholder' => 'Décrivez brièvement l\'événement...',
                    'rows' => 5,
                    'class' => 'form-control',
                ],
            ])
            ->add('location', TextType::class, [
                'label' => '📍 Lieu de l\'activité',
                'required' => true,
                'attr' => [
                    'id' => 'event_location',
                    'placeholder' => 'Ex : Salle polyvalente, Parc communal, etc.',
                    'class' => 'form-control',
                ],
            ])
            ->add('startDate', DateTimeType::class, [
                'label' => '⏰ Date de début',
                'widget' => 'single_text',
                'required' => true,
                'attr' => [
                    'id' => 'event_start',
                    'placeholder' => 'Sélectionnez la date et l\'heure de début',
                    'class' => 'form-control',
                ],
            ])
            ->add('endDate', DateTimeType::class, [
                'label' => '⏳ Date de fin',
                'widget' => 'single_text',
                'required' => true,
                'attr' => [
                    'id' => 'event_end',
                    'placeholder' => 'Sélectionnez la date et l\'heure de fin',
                    'class' => 'form-control',
                ],
            ])
            ->add('type', ChoiceType::class, [
                'label' => '📌 Type d\'événement',
                'choices' => [
                    'Communautaire' => 'Communautaire',
                    'Corporatif' => 'Corporatif',
                ],
                'required' => true,
                'placeholder' => 'Sélectionnez un type',
                'expanded' => false,
                'multiple' => false,
                'attr' => [
                    'id' => 'event_type',
                    'class' => 'form-select',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
    public function listEvents(Request $request, EventRepository $repo): Response
{
    $type = $request->query->get('type');

    $events = $type
        ? $repo->findBy(['type' => $type])
        : $repo->findAll();

    return $this->render('admin/events/list.html.twig', [
        'events' => $events,
        'selectedType' => $type,
    ]);
}
}