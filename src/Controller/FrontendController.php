<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Entity\Product;
use App\Entity\ProductCateory;
use App\Repository\ProductRepository;
use App\Repository\ProductCategoryRepository;


class FrontendController extends AbstractController
{
    #[Route('/', name: 'app_frontend')]
    public function index(ProductRepository $productRepository, ProductCategoryRepository $productCategoryRepository): Response
    {
         $products = $productRepository->findAll();
         $productCategory = $productCategoryRepository->findAll();
        return $this->render('frontend/index.html.twig', [
            'products' => $products,
            'productCategories' => $productCategory
        ]);
    }
    #[Route('/shop', name: 'app_frontend_shop')]
    public function shop(
        Request $request,
        ProductRepository $productRepository, 
        ProductCategoryRepository $productCategoryRepository,
        SessionInterface $session
    ): Response
    {
        $products = $productRepository->findAll();
        $productCategories = $productCategoryRepository->findAll();

        // Récupérer le panier depuis la session
        $cart = $session->get('cart', []); 
        // Exemple de structure: 
        // $cart = [
        //     1 => ['product' => $productObj, 'quantity' => 2],
        //     3 => ['product' => $productObj, 'quantity' => 1]
        // ];

        return $this->render('frontend/shop.html.twig', [
            'products' => $products,
            'productCategories' => $productCategories,
            'cart' => $cart
        ]);
    }
   #[Route('/{categorySlug}/{productSlug}', name: 'product_show')]
    public function show(
        string $categorySlug,
        string $productSlug,
        ProductRepository $productRepository
    ): Response
    {
        $product = $productRepository->findOneBy([
            'slug' => $productSlug
        ]);

        if (!$product) {
            throw $this->createNotFoundException('Produit introuvable');
        }

        // Vérifier que la catégorie correspond à l’URL
        if ($product->getProductCategory()->getSlug() !== $categorySlug) {
            throw $this->createNotFoundException('Catégorie invalide');
        }

        return $this->render('frontend/product/show.html.twig', [
            'product' => $product,
        ]);
    }


}

