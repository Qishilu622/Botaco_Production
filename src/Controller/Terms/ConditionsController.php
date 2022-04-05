<?php

namespace App\Controller\Terms;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

    /**
     * @Route("/conditions")
     */
class ConditionsController extends AbstractController
{
    /**
     * @Route("/termsofuse", name="app_conditions")
     */
    public function index(): Response
    {
        return $this->render('conditions/index.html.twig', [
            'controller_name' => 'ConditionsController',
        ]);
    }
}
