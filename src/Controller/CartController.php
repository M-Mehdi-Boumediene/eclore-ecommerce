<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use App\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/cart')]
class CartController extends AbstractController
{
    public function __construct(private CartService $cartService) {}

    #[Route('/add-ajax/{id}', name: 'cart_add_ajax', methods: ['POST'])]
    public function addAjax(
        int $id,
        ProductRepository $productRepo,
        Request $request,
        SessionInterface $session
    ): JsonResponse {
        $product = $productRepo->find($id);

        if (!$product) {
            return new JsonResponse(['success' => false, 'message' => 'Produit introuvable'], 404);
        }

        $data = json_decode($request->getContent(), true);
        $quantity = $data['quantity'] ?? 1;
        $color = $data['color'] ?? null;
        $size = $data['size'] ?? null;
        $image = $data['image'] ?? null; // 👈 image du variant

        // UTILISATEUR CONNECTÉ → DB
        if ($this->getUser()) {
            $this->cartService->addProduct($product, $quantity, $color, $size, $image);

            return new JsonResponse([
                'success' => true,
                'html' => $this->renderView('cart/mini.html.twig', [
                    'cart' => $this->cartService->getItems()
                ])
            ]);
        }

        // VISITEUR → SESSION
        $cart = $session->get('cart', []);
        $cartKey = (string) $id . '-' . ($color ?? 'nocolor') . '-' . ($size ?? 'nosize');

        if (isset($cart[$cartKey])) {
            $cart[$cartKey]['quantity'] += $quantity;
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
                'quantity' => $quantity,
                'color' => $color,
                'size' => $size,
                'image' => $image
            ];
        }

        $session->set('cart', $cart);

        return new JsonResponse([
            'success' => true,
            'html' => $this->renderView('cart/mini.html.twig', ['cart' => $cart])
        ]);
    }

    #[Route('/remove-ajax/{key}', name: 'cart_remove_ajax', methods: ['POST'])]
    public function removeAjax(string $key, SessionInterface $session): JsonResponse
    {
        // UTILISATEUR CONNECTÉ → DB
        if ($this->getUser()) {
            $items = $this->cartService->getItems();

            if (isset($items[$key])) {
                $item = $items[$key];

                $this->cartService->removeProduct(
                    $item['product'],
                    $item['color'],
                    $item['size']
                );
            }

            return new JsonResponse([
                'success' => true,
                'html' => $this->renderView('cart/mini.html.twig', [
                    'cart' => $this->cartService->getItems()
                ])
            ]);
        }

        // VISITEUR → SESSION
        $cart = $session->get('cart', []);

        if (isset($cart[$key])) {
            unset($cart[$key]);
            $session->set('cart', $cart);
        }

        return new JsonResponse([
            'success' => true,
            'html' => $this->renderView('cart/mini.html.twig', ['cart' => $cart])
        ]);
    }

    #[Route('/update-ajax/{key}', name: 'cart_update_ajax', methods: ['POST'])]
    public function updateAjax(string $key, Request $request, SessionInterface $session): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $quantity = max(1, (int) ($data['quantity'] ?? 1));

        // Utilisateur connecté → DB
        if ($this->getUser()) {
            $items = $this->cartService->getItems();

            if (!isset($items[$key])) {
                return new JsonResponse(['success' => false], 404);
            }

            $item = $items[$key];
            $cartItem = $this->cartService->findCartItem(
                $item['product'],
                $item['color'],
                $item['size']
            );

            if ($cartItem) {
                $cartItem->setQuantity($quantity);
                $this->cartService->flush();
            }

            return new JsonResponse([
                'success' => true,
                'html' => $this->renderView('cart/mini.html.twig', [
                    'cart' => $this->cartService->getItems()
                ]),
                'total' => $this->cartService->getTotal()
            ]);
        }

        // Visiteur → session
        $cart = $session->get('cart', []);

        if (isset($cart[$key])) {
            $cart[$key]['quantity'] = $quantity;
            $session->set('cart', $cart);
        }

        return new JsonResponse([
            'success' => true,
            'html' => $this->renderView('cart/mini.html.twig', ['cart' => $cart]),
            'total' => $this->cartService->getTotalFromSession($cart)
        ]);
    }
}