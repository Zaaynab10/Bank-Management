<?php

namespace App\Controller;

use App\Entity\BankAccount;
use App\Entity\Transaction;
use App\Enum\TransactionStatus;
use App\Enum\TransactionType;
use App\Form\DepositType;
use App\Form\TransferType;
use App\Form\WithdrawType;
use App\Repository\BankAccountRepository;
use App\Repository\TransactionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class TransactionsController extends AbstractController
{   
    
    private function createFailedTransaction(
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
    
    


    
    #[Route('/transaction/deposit', name: 'app_deposit')]
    public function MakeDeposit(
        Request $request, 
        EntityManagerInterface $entityManager, 
      BankAccountRepository $bankAccountRepository

    ): Response
    {
        $user = $this->getUser();

        $transaction = new Transaction();

        $form = $this->createForm(DepositType::class, $transaction, [
            'user' => $user, 
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $bankAccountIndex =(int) $form->get('bankAccount')->getData();
            $amount = $form->get('amount')->getData();
            $bankAccounts = $bankAccountRepository->findBy([], ['id' => 'ASC']);
           $bankAccount=$bankAccounts[$bankAccountIndex];



            if (!$bankAccount || $bankAccount->getOwner() !== $user) {
                $this->createFailedTransaction($amount, $bankAccount, $bankAccount, TransactionType::DEPOSIT , $entityManager);

                throw $this->createAccessDeniedException('You do not own this account.');
            }

          
            if (!$bankAccount->canDeposit($amount)) {
                throw $this->createAccessDeniedException('Deposit denied, the deposit limit is 25,000.');
            }
         

            $transaction->setAmount($amount);
            $transaction->setType(TransactionType::DEPOSIT); 
            $transaction->setStatus(TransactionStatus::SUCCESSED);
            $transaction->setDestinationAccount($bankAccount);
            $transaction->setSourceAccount($bankAccount);
            $transaction->setDateTime(new \DateTime());

            $newBankAccount = $bankAccount->getBalance() + $amount ;
            $bankAccount->setBalance($newBankAccount);

        
            $entityManager->persist($bankAccount);
            $entityManager->persist($transaction);
            $entityManager->flush();

            return $this->redirectToRoute('app_user');
        }

        return $this->render('transactions/deposit.html.twig', [
            'form' => $form->createView(),
        ]);
    }



    
   
    #[Route('/transaction/withdraw', name: 'app_withdraw')]
    public function MakeWithdrawal(
        Request $request, 
        EntityManagerInterface $entityManager, 
      BankAccountRepository $bankAccountRepository

    ): Response
    {
        $user = $this->getUser();

        $transaction = new Transaction();

        $form = $this->createForm(WithdrawType::class, $transaction, [
            'user' => $user, 
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $bankAccountIndex =(int) $form->get('bankAccount')->getData();
            $amount = $form->get('amount')->getData();
            $bankAccounts = $bankAccountRepository->findBy([], ['id' => 'ASC']);
           $bankAccount=$bankAccounts[$bankAccountIndex];


            if (!$bankAccount || $bankAccount->getOwner() !== $user) {
                throw $this->createAccessDeniedException('You do not own this account.');
            }

            
            if (!$bankAccount->canWithdraw($amount)) {
                $this->createFailedTransaction($amount, $bankAccount, $bankAccount, TransactionType::WITHDRAWAL, $entityManager);

                throw $this->createAccessDeniedException('Insufficient funds.');
            }
            
            $transaction->setAmount($amount);
            $transaction->setType(TransactionType::WITHDRAWAL); 
            $transaction->setStatus(TransactionStatus::SUCCESSED);
            $transaction->setDestinationAccount($bankAccount);
            $transaction->setSourceAccount($bankAccount);
            $transaction->setDateTime(new \DateTime());

            $newBankAccount = $bankAccount->getBalance() - $amount ;
            $bankAccount->setBalance($newBankAccount);


            $entityManager->persist($bankAccount);
            $entityManager->persist($transaction);
            $entityManager->flush();

            return $this->redirectToRoute('app_user');
        }

        return $this->render('transactions/withdraw.html.twig', [
            'form' => $form->createView(),
        ]);
    }



    #[Route('/transactions/transfer', name: 'app_transfer')]
    public function MakeTransfer(Request $request,
                             BankAccountRepository $bankAccountRepository,
                             EntityManagerInterface $entityManager,
                            ): Response
    {
        $user = $this->getUser();
        
        $bankAccounts = $bankAccountRepository->findBy(['owner' => $user]);
    
        $transaction = new Transaction();
    
        $form = $this->createForm(TransferType::class, $transaction, [
            'user' => $user,
            'bank_accounts' => $bankAccounts,
        ]);
    
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $sourceAccount = $form->get('source_account')->getData();
            $destinationAccountNumber = $form->get('destination_account_number')->getData();
            $amount = $form->get('amount')->getData();
    
            if ($sourceAccount === null) {

                throw $this->createNotFoundException('Source account not found.');
            }
    
            $destinationAccount = $bankAccountRepository->findOneBy(['account_number' => $destinationAccountNumber]);
    
            if ($destinationAccount === null) {

                throw $this->createNotFoundException('Destination account not found.');
            }
    
            if ($sourceAccount->getOwner() !== $user) {

                throw $this->createAccessDeniedException('You do not own the source account.');

            }
    
            if (!$destinationAccount->canDeposit($amount)) {
                $this->createFailedTransaction($amount, $sourceAccount, $destinationAccount, TransactionType::TRANSFER , $entityManager);

                throw $this->createAccessDeniedException('Transfer denied: the savings account has exceeded its deposit limit of 25,000.');

                $transaction->setStatus(TransactionStatus::FAILED);

            }
           
            
    
            $transaction->setAmount($amount);
            $transaction->setDateTime(new \DateTime());
            $transaction->setType(TransactionType::TRANSFER);
            $transaction->setStatus(TransactionStatus::SUCCESSED);
            $transaction->setSourceAccount($sourceAccount);
            $transaction->setDestinationAccount($destinationAccount);
    
            $newSourceAccount = $sourceAccount->getBalance() - $amount ;
            $sourceAccount->setBalance($newSourceAccount);

            $newDestinationAccount = $destinationAccount->getBalance() + $amount ;
            $destinationAccount->setBalance($newDestinationAccount);

            $entityManager->persist($transaction);
            $entityManager->persist($destinationAccount);
            $entityManager->persist($sourceAccount);


            $entityManager->flush();
    
            return $this->json(['status' => 'ok']);
        }
    
        return $this->render('transactions/transfer.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    

    #[Route('/transactions/{accountId}', name: 'app_transactions')]
    public function DisplayTransactions(int $accountId, TransactionRepository $transactionRepository, BankAccountRepository $bankAccountRepository): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            throw $this->createAccessDeniedException("You must be logged in to view your transactions.");
        }
    
        $bankAccount = $bankAccountRepository->findOneBy(['id' => $accountId, 'owner' => $user]);
    
        if (!$bankAccount) {
            throw $this->createNotFoundException("Bank account not found or not authorized.");
        }
    
        $transactionsSource = $transactionRepository->findBy([
            'source_account' => $accountId
        ]);
    
        $transactionsDestination = $transactionRepository->findBy([
            'destination_account' => $accountId
        ]);
    
        $transactions = array_merge($transactionsSource, $transactionsDestination);
    
    
        return $this->render('transactions/index.html.twig', [
            'transactions' => $transactions,
            'bankAccount' => $bankAccount,
        ]);
    }
    

    

}
