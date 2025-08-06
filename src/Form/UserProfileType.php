<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use App\Entity\User;

class UserProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('photo', FileType::class, [
                'label' => '📷 Photo de profil',
                'required' => false,
                'mapped' => false,
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/webp',
                        ],
                        'mimeTypesMessage' => 'Veuillez uploader une image valide (JPEG, PNG ou WEBP).',
                    ])
                ],
            ])
            ->add('adresse1', TextType::class, [
                'label' => '🏠 Adresse 1',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('adresse2', TextType::class, [
                'label' => '🏠 Adresse 2',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('code_postale', TextType::class, [
                'label' => '📮 Code postal',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('ville', TextType::class, [
                'label' => '🏙️ Ville',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
                ->add('dateArrivee', DateType::class, [
        'label' => '📅 Date d\'arrivée',
        'required' => false,
        'widget' => 'single_text',
        'attr' => ['class' => 'form-control'],
    ])
    ->add('dateDepart', DateType::class, [
        'label' => '📅 Date de départ',
        'required' => false,
        'widget' => 'single_text',
        'attr' => ['class' => 'form-control'],
    ])

            ->add('telephone', TextType::class, [
                'label' => '📞 Téléphone',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => '+262 692 12 34 56'],
                'constraints' => [
                    new Regex([
                        'pattern' => '/^\+?\d{1,4}[\s.-]?\(?\d+\)?[\s.-]?\d+[\s.-]?\d+$/',
                        'message' => 'Veuillez entrer un numéro de téléphone valide.',
                    ]),
                ],
            ])
            ->add('currentPassword', PasswordType::class, [
                'label' => '🔒 Mot de passe actuel',
                'mapped' => false,
                'required' => false,
                'attr' => ['autocomplete' => 'current-password', 'class' => 'form-control'],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer votre mot de passe actuel.']),
                ],
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'required' => false,
                'first_options' => [
                    'label' => '🔑 Nouveau mot de passe',
                    'attr' => ['autocomplete' => 'new-password', 'class' => 'form-control'],
                ],
                'second_options' => [
                    'label' => '🔁 Confirmer le mot de passe',
                    'attr' => ['autocomplete' => 'new-password', 'class' => 'form-control'],
                ],
                'invalid_message' => 'Les mots de passe ne correspondent pas.',
                'constraints' => [
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Le mot de passe doit contenir au moins {{ limit }} caractères',
                        'max' => 4096,
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}