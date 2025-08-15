<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotCompromisedPassword;
use Symfony\Component\Validator\Constraints\PasswordStrength;

class ChangePasswordFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'options' => [
                    'attr' => [
                        'autocomplete' => 'nouveau mot de passe',
                        'class' => 'form-control', // Ajout pour alignement visuel
                    ],
                ],
                'first_options' => [
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Entrez un mot de passe',
                        ]),
                        new Length([
                            'min' => 12,
                            'minMessage' => 'Ton mot de passe doit contenir au moins {{ limit }} caractères',
                            'max' => 40,
                        ]),
                        new PasswordStrength([
                            'message' => 'Ton mot de passe doit comporter au moins 1 majuscule, 1 minuscule et 1 chiffre.',
                        ]),
                        new NotCompromisedPassword([
                            'message' => 'Ce mot de passe a été compromis. Choisis en un autre.',
                        ]),
                    ],
                    'label' => 'Nouveau mot de passe',
                    'label_attr' => [
                        'class' => 'form-label', // Pour aligner le label avec le champ
                    ],
                    'attr' => [
                        'class' => 'form-control', // Pour aligner le champ avec le label
                    ],
                ],
                'second_options' => [
                    'label' => 'Répéter le mot de passe',
                    'label_attr' => [
                        'class' => 'form-label',
                    ],
                    'attr' => [
                        'class' => 'form-control',
                    ],
                ],
                'invalid_message' => 'Les champs de mot de passe doivent correspondre.',
                'mapped' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}