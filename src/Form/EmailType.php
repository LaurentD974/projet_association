<?PHP
// src/Form/EmailType.php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class EmailType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('expediteur', TextType::class, [
                'label' => 'Votre adresse email',
                'mapped' => false,
                'disabled' => true,
                'data' => $options['expediteur_email'] ?? '',
            ])
            ->add('message', TextareaType::class, [
                'label' => 'Votre message',
            ])
            ->add('destinataires', TextType::class, [
                'label' => 'Destinataires',
                'mapped' => false,
                'disabled' => true,
                'data' => $options['destinataires_emails'] ?? '',
            ]);
    }

    public function configureOptions(\Symfony\Component\OptionsResolver\OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'expediteur_email' => '',
            'destinataires_emails' => '',
        ]);
    }
}