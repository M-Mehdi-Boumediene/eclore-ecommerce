<?php
namespace App\Service;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Product;
use App\Entity\Order;
use App\Entity\OrderItem;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CartService
{
    public function __construct(
        private EntityManagerInterface $em,
        private Security $security
    ) {}

    // ======================================================
    // CART HELPERS
    // ======================================================

    public function flush(): void
    {
        $this->em->flush();
    }

    public function getOrCreateCart(): ?Cart
    {
        $user = $this->security->getUser();
        if (!$user) {
            return null;
        }

        $cart = $this->em->getRepository(Cart::class)->findOneBy([
            'user' => $user,
            'status' => 'active'
        ]);

        if (!$cart) {
            $cart = new Cart();
            $cart->setUser($user);
            $cart->setStatus('active');
            $cart->setCreatedAt(new \DateTimeImmutable());

            $this->em->persist($cart);
            $this->em->flush();
        }

        return $cart;
    }

    public function findCartItem(Product $product, ?string $color, ?string $size): ?CartItem
    {
        $cart = $this->getOrCreateCart();
        if (!$cart) return null;

        foreach ($cart->getCartItems() as $item) {
            if (
                $item->getProduct()->getId() === $product->getId() &&
                $item->getColor() === $color &&
                $item->getSize() === $size
            ) {
                return $item;
            }
        }

        return null;
    }

    public function getTotal(): float
    {
        $cart = $this->getOrCreateCart();
        if (!$cart) return 0;

        $total = 0;
        foreach ($cart->getCartItems() as $item) {
            $total += $item->getPrice() * $item->getQuantity();
        }

        return $total;
    }

    public function getTotalFromSession(array $cart): float
    {
        $total = 0;
        foreach ($cart as $item) {
            $total += ($item['product']['price'] ?? 0) * $item['quantity'];
        }
        return $total;
    }

    // ======================================================
    // ADD / REMOVE
    // ======================================================

    public function addProduct(Product $product, int $quantity = 1, ?string $color = null, ?string $size = null, ?string $image = null): void
    {
        $cart = $this->getOrCreateCart();
        if (!$cart) return;

        $cartItem = $this->findCartItem($product, $color, $size);

        if ($cartItem) {
            $cartItem->setQuantity($cartItem->getQuantity() + $quantity);
        } else {
            $cartItem = new CartItem();
            $cartItem->setCart($cart);
            $cartItem->setProduct($product);
            $cartItem->setQuantity($quantity);
            $cartItem->setPrice($product->getPrice());
            $cartItem->setColor($color);
            $cartItem->setSize($size);
            $cartItem->setImage($image);

            $this->em->persist($cartItem);
        }

        $this->em->flush();
    }

    public function updateQuantity(Product $product, int $quantity, ?string $color = null, ?string $size = null): void
    {
        $cartItem = $this->findCartItem($product, $color, $size);
        if (!$cartItem) return;

        if ($quantity <= 0) {
            $this->em->remove($cartItem);
        } else {
            $cartItem->setQuantity($quantity);
        }

        $this->em->flush();
    }

    public function removeProduct(Product $product, ?string $color = null, ?string $size = null): void
    {
        $cartItem = $this->findCartItem($product, $color, $size);
        if (!$cartItem) return;

        $this->em->remove($cartItem);
        $this->em->flush();
    }

    // ======================================================
    // ITEMS FOR TWIG
    // ======================================================

    public function getItems(): array
    {
        $cart = $this->getOrCreateCart();
        if (!$cart) return [];

        $this->em->refresh($cart);

        $items = [];
        foreach ($cart->getCartItems() as $item) {
            $items[] = [
                'id'       => $item->getId(),
                'product'  => $item->getProduct(),
                'quantity' => $item->getQuantity(),
                'color'    => $item->getColor(),
                'size'     => $item->getSize(),
                'image'    => $item->getImage()
            ];
        }

        return $items;
    }

    // ======================================================
    // ORDER CONVERSION
    // ======================================================

    public function createOrderFromSession(SessionInterface $session, array $data): ?Order
    {
        $user = $this->security->getUser();
        if (!$user) return null;

        $sessionCart = $session->get('cart', []);
        if (empty($sessionCart)) return null;

        $order = new Order();
        $order->setUser($user);
        $order->setStatus('pending');
        $order->setCreatedAt(new \DateTimeImmutable());
        $order->setFullName($data['fullName'] ?? '');
        $order->setEmail($data['email'] ?? '');
        $order->setAddress($data['address'] ?? '');
        $order->setPhone($data['phone'] ?? '');
        $order->setTotal(0);

        $this->em->persist($order);

        $total = 0;

        foreach ($sessionCart as $item) {
            $product = $this->em->getRepository(Product::class)->find($item['product']['id']);
            if (!$product) continue;

            $orderItem = new OrderItem();
            $orderItem->setOrder($order);
            $orderItem->setProduct($product);
            $orderItem->setQuantity($item['quantity']);
            $orderItem->setPrice($product->getPrice());

            $total += $product->getPrice() * $item['quantity'];

            $this->em->persist($orderItem);
        }

        $order->setTotal($total);

        $this->em->flush();
        $session->remove('cart');

        return $order;
    }
}