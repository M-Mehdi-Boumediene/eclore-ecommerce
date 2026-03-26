<?php
namespace App\Controller;

use App\Service\CartService;
use App\Repository\ProductRepository;
use App\Repository\ProductCategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class FrontendController extends AbstractController
{
    public function __construct(private CartService $cartService) {}

    #[Route('/', name: 'app_frontend')]
    public function index(
        Request $request,
        ProductRepository $productRepository,
        ProductCategoryRepository $productCategoryRepository,
        SessionInterface $session
    ): Response {
        return $this->render('frontend/index.html.twig', [
            'products' => $productRepository->findAll(),
            'productCategories' => $productCategoryRepository->findAll(),
            'cart' => $this->getUser()
                ? $this->cartService->getItems()
                : $session->get('cart', [])
       
        ]);
    }

    #[Route('/shop', name: 'app_frontend_shop')]
    public function shop(
        Request $request,
        ProductRepository $productRepository,
        ProductCategoryRepository $productCategoryRepository,
        SessionInterface $session
    ): Response {
        return $this->render('frontend/shop.html.twig', [
            'products' => $productRepository->findAll(),
            'productCategories' => $productCategoryRepository->findAll(),

            // 👉 panier : DB si connecté, session sinon
            'cart' => $this->getUser()
                ? $this->cartService->getItems()
                : $session->get('cart', [])
        ]);
    }

    #[Route('/{categorySlug}/{productSlug}', name: 'product_show')]
    public function show(
        string $categorySlug,
        string $productSlug,
        Request $request,
        SessionInterface $session,
        ProductRepository $productRepository
    ): Response {
        $product = $productRepository->findOneBy(['slug' => $productSlug]);

        if (!$product || $product->getProductCategory()->getSlug() !== $categorySlug) {
            throw $this->createNotFoundException('Produit introuvable');
        }

        return $this->render('frontend/product/show.html.twig', [
            'product' => $product,
             'cart' => $this->getUser()
                ? $this->cartService->getItems()
                : $session->get('cart', [])
        ]);
    }
    
}