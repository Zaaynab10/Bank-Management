<?php

namespace App\Controller;

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

final class TransactionsController extends AbstractController{
    #[Route('/transactions/{bankAccountId}', name: 'app_transactions')]
    public function index(TransactionRepository $transactionRepository, int $bankAccountId): Response
    {
        $transactions = $transactionRepository->findBy(['sourceAccount' => $bankAccountId, 'destinationAccount' => $bankAccountId]);

        return $this->render('transactions/index.html.twig', [
            'controller_name' => 'TransactionsController',
        ]);
    }

    #[Route('/transactions/{bankAccountId}/deposit/{amount}', name: 'app_deposit')]
    public function deposit(BankAccountRepository $bankAccountRepository,
                            EntityManagerInterface $entityManager,
                            int $bankAccountId,
                            int $amount): Response
    {
        $bankAccount = $bankAccountRepository->find($bankAccountId);

        if ($bankAccount === null) {
            throw $this->createNotFoundException();
        }

        if ($bankAccount->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $transaction = new Transaction();
        $transaction->setAmount($amount);
        $transaction->setDateTime(new \DateTime());
        $transaction->setType(TransactionType::DEPOSIT);
        $transaction->setStatus(TransactionStatus::PENDING);
        $transaction->setDestinationAccount($bankAccount);

        $entityManager->persist($transaction);
        $entityManager->flush();

        return $this->json(['status' => 'ok']);
    }

    #[Route('/transactions/{bankAccountId}/withdraw/{amount}', name: 'app_withdraw')]
    public function withdraw(BankAccountRepository $bankAccountRepository,
                             EntityManagerInterface $entityManager,
                             TransactionRepository $transactionRepository,
                             int $bankAccountId,
                             int $amount): Response
    {
        $bankAccount = $bankAccountRepository->find($bankAccountId);

        if ($bankAccount === null) {
            throw $this->createNotFoundException();
        }

        if ($bankAccount->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $balance = $transactionRepository->getBalanceByAccount($bankAccountId);

        if ($balance < $amount || $bankAccount->getType() == 'current' && $balance - $amount < -400) {
            throw $this->createAccessDeniedException('Insufficient funds');
        }

        $transaction = new Transaction();
        $transaction->setAmount($amount);
        $transaction->setDateTime(new \DateTime());
        $transaction->setType(TransactionType::WITHDRAWAL);
        $transaction->setStatus(TransactionStatus::PENDING);
        $transaction->setSourceAccount($bankAccount);

        $entityManager->persist($transaction);
        $entityManager->flush();

        return $this->json(['status' => 'ok']);
    }

    #[Route('/transactions/{sourceAccountId}/transfer/{destinationAccountId}/{amount}', name: 'app_transfer')]
    public function transfer(BankAccountRepository $bankAccountRepository,
                             EntityManagerInterface $entityManager,
                             TransactionRepository $transactionRepository,
                             int $sourceAccountId,
                             int $destinationAccountId,
                             int $amount): Response
    {

        if ($sourceAccountId === $destinationAccountId) {
            throw $this->createAccessDeniedException('Cannot transfer to the same account');
        }

        $sourceAccount = $bankAccountRepository->find($sourceAccountId);
        $destinationAccount = $bankAccountRepository->find($destinationAccountId);

        if ($sourceAccount === null || $destinationAccount === null) {
            throw $this->createNotFoundException();
        }

        if ($sourceAccount->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $balance = $transactionRepository->getBalanceByAccount($sourceAccountId);

        if ($sourceAccount->getType() == BankAccountType::CURRENT) {
            if ($balance - $amount < -400) {
                throw $this->createAccessDeniedException('Insufficient funds');
            }
        } else {
            if ($balance < $amount) {
                throw $this->createAccessDeniedException('Insufficient funds');
            }
        }

        $transaction = new Transaction();
        $transaction->setAmount($amount);
        $transaction->setDateTime(new \DateTime());
        $transaction->setType(TransactionType::TRANSFER);
        $transaction->setStatus(TransactionStatus::PENDING);
        $transaction->setSourceAccount($sourceAccount);
        $transaction->setDestinationAccount($destinationAccount);

        $entityManager->persist($transaction);
        $entityManager->flush();

        return $this->json(['status' => 'ok']);
    }
}
