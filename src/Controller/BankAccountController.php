<?php

namespace App\Controller;

use App\Entity\BankAccount;
use App\Entity\Transaction;
use App\Enum\BankAccountType;
use App\Enum\TransactionStatus;
use App\Enum\TransactionType;
use App\Repository\BankAccountRepository;
use App\Repository\TransactionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class BankAccountController extends AbstractController{
    #[Route('/bank/account/{id}', name: 'app_bank_account')]
    public function index(int $id, BankAccountRepository $bankAccountRepository,
                          TransactionRepository $transactionRepository): Response
    {
        $bankAccount = $bankAccountRepository->find($id);

        if ($bankAccount === null) {
            throw $this->createNotFoundException();
        }

        if ($bankAccount->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $balance = $transactionRepository->getBalanceByAccount($id);

        return $this->json(['balance' => $balance, 'type' => $bankAccount->getType(), 'id' => $bankAccount->getId()]);
    }

    #[Route('/bank/account/create/{type}/{initialDeposit}', name: 'app_create_bank_account')]
    public function create(EntityManagerInterface $entityManager,
                           BankAccountRepository $bankAccountRepository,
                           BankAccountType $type,
                           int $initialDeposit = 0): Response
    {
        // verify if the user has a less than 5 bank accounts
        $bankAccounts = $bankAccountRepository->findBy(['owner' => $this->getUser()]);

        if (count($bankAccounts) >= 5) {
            throw $this->createAccessDeniedException('You can only have up to 5 bank accounts');
        }

        if ($type === BankAccountType::SAVINGS && $initialDeposit < 10) {
            throw $this->createAccessDeniedException('Initial deposit for savings account must be at least 10');
        }

        $bankAccount = new BankAccount();
        $bankAccount->setOwner($this->getUser());
        $bankAccount->setType($type);

        $entityManager->persist($bankAccount);

        $transaction = new Transaction();
        $transaction->setAmount($initialDeposit);
        $transaction->setDateTime(new \DateTime());
        $transaction->setType(TransactionType::DEPOSIT);
        $transaction->setStatus(TransactionStatus::PENDING);
        $transaction->setDestinationAccount($bankAccount);

        $entityManager->persist($transaction);
        $entityManager->flush();

        return $this->json(['id' => $bankAccount->getId()]);
    }
}
