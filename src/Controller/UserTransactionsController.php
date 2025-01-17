<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class UserTransactionsController extends AbstractController{
    #[Route('/user/transactions', name: 'app_user_transactions')]
    public function index(): Response
    {
        return $this->render('user_transactions/index.html.twig', [
            'controller_name' => 'UserTransactionsController',
        ]);
    }
}
