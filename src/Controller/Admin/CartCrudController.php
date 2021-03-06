<?php

namespace App\Controller\Admin;

use App\Entity\Cart;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class CartCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Cart::class;
    }
    public function configureCrud(Crud $crud): Crud
    {
        return $crud->setDefaultSort(['id'=>'DESC']);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('user.FullName','Client'),
            TextField::new('carrierName','Carrier Name'),
            MoneyField::new('CarrierPrice', 'Shipping')->setCurrency('CZK'),
            MoneyField::new('subTotal', 'Without Tax')->setCurrency('CZK'),
            MoneyField::new('dph', 'Tax')->setCurrency('CZK'),
            MoneyField::new('Total', 'Total')->setCurrency('CZK'),
            BooleanField::new('isPaid')
        ];
    }

}
