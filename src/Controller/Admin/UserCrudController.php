<?php

namespace App\Controller\Admin;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly UserPasswordHasherInterface $hasher
    ) {}

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureFields(string $pageName): iterable
    {
        // Index / show
        $rolesIndex = ArrayField::new('roles', 'Rôles')->hideOnForm();

        // Edition des rôles sur formulaire
        $rolesForm = ChoiceField::new('roles', 'Rôles')
            ->setChoices([
                'Utilisateur'          => 'ROLE_USER',
                'Gâcheur (Maître métier)' => 'ROLE_GACHEUR',
                'Admin'                => 'ROLE_ADMIN',
            ])
            ->allowMultipleChoices()
            ->renderExpanded(true) // liste déroulante multi (mettre true pour cases à cocher)
            ->onlyOnForms();

        return [
            TextField::new('nom', 'Nom'),
            TextField::new('prenom', 'Prénom'),
            TextField::new('metier', 'Métier'),
            EmailField::new('email', 'Email'),
            TelephoneField::new('telephone', 'Téléphone')->hideOnIndex(),

            $rolesIndex,
            $rolesForm,

            TextField::new('fonction1', 'Fonction 1')->hideOnIndex(),
            TextField::new('fonction2', 'Fonction 2')->hideOnIndex(),
            TextField::new('statut', 'Statut')->hideOnIndex(),
            TextField::new('position', 'Position')->hideOnIndex(),
            TextField::new('nomProvince', 'Nom de province')->hideOnIndex(),
            TextField::new('nomCompagnon', 'Nom de compagnon')->hideOnIndex(),

            TextField::new('adresse1', 'Adresse 1')->hideOnIndex(),
            TextField::new('adresse2', 'Adresse 2')->hideOnIndex(),
            TextField::new('codePostale', 'Code postal')->hideOnIndex(),
            TextField::new('ville', 'Ville')->hideOnIndex(),
            TextField::new('droit', 'Droit')->hideOnIndex(),
            TextField::new('photo', 'Photo')->hideOnIndex(),
            TextField::new('passetemps', 'Passe-temps')->hideOnIndex(),

            DateField::new('dateArrivee', 'Date d’arrivée')->hideOnIndex(),
            DateField::new('dateDepart',  'Date de départ')->hideOnIndex(),

            // Mot de passe : champ non persisté, on hash si renseigné
            TextField::new('plainPassword', 'Mot de passe')
                ->onlyOnForms()
                ->setFormType(PasswordType::class)
                ->setRequired($pageName === Crud::PAGE_NEW)
                ->setHelp('Laissez vide pour ne pas modifier le mot de passe.'),
        ];
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Utilisateur')
            ->setEntityLabelInPlural('Utilisateurs')
            ->setDefaultSort(['nom' => 'ASC'])
            ->setPaginatorPageSize(20);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('nom')
            ->add('prenom')
            ->add('email')
            ->add('statut')
            ->add('metier')
            ->add('position');
    }

    public function configureActions(Actions $actions): Actions
    {
        return parent::configureActions($actions);
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof User) {
            $this->hashPasswordIfNeeded($entityInstance);
        }
        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof User) {
            $this->hashPasswordIfNeeded($entityInstance);
        }
        parent::updateEntity($entityManager, $entityInstance);
    }

    private function hashPasswordIfNeeded(User $user): void
    {
        $plain = $user->getPlainPassword();
        if (\is_string($plain) && $plain !== '') {
            $user->setPassword($this->hasher->hashPassword($user, $plain));
            $user->setPlainPassword(null);
        }
    }
}
