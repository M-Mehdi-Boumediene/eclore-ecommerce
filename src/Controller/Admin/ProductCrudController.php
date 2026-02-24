<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use App\Entity\Size;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints\File;
use Doctrine\ORM\EntityManagerInterface;

class ProductCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Product::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name'),
            TextEditorField::new('description'),
            MoneyField::new('price')
            ->setCurrency('DZD')     // devise
            ->setStoredAsCents(false) // important pour éviter que EasyAdmin multiplie par 100
            ->setNumDecimals(2) ,
            AssociationField::new('productCategory'),
            AssociationField::new('colors')->setFormTypeOptions(['by_reference' => false]),
            AssociationField::new('sizes')
            ->setFormTypeOptions([
                'by_reference' => false
            ]),
            ImageField::new('mainPhoto')
                ->setBasePath('uploads/products')
                ->setUploadDir('public/uploads/products')
                ->setUploadedFileNamePattern('[randomhash].[extension]')
                ->setRequired(false),
            FormField::addPanel('Video'),
            Field::new('mainVideo')
                ->setFormType(FileType::class)
                ->setFormTypeOptions([
                    'required' => false,
                    'mapped' => false, // ⚠️ important pour upload manuel
                    'constraints' => [
                        new File([
                            'maxSize' => '100M',
                            'mimeTypes' => ['video/mp4'],
                            'mimeTypesMessage' => 'Please upload a valid MP4 video',
                        ])
                    ],
                ]),
        ];
    }

    /**
     * Persists a new Product entity with manual video upload
     */
    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->handleVideoUpload($entityInstance);
        parent::persistEntity($entityManager, $entityInstance);
    }

    /**
     * Updates an existing Product entity with manual video upload
     */
    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->handleVideoUpload($entityInstance);
        parent::updateEntity($entityManager, $entityInstance);
    }

    /**
     * Gère l'upload manuel de la vidéo
     */
    private function handleVideoUpload($entityInstance): void
    {
        if (!$entityInstance instanceof Product) return;

        $request = $this->getContext()->getRequest();
        $form = $request->files->get('Product');

        /** @var UploadedFile|null $videoFile */
        $videoFile = $form['mainVideo'] ?? null;

        if ($videoFile instanceof UploadedFile) {
            $newFilename = uniqid() . '.' . $videoFile->guessExtension();

            try {
                $videoFile->move(
                    $this->getParameter('videos_directory'), // défini dans services.yaml
                    $newFilename
                );
                $entityInstance->setMainVideo($newFilename);
            } catch (\Exception $e) {
                // Optionnel : gérer l’erreur d’upload
            }
        }
    }
}
