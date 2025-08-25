<?php
// src/Form/SendEmailType.php  (recommandé: renomme ton fichier/classe)
// namespace adapté si tu gardes EmailType: ajuste les "use" en conséquence.

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\EmailType as SymfonyEmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

class SendEmailType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Affichage en lecture seule de l'expéditeur
            ->add('expediteur', SymfonyEmailType::class, [
                'label'   => 'Votre adresse email',
                'mapped'  => false,
                'disabled'=> true,
                'data'    => $options['expediteur_email'] ?? '',
            ])
            ->add('message', TextareaType::class, [
                'label' => 'Votre message',
                'attr'  => ['rows' => 6],
            ])

            // ✅ Cases à cocher pour chaque destinataire
            ->add('destinataires', EntityType::class, [
                'class'        => User::class,
                // on passe la liste depuis le contrôleur (voir plus bas)
                'choices'      => $options['destinataires_choices'],
                'choice_label' => static function (User $u): string {
                    // Adapte ces getters à ton entité
                    return sprintf('%s %s — %s', $u->getPrenom(), $u->getNom(), $u->getEmail());
                },
                'multiple'     => true,
                'expanded'     => true,   // => cases à cocher
                'mapped'       => false,  // on lira la sélection dans le contrôleur
                'by_reference' => false,
                'label'        => 'Destinataires',
            ])
        ;

        // ✅ Tout cocher par défaut si aucune data n'est encore présente
        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) use ($options) {
            $form = $event->getForm();
            $selected = $form->get('destinataires')->getData();
            if (!$selected || \count($selected) === 0) {
                $form->get('destinataires')->setData($options['destinataires_choices']);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'expediteur_email'      => '',
            // tableau d'objets User à injecter depuis le contrôleur
            'destinataires_choices' => [],
        ]);
    }
}
