<?php

namespace App\Service;

use App\Entity\BankAccount;
use App\Entity\Transaction;
use App\Enum\TransactionStatus;
use App\Enum\TransactionType;
use Doctrine\ORM\EntityManagerInterface;

class TransactionService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function processTransaction(
        float $amount,
        BankAccount $sourceAccount,
        BankAccount $destinationAccount,
        TransactionType $transactionType
    ) {
        $transaction = new Transaction();
        $transaction->setAmount($amount);
        $transaction->setDateTime(new \DateTime());
        $transaction->setType($transactionType);
        $transaction->setStatus(TransactionStatus::SUCCESSED);
        $transaction->setSourceAccount($sourceAccount);
        $transaction->setDestinationAccount($destinationAccount);

        if ($transactionType === TransactionType::TRANSFER || $transactionType === TransactionType::WITHDRAWAL) {
            $sourceAccount->setBalance($sourceAccount->getBalance() - $amount);
        }

        if ($transactionType === TransactionType::TRANSFER || $transactionType === TransactionType::DEPOSIT) {
            $destinationAccount->setBalance($destinationAccount->getBalance() + $amount);
        }

        $this->entityManager->persist($transaction);
        $this->entityManager->persist($sourceAccount);
        $this->entityManager->persist($destinationAccount);
        $this->entityManager->flush();
    }
    public function createFailedTransaction(
        float $amount, 
        BankAccount $sourceAccount, 
        BankAccount $destinationAccount, 
        TransactionType $transactionType,
        EntityManagerInterface $entityManager
    ) {
        $transaction = new Transaction();
    
        $transaction->setAmount($amount);
        $transaction->setDateTime(new \DateTime());
        $transaction->setType($transactionType);
        $transaction->setStatus(TransactionStatus::FAILED);
        $transaction->setSourceAccount($sourceAccount);
        $transaction->setDestinationAccount($destinationAccount);
    
        $entityManager->persist($transaction);
        $entityManager->flush(); 
    
    }
    

}
