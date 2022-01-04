<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StripeController extends AbstractController
{
    #[Route('/commande/create-session', name: 'stripe_create_session')]
    public function index(EntityManagerInterface $entityManager, Cart $cart,$reference): Response
    {
        $products_for_stripes=[];
        $YOUR_DOMAIN = 'http://127.0.0.1:800';

        $order = $entityManager->getRepository(Order::class)->findOneByReference($reference);

        foreach($order->getOrderDetails()->getValues() as $product){
            $products_for_stripes[]=[
                'price_data' => [
                    'currency' => 'EUR',
                    'product_data' => [
                        'name' => $product['product']->getName(),
                        'images' => [$YOUR_DOMAIN."/uploads/".$product['product']->getIllustration()]
                    ],
                    'unit_amount' => $product['product']->getPrice(),
                ],
                'quantity' => $product['quantity'],
            ];
        }

        Stripe::setApiKey('sk_test_51KAtb8J6RHo8Aj90EjPkENoHbPnXmNS4RN13zdz3FKNNQZVtxbqzx6bVcNUpyXpoU7FaxooXptArMGimuijKPAye00UUtysThS');

        $session = Session::create([
            'line_items' => [
                $products_for_stripes
            ],
            'mode' => 'payment',
            'success_url' => $YOUR_DOMAIN.'/success.html',
            'cancel_url' => $YOUR_DOMAIN.'/cancel.html',
        ]);

        return $this->redirect($session->url,303);
    }
}
