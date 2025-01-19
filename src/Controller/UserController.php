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
        BankAccountRepository $bankAccountRepository,
        TransactionRepository $transactionRepository
    ): Response {
        $user = $this->getUser(); // Récupération de l'utilisateur connecté

        if (!$user) {
            throw $this->createAccessDeniedException("Vous devez être connecté pour accéder à cette page.");
        }

        // Récupération des comptes bancaires de l'utilisateur
        $bankAccounts = $bankAccountRepository->findBy(['owner' => $user]);

        // Récupération des transactions de l'utilisateur
        $transactions = $transactionRepository->findTransactionsByUserId($user->getId());

        return $this->render('user/index.html.twig', [
            'user' => $user,
            'bankAccounts' => $bankAccounts,
            'transactions' => $transactions,
        ]);
    }
}