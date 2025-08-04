<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            // Champs visibles sur la page INDEX et aussi éditables
            TextField::new('nom'),
            TextField::new('prenom'),
            TextField::new('metier'),
            EmailField::new('email'),
            TextField::new('telephone'),
            ArrayField::new('roles'),
            TextField::new('fonction1'),
            TextField::new('fonction2'),
            TextField::new('statut'),
            TextField::new('nom_province'),
            TextField::new('nom_compagnon'),
            // Autres champs visibles uniquement en EDITION
            TextField::new('adresse1')->hideOnIndex(),
            TextField::new('adresse2')->hideOnIndex(),
            TextField::new('code_postale')->hideOnIndex(),
            TextField::new('ville')->hideOnIndex(),
            TextField::new('position')->hideOnIndex(),
            TextField::new('droit')->hideOnIndex(),
            TextField::new('photo')->hideOnIndex(),
           
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
            ->add('statut');
    }

    public function configureActions(Actions $actions): Actions
    {
        return parent::configureActions($actions);
    }
}