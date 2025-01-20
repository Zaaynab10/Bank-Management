<?php

namespace App\Controller;

use App\Enum\BankAccountStatus;
use App\Repository\BankAccountRepository;
use App\Repository\TransactionRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminManageUsersController extends AbstractController
{
    #[Route('/admin/users', name: 'admin_users_list')]
    public function listUsers(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();
        return $this->render('admin/users.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/admin/user/{id}/accounts', name: 'admin_user_accounts')]
    public function showUserAccounts(int $id, BankAccountRepository $bankAccountRepository): Response
    {
        $bankAccounts = $bankAccountRepository->findBy(['owner' => $id]);
        return $this->render('admin/userAccounts.html.twig', [
            'bankAccounts' => $bankAccounts,
            'id' => $id,  
        ]);
    }

    #[Route('/admin/user/{id}/account/{accountId}/transactions', name: 'account_transactions')]
public function showAccountTransactions(int $accountId, TransactionRepository $transactionRepository)
{
    $transactions = $transactionRepository->findBy(
        [
            'source_account' => $accountId
        ],
        ['date_time' => 'DESC']
    );

    $destinationTransactions = $transactionRepository->findBy(
        [
            'destination_account' => $accountId
        ],
        ['date_time' => 'DESC']
    );

    $allTransactions = array_merge($transactions, $destinationTransactions);

    return $this->render('admin/userAccountTransactions.html.twig', [
        'transactions' => $allTransactions,
        'accountId' => $accountId,
    ]);
}

    #[Route('/admin/account/{id}/toggle-status', name: 'toggle_bank_account_status')]
    public function toggleStatus(int $id, BankAccountRepository $bankAccountRepository, EntityManagerInterface $entityManager): Response
    {
        $bankAccount = $bankAccountRepository->find($id);
        
        if (!$bankAccount) {
            throw $this->createNotFoundException("Bank account not found.");
        }

        if ($bankAccount->getStatus() === BankAccountStatus::ACTIVE) {
            $bankAccount->setStatus(BankAccountStatus::CLOSE);
        } else {
            $bankAccount->setStatus(BankAccountStatus::ACTIVE);
        }

        $entityManager->flush();

        return $this->redirectToRoute('admin_users_list');
    }
}
