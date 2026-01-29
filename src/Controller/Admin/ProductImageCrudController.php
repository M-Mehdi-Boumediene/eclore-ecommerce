<?php

namespace App\Controller\Admin;

use App\Entity\ProductImage;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;

class ProductImageCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ProductImage::class;
    }

   public function configureFields(string $pageName): iterable
    {
        return [
            ImageField::new('path')
                ->setBasePath('uploads/product-images')
                ->setUploadDir('public/uploads/product-images')
                ->setUploadedFileNamePattern('[randomhash].[extension]'),
            AssociationField::new('product'),
            AssociationField::new('color')->setRequired(false),
        ];
    }
}
