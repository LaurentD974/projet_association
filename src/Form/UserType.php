<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\{
    TextType,
    EmailType,
    TelType,
    PasswordType,
    ChoiceType,
    FileType
};
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\User;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder

        // 👤 Informations personnelles
        ->add('prenom', TextType::class, ['label' => 'Prénom'])
        ->add('nom', TextType::class, ['label' => 'Nom'])
        ->add('email', EmailType::class)
        ->add('password', PasswordType::class, ['required' => false])
        ->add('date_naissance', TextType::class, ['label' => 'Date de naissance']) // tu peux utiliser DateType si tu préfères

        // 📱 Coordonnées
        ->add('telephone', TelType::class)
        ->add('adresse1', TextType::class, ['label' => 'Adresse ligne 1'])
        ->add('adresse2', TextType::class, ['label' => 'Adresse ligne 2'])
        ->add('code_postale', TextType::class, ['label' => 'Code postal'])
        ->add('ville', TextType::class)

        // 🏢 Rôle et organisation
        ->add('roles', ChoiceType::class, [
            'choices'  => [
                'Utilisateur' => 'ROLE_USER',
                'Administrateur' => 'ROLE_ADMIN',
                'Modérateur' => 'ROLE_MODERATOR',
            ],
            'multiple' => true,
            'expanded' => false,
            'label' => 'Rôles'
        ])
        ->add('metier', TextType::class)
        ->add('statut', TextType::class)
        ->add('fonction1', TextType::class)
        ->add('fonction2', TextType::class)
        ->add('position', TextType::class)

        // 🧭 Province et compagnon
        ->add('nom_province', TextType::class)
        ->add('nom_compagnon', TextType::class)

        // 🖼️ Photo
        ->add('photo', FileType::class, ['label' => 'Photo (JPEG/PNG)', 'required' => false])

        // 🛂 Droits spécifiques
        ->add('droit', ChoiceType::class, [
            'choices' => [
                'Aucun droit spécifique' => 'none',
                'Peut publier' => 'publish',
                'Peut valider' => 'validate',
            ],
            'label' => 'Droit spécifique'
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}