<?php

namespace App\Controller;

use App\Service\CartService;
use App\Form\CheckoutType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/checkout')]
class CheckoutController extends AbstractController
{
    #[Route('', name: 'checkout')]
    public function index(Request $request, CartService $cartService): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        // Panier : si connecté -> DB, sinon session
        $session = $request->getSession();
        $cart = $session->get('cart', []);

        // Si utilisateur connecté, on peut aussi charger DB (optionnel)
        if ($this->getUser()) {
            $cart = $cartService->getItems();
        }

        if (empty($cart)) {
            return $this->redirectToRoute('app_frontend_shop');
        }

        $form = $this->createForm(CheckoutType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $order = $cartService->createOrderFromSession($session, $form->getData());
            
        
            if ($order) {
                return $this->redirectToRoute('order_success');
            }
        }

        return $this->render('checkout/index.html.twig', [
            'cart' => $cart,
            'form' => $form->createView()
        ]);
    }

    #[Route('/success', name: 'order_success')]
    public function success(): Response
    {
        return $this->render('checkout/success.html.twig');
    }
}