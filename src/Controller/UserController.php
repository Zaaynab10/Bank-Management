<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\BankAccountRepository;
use App\Repository\TransactionRepository;

final class UserController extends AbstractController
{
    #[Route('/user', name: 'customer_dashboard')]
    public function index(
       
    ): Response {
        $user = $this->getUser(); 

        if (!$user) {
            throw $this->createAccessDeniedException("Vous devez être connecté pour accéder à cette page.");
        }

       
        return $this->render('user/index.html.twig', [
            'user' => $user,
        ]);
    }
}