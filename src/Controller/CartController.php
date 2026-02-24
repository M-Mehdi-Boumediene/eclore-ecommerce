<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/cart')]
class CartController extends AbstractController
{
#[Route('/add-ajax/{id}', name: 'cart_add_ajax', methods: ['POST'])]
public function addAjax(int $id, ProductRepository $productRepo, Request $request, SessionInterface $session): JsonResponse
{
    $product = $productRepo->find($id);

    if (!$product) {
        return new JsonResponse(['success' => false, 'message' => 'Produit introuvable'], 404);
    }

    $data = json_decode($request->getContent(), true);
    $color = $data['color'] ?? null;
    $image = $data['image'] ?? null;

    $cart = $session->get('cart', []);

    $cartKey = $id . '_' . $color;

    if (isset($cart[$cartKey])) {
        $cart[$cartKey]['quantity']++;
    } else {
        $cart[$cartKey] = [
            'product' => [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'slug' => $product->getSlug(),
                'category' => $product->getProductCategory()->getSlug(),
                'price' => $product->getPrice(),
                'mainPhoto' => $product->getMainPhoto()
            ],
            'color' => $color,
            'image' => $image,
            'quantity' => 1
        ];
    }

    $session->set('cart', $cart);

    $html = $this->renderView('cart/mini.html.twig', ['cart' => $cart]);

    return new JsonResponse([
        'success' => true,
        'html' => $html
    ]);
}

#[Route('/remove-ajax/{key}', name: 'cart_remove_ajax', methods: ['POST'])]
public function removeAjax(string $key, SessionInterface $session): JsonResponse
{
    $cart = $session->get('cart', []);

    if (isset($cart[$key])) {
        unset($cart[$key]);
        $session->set('cart', $cart);
    }

    $html = $this->renderView('cart/mini.html.twig', ['cart' => $cart]);

    return new JsonResponse([
        'success' => true,
        'html' => $html
    ]);
}
}