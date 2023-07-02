<?php

namespace App\Controller\Admin;

use App\Entity\Parameters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class ParametersCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Parameters::class;
    }

    /*
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('title'),
            TextEditorField::new('description'),
        ];
    }
    */
}
