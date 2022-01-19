<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Entity\Order;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StripeController extends AbstractController
{
    #[Route('/commande/create-session/{reference}', name: 'stripe_create_session')]
    public function index(EntityManagerInterface $entityManager, Cart $cart,$reference): Response
    {
        $products_for_stripes=[];
        $YOUR_DOMAIN = 'http://127.0.0.1:8000';

        $order = $entityManager->getRepository(Order::class)->findOneByReference($reference);

        if(!$order){
            return $this->redirectToRoute('order');
        }


        foreach($order->getOrderDetails()->getValues() as $product){
            $product_object = $entityManager->getRepository(Product::class)->findOneByName($product->getProduct());
            $products_for_stripes[]=[
                'price_data' => [
                    'currency' => 'EUR',
                    'product_data' => [
                        'name' => $product->getProduct(),
                        'images' => [$YOUR_DOMAIN."/uploads/".$product_object->getIllustration()]
                    ],
                    'unit_amount' => $product->getPrice(),
                ],
                'quantity' => $product->getQuantity(),
            ];
        }

        $products_for_stripes[]=[
            'price_data' => [
                'currency' => 'EUR',
                'product_data' => [
                    'name' => $order->getCarrierName(),
                    'images' => [$YOUR_DOMAIN]
                ],
                'unit_amount' => $order->getCarrierPrice(),
            ],
            'quantity' => 1
        ];

        Stripe::setApiKey('sk_test_51KAtb8J6RHo8Aj90EjPkENoHbPnXmNS4RN13zdz3FKNNQZVtxbqzx6bVcNUpyXpoU7FaxooXptArMGimuijKPAye00UUtysThS');

        $session = Session::create([
            'customer_email'=>$this->getUser()->getEmail(),
            'line_items' => [
                $products_for_stripes
            ],
            'mode' => 'payment',
            'success_url' => $YOUR_DOMAIN."/commande/merci/{CHECKOUT_SESSION_ID}",
            'cancel_url' => $YOUR_DOMAIN."/commande/erreur/{CHECKOUT_SESSION_ID}",
        ]);

        $order->setStripeSessionId($session->id);
        $entityManager->flush();

        return $this->redirect($session->url,303);
    }
}
