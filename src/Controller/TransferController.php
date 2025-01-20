<?php

namespace App\Controller;

use App\Entity\Transaction;
use App\Enum\TransactionStatus;
use App\Enum\TransactionType;
use App\Form\TransferType;
use App\Repository\BankAccountRepository;
use App\Service\TransactionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/transaction')]
final class TransferController extends AbstractController
{
    #[Route('/transfer', name: 'app_transfer')]
    public function MakeTransfer(Request $request,
                             BankAccountRepository $bankAccountRepository,
                             TransactionService $transactionService, 
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
    

            if (!$sourceAccount->isActive()) {
                throw new AccessDeniedException('Le compte source est inactif. Transaction refusée.');
            }

            if (!$destinationAccount->isActive()) {
                throw new AccessDeniedException('Le compte destination est inactif. Transaction refusée.');
            }

            if (!$destinationAccount->canDeposit($amount)) {
                $transactionService->createFailedTransaction($amount, $sourceAccount, $destinationAccount, TransactionType::TRANSFER , $entityManager);

                throw $this->createAccessDeniedException('Transfer denied: the savings account has exceeded its deposit limit of 25,000.');

                $transaction->setStatus(TransactionStatus::FAILED);

            }
           
            
    
            $transactionService->processTransaction($amount, $sourceAccount, $destinationAccount, TransactionType::DEPOSIT);

    
            $newSourceAccount = $sourceAccount->getBalance() - $amount ;
            $sourceAccount->setBalance($newSourceAccount);

            $newDestinationAccount = $destinationAccount->getBalance() + $amount ;
            $destinationAccount->setBalance($newDestinationAccount);

            $entityManager->persist($destinationAccount);
            $entityManager->persist($sourceAccount);


            $entityManager->flush();
    
            return $this->json(['status' => 'ok']);
        }
    
        return $this->render('transactions/transfer.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    

    
}
