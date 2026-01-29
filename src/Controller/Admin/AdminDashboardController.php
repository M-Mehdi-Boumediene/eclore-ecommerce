<?php

namespace App\Controller\Admin;
use App\Entity\Product;
use App\Entity\ProductCategory;
use App\Entity\Color;
use App\Entity\ProductImage;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class AdminDashboardController extends AbstractDashboardController
{
    public function index(): Response
    {
      

         $adminUrlGenerator = $this->container->get(\EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator::class);
         return $this->redirect($adminUrlGenerator->setController(ProductCrudController::class)->generateUrl());
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Eclore Ecommerce');
    }

   public function configureMenuItems(): iterable
    {
        yield MenuItem::linktoDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::linkToCrud('Categories', 'fa fa-folder', ProductCategory::class);
        yield MenuItem::linkToCrud('Colors', 'fa fa-palette', Color::class);
        yield MenuItem::linkToCrud('Products', 'fa fa-box', Product::class);
        yield MenuItem::linkToCrud('Product Images', 'fa fa-image', ProductImage::class);
    }
}
