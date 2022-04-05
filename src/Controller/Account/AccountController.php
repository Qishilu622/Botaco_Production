<?php

namespace App\Controller\Account;

use App\Entity\Order;
use App\Repository\OrderRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


    /**
     * @Route("/account")
     */
class AccountController extends AbstractController
{
    /**
     * @Route("/", name="app_account")
     */
    public function index(OrderRepository $repoOrder): Response
    {
        $orders = $repoOrder->findBy(['isPaid' => true, 'user'=>$this->getUser()],['id' => 'DESC']);
        return $this->render('account/index.html.twig',[
            'orders' => $orders,
        ]);
    }
        /**
     * @Route("/order/{id}", name="app_account_order_details")
     */
    public function show(?Order $order): Response
    {
       if(!$order || $order->getUser() !== $this->getUser()|| !$order->getIsPaid()){
        return $this->redirectToRoute('app_home');
       }

       if(!$order->getIsPaid()){
        return $this->redirectToRoute('app_account');   
       }

       return $this->render('account/detail_order.html.twig',[
        'order' => $order,
    ]);
    }
}
