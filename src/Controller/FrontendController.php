<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Product;
use App\Repository\ProductRepository;

class FrontendController extends AbstractController
{
    #[Route('/', name: 'app_frontend')]
    public function index(ProductRepository $productRepository): Response
    {
         $products = $productRepository->findAll();
        return $this->render('frontend/index.html.twig', [
            'products' => $products
        ]);
    }
    #[Route('/product/{slug}', name: 'product_show')]
    public function show(Product $product): Response
    {
        // Le paramConverter de Symfony récupère automatiquement le produit via le slug
        return $this->render('frontend/product/show.html.twig', [
            'product' => $product,
        ]);
    }


}
