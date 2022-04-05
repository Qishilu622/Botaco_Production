<?php

namespace App\Controller\Stripee;

use App\Entity\Order;
use App\Services\CartServices;
use App\Services\StockManagerServices;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StripeSuccessPaymentController extends AbstractController
{
    /**
     * @Route("/stripe-payment-success/{StripeCheckoutSessionId}", name="app_stripe_payment_success")
     */
    public function index(?Order $order, CartServices $cartServices, EntityManagerInterface $manager, StockManagerServices $stockManager): Response
    {
        if(!$order || $order->getUser() !== $this->getUser()){
            return $this->redirectToRoute("app_home");
        }
        if(!$order->getIsPaid()){
            
            $order->setIsPaid(true);
            $stockManager->Stock($order);
            $manager->flush();
            $cartServices->deleteCart();
        }

        return $this->render('stripe/stripe_success_payment/index.html.twig', [
            'controller_name' => 'StripeSuccessPaymentController',
            'order' => $order
        ]);
    }
}
