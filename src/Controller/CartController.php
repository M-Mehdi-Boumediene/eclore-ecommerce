<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use App\Service\CartService;
use App\Form\CheckoutType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/cart')]
class CartController extends AbstractController
{
    public function __construct(private CartService $cartService) {}

    #[Route('/view', name: 'viewCart')]
    public function viewCart(Request $request, CartService $cartService, ProductRepository $productRepository): Response
    {
        $session = $request->getSession();
        $user = $this->getUser();

        // 1️⃣ Panier pour utilisateur connecté → BDD
        if ($user) {
            $cartService->mergeSessionCartToUser($session);

            $cartItems = $cartService->getItems();
            $cartTotal = $cartService->getTotal();
        } 
        // 2️⃣ Panier pour visiteur → Session
        else {
            $sessionCart = $session->get('cart', []);

            if (empty($sessionCart)) {
                return $this->redirectToRoute('app_frontend_shop');
            }

            $cartItems = [];
            $cartTotal = 0;

            // ✅ On préserve la clé du panier en session
            foreach ($sessionCart as $key => $item) {
                $productEntity = $productRepository->find($item['product']['id']);
                if (!$productEntity) continue;

                $quantity = $item['quantity'] ?? 1;
                $linePrice = $productEntity->getPrice() * $quantity;
                $cartTotal += $linePrice;

                $cartItems[$key] = [
                    'id' => $item['product']['id'],
                    'product' => $productEntity,
                    'quantity' => $quantity,
                    'color' => $item['color'] ?? null,
                    'size' => $item['size'] ?? null,
                    'image' => $item['image'] ?? null,
                    'line_price' => $linePrice,
                ];
            }
        }

        $form = $this->createForm(CheckoutType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $user) {
            $order = $cartService->createOrderFromSession($session, $form->getData());
            if ($order) {
                return $this->redirectToRoute('order_success');
            }
        }

        return $this->render('cart/cart.html.twig', [
            'cartItems' => $cartItems,
            'cartTotal' => $cartTotal,
            'form' => $form->createView()
        ]);
    }

    #[Route('/add-ajax/{id}', name: 'cart_add_ajax', methods: ['POST'])]
    public function addAjax(int $id, ProductRepository $productRepo, Request $request, SessionInterface $session): JsonResponse
    {
        $product = $productRepo->find($id);
        if (!$product) return new JsonResponse(['success' => false, 'message' => 'Produit introuvable'], 404);

        $data = json_decode($request->getContent(), true);
        $quantity = $data['quantity'] ?? 1;
        $color = $data['color'] ?? null;
        $size = $data['size'] ?? null;
        $image = $data['image'] ?? null;

        if ($this->getUser()) {
            $this->cartService->addProduct($product, $quantity, $color, $size, $image);
            return new JsonResponse([
                'success' => true,
                'html' => $this->renderView('cart/mini.html.twig', [
                    'cart' => $this->cartService->getItems()
                ])
            ]);
        }

        $cart = $session->get('cart', []);
        $cartKey = $id . '-' . ($color ?? 'nocolor') . '-' . ($size ?? 'nosize');

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
        if ($this->getUser()) {
            $items = $this->cartService->getItems();
            if (isset($items[$key])) {
                $item = $items[$key];
                $this->cartService->removeProduct($item['product'], $item['color'], $item['size']);
            }
            return new JsonResponse([
                'success' => true,
                'html' => $this->renderView('cart/mini.html.twig', ['cart' => $this->cartService->getItems()])
            ]);
        }

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

    #[Route('/update-ajax/{id}', name: 'cart_update_ajax', methods: ['POST'])]
    public function updateAjax(string $id, Request $request, SessionInterface $session, ProductRepository $productRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $quantity = max(1, (int) ($data['quantity'] ?? 1));
        $color = $data['color'] ?? null;
        $size = $data['size'] ?? null;

        // Utilisateur connecté → BDD
        if ($this->getUser()) {
            preg_match('/^\d+/', $id, $matches);
            $realId = $matches[0] ?? null;
            if (!$realId) return new JsonResponse(['success' => false, 'message' => 'ID invalide'], 400);

            $cartItem = $this->cartService->findCartItemById((int)$realId);
            if (!$cartItem) return new JsonResponse(['success' => false, 'message' => 'Item introuvable'], 404);

            $cartItem->setQuantity($quantity);
            $cartItem->setColor($color);
            $cartItem->setSize($size);
            $this->cartService->flush();

            $lineTotal = $cartItem->getQuantity() * $cartItem->getPrice();
            $total = $this->cartService->getTotal();

            return new JsonResponse([
                'success' => true,
                'lineTotal' => $lineTotal,
                'total' => $total,
                'html' => $this->renderView('cart/mini.html.twig', ['cart' => $this->cartService->getItems()])
            ]);
        }

        // Visiteur → Session
        $sessionCart = $session->get('cart', []);
        if (empty($sessionCart) || !isset($sessionCart[$id])) {
            return new JsonResponse(['success' => false, 'message' => 'Item introuvable'], 404);
        }

        $sessionCart[$id]['quantity'] = $quantity;
        $sessionCart[$id]['color'] = $color;
        $sessionCart[$id]['size'] = $size;
        $session->set('cart', $sessionCart);

        $lineTotal = ($productRepository->find($sessionCart[$id]['product']['id'])->getPrice() ?? 0) * $quantity;
        $cartItems = [];
        $cartTotal = 0;

        foreach ($sessionCart as $key => $item) {
            $productEntity = $productRepository->find($item['product']['id']);
            if (!$productEntity) continue;

            $quantityItem = $item['quantity'] ?? 1;
            $linePrice = $productEntity->getPrice() * $quantityItem;
            $cartTotal += $linePrice;

            $cartItems[$key] = [
                'id' => $key,
                'product' => $productEntity,
                'quantity' => $quantityItem,
                'color' => $item['color'] ?? null,
                'size' => $item['size'] ?? null,
                'image' => $item['image'] ?? null,
                'line_price' => $linePrice,
            ];
        }

        return new JsonResponse([
            'success' => true,
            'lineTotal' => $lineTotal,
            'total' => $cartTotal,
            'html' => $this->renderView('cart/mini.html.twig', ['cart' => $cartItems])
        ]);
    }
}