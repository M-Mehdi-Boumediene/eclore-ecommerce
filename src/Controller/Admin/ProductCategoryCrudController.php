<?php

namespace App\Controller\Admin;

use App\Entity\ProductCategory;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;

class ProductCategoryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ProductCategory::class;
    }

   public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name'),
            ImageField::new('photo')
                ->setBasePath('uploads/categories') // chemin public pour afficher
                ->setUploadDir('public/uploads/categories') // dossier où stocker
                ->setUploadedFileNamePattern('[randomhash].[extension]')
                ->setRequired(false),
        ];
    }
}
