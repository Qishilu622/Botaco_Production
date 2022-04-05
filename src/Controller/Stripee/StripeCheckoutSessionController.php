<?php

namespace App\Controller\Stripee;

use App\Entity\Cart;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use App\Services\CartServices;
use App\Services\OrderServices;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class StripeCheckoutSessionController extends AbstractController
{
    /**
     * @Route("/create-checkout-session/{reference}", name="app_create_checkout_session")
     */
    public function index(?Cart $cart, OrderServices $orderServices, EntityManagerInterface $manager): Response
    {
      $user = $this->getUser();
        if(!$cart){
          return $this->redirectToRoute('app_home');
        }


        $order = $orderServices->createOrder($cart);
        Stripe::setApiKey('sk_test_51KkpHjA6Z3ZEOoHRTcr8pJRWK7AejGbclFsgjianLBfxSVbfxrQdgw7IyPGTgF2kT5uoVjoOJzZ7RJlmyBX4Zvnn00slf4ORm7');

       
        $checkout_session = Session::create([
          'customer_email' => $user->getEmail(),
          'payment_method_types' =>['card'],
          'line_items' => $orderServices->getLineItems($cart),
          'mode' => 'payment',
          'success_url' => $_ENV['YOUR_DOMAIN'] . '/stripe-payment-success/{CHECKOUT_SESSION_ID}',
          'cancel_url' =>  $_ENV['YOUR_DOMAIN'] . '/stripe-payment-cancel/{CHECKOUT_SESSION_ID}',
        ]);
    
         $order->setStripeCheckoutSessionId($checkout_session->id);
         $manager->flush();
         return $this->json(['id' => $checkout_session->id]);
    }
}