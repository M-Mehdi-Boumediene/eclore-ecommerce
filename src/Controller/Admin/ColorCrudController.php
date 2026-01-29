<?php

namespace App\Controller\Admin;

use App\Entity\Color;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ColorCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Color::class;
    }

   public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name'),
            TextField::new('hex')->setHelp('Ex: #FF0000'),
        ];
    }
}
