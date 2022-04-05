<?php
namespace App\Services;

use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CartServices{
    private $session;
    private $repoProduct;
    private $dph = 0.21;

    public function __construct(SessionInterface $session, ProductRepository $repoProduct)
    {
        $this->session = $session;
        $this->repoProduct = $repoProduct;
    }
    public function addToCart($id)
    {
        $cart = $this->getCart();
        if(isset($cart[$id])){
            $cart[$id]++;
        }else{
            $cart[$id] = 1;
        }
        $this->updateCart($cart);
    }
    public function deleteFromCart($id)
    {
        $cart = $this->getCart();
        if(isset($cart[$id])){
            if($cart[$id] > 1){
                $cart[$id]--;
            }else{
                unset($cart[$id]);
            }
            $this->updateCart($cart);
        }
    }
    public function deleteAllToCart($id)
    {
        $cart = $this->getCart();
        if(isset($cart[$id])){
            unset($cart[$id]);
            $this->updateCart($cart);
        }
    }
    public function deleteCart()
    {
        $this->updateCart([]);
    }
    public function updateCart($cart)
    {
        $this->session->set('cart', $cart);
        $this->session->set('cartData', $this->getFullCart());

    }
    public function getCart()
    {
        return $this->session->get('cart',[]);
    }

    public function getFullCart(){
        $cart = $this->getCart();

        $fullCart = [];
        $Quantity_cart = 0;
        $subTotal = 0;

        foreach($cart as $id => $Quantity){
            $product = $this->repoProduct->find($id);

            if($product){

                if($Quantity > $product->getQuantity())
                {
                    $Quantity = $product->getQuantity();
                    $cart[$id] = $Quantity;
                    $this->updateCart($cart);
                }
                
                $fullCart['products'][] =
                 [
                    "Quantity" => $Quantity,
                    "product" => $product
                ];
                $Quantity_cart += $Quantity;
                $subTotal += $Quantity * $product->getPrice()/100;
            }else{
                $this->deleteFromCart($id);
            }
        }
        $fullCart['data'] = [
            "Quantity_cart" => $Quantity_cart,
            "subTotal" => $subTotal,
            "dph" => round($subTotal*$this->dph,2),
            "Total" => round(($subTotal + ($subTotal*$this->dph)),2),
        ];

        return $fullCart;

    }
}