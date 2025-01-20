<?php 
namespace App\Service;

use App\Repository\BankAccountRepository;
use App\Repository\TransactionRepository;

class AccountService
{
    private $bankAccountRepository;
    private $transactionRepository;

    public function __construct(BankAccountRepository $bankAccountRepository, TransactionRepository $transactionRepository)
    {
        $this->bankAccountRepository = $bankAccountRepository;
        $this->transactionRepository = $transactionRepository;
    }

    public function getUserAccounts($user)
    {
        return $this->bankAccountRepository->findBy(['owner' => $user]);
    }

    public function getAccountTransactions(int $accountId)
    {
        $transactions = $this->transactionRepository->findBy(
            ['source_account' => $accountId],
            ['date_time' => 'DESC']
        );

        $destinationTransactions = $this->transactionRepository->findBy(
            ['destination_account' => $accountId],
            ['date_time' => 'DESC']
        );

        return array_merge($transactions, $destinationTransactions);
    }
}
