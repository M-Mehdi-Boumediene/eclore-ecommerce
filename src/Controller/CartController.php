<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Repository\ProductRepository;
use App\Repository\ProductCategoryRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;


class CartController extends AbstractController
{
 

 #[Route('/cart/add-ajax/{id}', name: 'cart_add_ajax', methods: ['POST'])]
public function addAjax(int $id, ProductRepository $productRepo, Request $request, SessionInterface $session): JsonResponse
{
    $product = $productRepo->find($id);
    if (!$product) {
        return new JsonResponse(['success' => false, 'message' => 'Produit introuvable'], 404);
    }

    $cart = $session->get('cart', []);

    if (isset($cart[$id])) {
        $cart[$id]['quantity']++;
    } else {
        $cart[$id] = ['product' => $product, 'quantity' => 1];
    }

    $session->set('cart', $cart);

    // Rendre le mini-cart
    $html = $this->renderView('cart/mini.html.twig', ['cart' => $cart]);

    return new JsonResponse([
        'success' => true,
        'html' => $html
    ]);
}
}