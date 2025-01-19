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
        BankAccount $sourceAccount,
        BankAccount $destinationAccount,
        float $amount,
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
}
