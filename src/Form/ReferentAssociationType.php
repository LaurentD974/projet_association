<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ReferentAssociationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Menu de gauche : SÉDENTAIRES (référents)
            ->add('referent', EntityType::class, [
                'class'        => User::class,
                'label'        => 'Ancien (Référent)',
                'choices'      => $options['choices_referents'],  // injecté par le contrôleur
                'group_by'     => fn(User $u) => $u->getMetier() ?: '—',
                'choice_label' => fn(User $u) => sprintf('%s %s — %s', $u->getPrenom(), $u->getNom(), $u->getEmail()),
                'placeholder'  => 'Sélectionner un sédentaire…',
                'attr'         => ['class' => 'form-select'],
                'mapped'       => false,  // on lira la valeur dans le contrôleur
            ])

            // Menu de droite : ITINÉRANTS
            ->add('responsableDe', EntityType::class, [
                'class'        => User::class,
                'label'        => 'Itinérant',
                'choices'      => $options['choices_itinerants'], // injecté par le contrôleur
                'group_by'     => fn(User $u) => $u->getMetier() ?: '—',
                'choice_label' => fn(User $u) => sprintf('%s %s — %s', $u->getPrenom(), $u->getNom(), $u->getEmail()),
                'placeholder'  => 'Sélectionner un itinérant…',
                'attr'         => ['class' => 'form-select'],
                'mapped'       => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Le formulaire ne mappe pas une entité ici (on récupère les 2 users et on persiste l’association côté contrôleur)
            'data_class'        => null,
            // Listes à fournir depuis le contrôleur (déjà filtrées et triées)
            'choices_referents'  => [],   // array<User>
            'choices_itinerants' => [],   // array<User>
        ]);
    }
}
