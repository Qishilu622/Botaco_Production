<?php
namespace App\Services;

use App\Entity\Cart;
use App\Entity\CartDetails;
use App\Entity\Order;
use App\Entity\OrderDetails;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Orx;
use PhpParser\Node\Expr\Cast\Double;

class OrderServices{
    private $manager;
    private $repoProduct;

    public function __construct( EntityManagerInterface $manager, ProductRepository $repoProduct)
    {
      $this->manager = $manager;   
      $this->repoProduct = $repoProduct;
    }

    public function createOrder($cart)
    {
        
        $order = new Order();
        $order->setReference($cart->getReference())
        ->setCarrierName($cart->getCarrierName())
        ->setCarrierPrice($cart->getCarrierPrice()/100)
        ->setFullName($cart->getFullName())
        ->setDeliveryAddress($cart->getDeliveryAddress())
        ->setMoreInformations($cart->getMoreInformations())
        ->setQuantity($cart->getQuantity())
        ->setSubTotal($cart->getSubTotal()/100)
        ->setDph($cart->getDph()/100)
        ->setTotal($cart->getTotal()/100)
        ->setUser($cart->getUser())
        ->setCreatedAt($cart->getCreatedAt());
        $this->manager->persist($order);

        $products = $cart->getCartDetails()->getValues();
        foreach ($products as $cart_product){
            $orderDetails = new OrderDetails();
            $orderDetails->setOrders($order)
            ->setProductName($cart_product->getProductName())
            ->setProductPrice($cart_product->getProductPrice())
            ->setQuantity($cart_product->getQuantity())
            ->setSubTotal($cart_product->getSubTotal())
            ->setDph($cart_product->getDph())
            ->setTotal($cart_product->getTotal());
            $this->manager->persist($orderDetails);

        }
        $this->manager->flush();
        return $order;
    }

    public function getLineItems($cart){
        $cartDetails = $cart->getCartDetails();
        $line_items = [];
        foreach($cartDetails as $details){
          $product = $this->repoProduct->findOneByName($details->getProductName());
          $line_items[] = [
                  'price_data' => [
                      'currency' => 'CZK',
                      'unit_amount' => $product->getPrice(),
                      'product_data' => [
                          'name' => $product->getName(),
                          'images' => [$_ENV['YOUR_DOMAIN'].'/uploads/products/'.$product->getImage()],
                      ],
                  ],
                  'quantity' => $details->getQuantity(),
              ];
        }
                
        //carrier
        $line_items[] = [
            'price_data' => [
                'currency' => 'CZK',
                'unit_amount' => $cart->getCarrierPrice(),
                'product_data' => [
                    'name' => 'Carrier ( '.$cart->getCarrierName().')',
                    'images' => [$_ENV['YOUR_DOMAIN'].'/uploads/products/'],
                ],
            ],
            'quantity' => 1,
        ];
        //dph
        $line_items[] = [
            'price_data' => [
                'currency' => 'CZK',
                'unit_amount' => $cart->getDph(),
                'product_data' => [
                    'name' => 'DPH 21%',
                    'images' => [$_ENV['YOUR_DOMAIN'].'/uploads/products/'],
                ],
            ],
            'quantity' => 1,
        ];
        return $line_items;

    }

    public function saveCart($data, $user)
    {
        $cart = new Cart();
        $reference = $this->generateUuid();
        $address = $data['checkout']['address'];
        $carrier = $data['checkout']['carrier'];
        $informations = $data['checkout']['informations'];
        $cart->setReference($reference)
        ->setCarrierName($carrier->getName())
        ->setCarrierPrice($carrier->getPrice()/100)
        ->setFullName($address->getFullName())
        ->setDeliveryAddress($address)
        ->setMoreInformations($informations)
        ->setQuantity($data['data']['Quantity_cart'])
        ->setSubTotal(($data['data']['subTotal']))
        ->setDph($data['data']['dph'],2)
        ->setTotal(round(($data['data']['subTotal']+$carrier->getPrice()/100)),2)
        ->setUser($user)
        ->setCreatedAt(new \DateTime());
        $this->manager->persist($cart);

        $cart_details_array = [];

        foreach($data['products'] as $products){
            $cartDetails = new CartDetails();

            $subTotal = $products['Quantity'] * $products['product']->getPrice()/100;

            $cartDetails->setCarts($cart)
                        ->setProductName($products['product']->getName())
                        ->setProductPrice($products['product']->getPrice()/100)
                        ->setQuantity($products['Quantity'])
                        ->setSubTotal($subTotal)
                        ->setTotal($subTotal*1.21)
                        ->setDph($subTotal*0.21);
            $this->manager->persist($cartDetails);
            $cart_details_array[] = $cartDetails;
        }


        $this->manager->flush();

        return $reference;


    }

    public function generateUuid(){

        mt_srand((Double)microtime()*100000);

        $charid = strtoupper(md5(uniqid(rand(), true)));

        $hyphen = chr(45);

        $uuid = ""
        .substr($charid, 0, 8).$hyphen
        .substr($charid, 8, 4).$hyphen
        .substr($charid, 12, 4).$hyphen
        .substr($charid, 16, 4).$hyphen
        .substr($charid, 20, 12);
        
        return $uuid;

    }
}