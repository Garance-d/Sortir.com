<?php

namespace App\Controller;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class  UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Utilisateur')
            ->setEntityLabelInPlural('Utilisateurs')
            ->setPaginatorPageSize(10);
     }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id', 'ID')
                ->hideOnForm(),
            TextField::new('lastname', 'Nom'),
            TextField::new('firstname', 'Prenom'),
            TextField::new('email', 'Email')
                ->onlyOnIndex()
                ->onlyWhenCreating()
                ->setFormTypeOption('disabled', $pageName ===Crud::PAGE_EDIT),
            BooleanField::new('administrator', 'Administrateur'),
            AssociationField::new('campus', 'Campus'),
            TextField::new('username', 'Pseudo'),
            ArrayField::new('isActive','Activer')
                ->hideOnForm(),
            TextField::new('password', 'Mot de passe')
                ->onlyOnForms()
                ->onlyWhenCreating(),
        ];
    }

}
