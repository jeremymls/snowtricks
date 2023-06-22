<?php

namespace App\Controller\Admin;

use App\Entity\Trick;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class TrickCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Trick::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPaginatorPageSize(100)
            ->showEntityActionsInlined()
        ;
    }
}
