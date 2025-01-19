<?php

namespace App\Controller;

use App\Entity\BankAccount;
use App\Enum\BankAccountStatus;
use App\Repository\BankAccountRepository;
use App\Repository\TransactionRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class AdminManageUsersController extends AbstractController
{
    #[Route('/admin/users', name: 'admin_users_list')]
    public function listUsers(UserRepository $userRepository): JsonResponse
    {
        $users = $userRepository->findAll();

        $userData = [];
        foreach ($users as $user) {
            $userData[] = [
                'id' => $user->getId(),
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastName(),
                'email' => $user->getEmail(),
            ];
        }

        return new JsonResponse($userData);
    }

    #[Route('/admin/user/{id}/accounts', name: 'admin_user_accounts')]
public function showUserAccounts(int $id, BankAccountRepository $bankAccountRepository): JsonResponse
{
    $bankAccounts = $bankAccountRepository->findBy(['owner' => $id]);

    if (!$bankAccounts) {
        return $this->json(['error' => 'Aucun compte bancaire trouvé pour cet utilisateur'], 404);
    }

    $accountsData = [];
    foreach ($bankAccounts as $account) {
        $accountsData[] = [
            'number' => $account->getAccountNumber(),
            'type' => $account->getType()->value,
            'balance' => $account->getBalance(),
        ];
    }

    return new JsonResponse($accountsData);
}


#[Route('/admin/user/{id}/account/{accountId}/transactions', name: 'account_transactions')]
public function showAccountTransactions(int $accountId, TransactionRepository $transactionRepository): JsonResponse
{
    $transactions = $transactionRepository->createQueryBuilder('t')
        ->where('t.source_account = :accountId')
        ->orWhere('t.destination_account = :accountId')
        ->setParameter('accountId', $accountId)
        ->getQuery()
        ->getResult();

    if (!$transactions) {
        return $this->json(['error' => 'No transactions found for this account'], 404);
    }

    $transactionsData = [];
    foreach ($transactions as $transaction) {
        $transactionsData[] = [
            'id' => $transaction->getId(),
            'type' => $transaction->getType()->value,
            'amount' => $transaction->getAmount(),
            'status' => $transaction->getStatus()->value,
            'date_time' => $transaction->getDateTime()->format('Y-m-d H:i:s')
        ];
    }

    return new JsonResponse($transactionsData);
}

#[Route('/admin/account/{id}/toggle-status', name: 'toggle_bank_account_status')]
public function toggleStatus(int $id, BankAccountRepository $bankAccountRepository, EntityManagerInterface $entityManager)
{
    $bankAccount = $bankAccountRepository->find($id);
    
    if (!$bankAccount) {
        throw $this->createNotFoundException('Account not found.');
    }

    $bankAccount->setStatus(
        $bankAccount->getStatus() === BankAccountStatus::ACTIVE 
        ? BankAccountStatus::CLOSE 
        : BankAccountStatus::ACTIVE
    );

    $entityManager->flush();

    return $this->redirectToRoute('app_user');
}

}




