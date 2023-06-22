<?php

namespace App\Controller\Admin;

use App\Entity\Comment;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;

class CommentCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Comment::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            AssociationField::new('trick'),
            AssociationField::new('user'),
            TextField::new('text'),
            DateTimeField::new('createdAt'),
            DateTimeField::new('deletedAt'),
        ];
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setPaginatorPageSize(100)
            ->renderContentMaximized()
            ->renderSidebarMinimized()
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->showEntityActionsInlined()
            ->setPageTitle(Crud::PAGE_INDEX, 'Commentaires')
            ->setPageTitle(Crud::PAGE_NEW, 'Créer un commentaire')
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifier un commentaire')
            ->setPageTitle(Crud::PAGE_DETAIL, 'Détails du commentaire')
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('trick'))
            ->add(EntityFilter::new('user'))
            ->add(DateTimeFilter::new('createdAt'))
            ->add(DateTimeFilter::new('deletedAt'))
        ;
    }
}
