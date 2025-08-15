<?php

namespace App\Form;

use App\Entity\Referent;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReferentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('referent', EntityType::class, [
                'class' => User::class,
                'choice_label' => fn(User $user) => $user->getPrenom() . ' ' . $user->getNom() . ' | ' . $user->getMetier() . ' | ' . $user->getStatut() . ' ' . $user->getPosition(),
                'label' => '👤 Personne référente',
                'placeholder' => 'Sélectionner un référent (Ancien)',
                'attr' => ['class' => 'form-select'],
            ])
            ->add('responsableDe', EntityType::class, [
                'class' => User::class,
                'choice_label' => fn(User $user) => $user->getPrenom() . ' ' . $user->getNom() . ' | ' . $user->getMetier() . ' | ' . $user->getStatut() . ' ' . $user->getPosition(),
                'label' => '👥 Est responsable de',
                'placeholder' => 'Sélectionner un itinérant',
                'attr' => ['class' => 'form-select'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Referent::class,
        ]);
    }
}