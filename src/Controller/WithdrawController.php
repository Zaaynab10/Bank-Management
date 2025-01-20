<?php

namespace App\Controller;

use App\Entity\Transaction;
use App\Enum\TransactionType;
use App\Form\WithdrawType;
use App\Repository\BankAccountRepository;
use App\Service\TransactionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/transaction')]
final class WithdrawController extends AbstractController
{
    #[Route('/withdraw', name: 'app_withdraw')]
    public function MakeWithdrawal(
        Request $request, 
        EntityManagerInterface $entityManager, 
        TransactionService $transactionService, 

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
           if (!$bankAccount->isActive()) {
            throw new AccessDeniedException('Le compte source est inactif. Transaction refusée.');
        }

            if (!$bankAccount || $bankAccount->getOwner() !== $user) {
                throw $this->createAccessDeniedException('You do not own this account.');
            }

            
            if (!$bankAccount || $bankAccount->getOwner() !== $user) {
                $transactionService->createFailedTransaction($amount, $bankAccount, $bankAccount, TransactionType::DEPOSIT , $entityManager);

                throw $this->createAccessDeniedException('You do not own this account.');
            }
            
            $transactionService->processTransaction($amount, $bankAccount, $bankAccount, TransactionType::DEPOSIT);

            $newBankAccount = $bankAccount->getBalance() - $amount ;
            $bankAccount->setBalance($newBankAccount);


            $entityManager->persist($bankAccount);
            $entityManager->flush();

            return $this->redirectToRoute('app_user');
        }

        return $this->render('transactions/withdraw.html.twig', [
            'form' => $form->createView(),
        ]);
    }


}
