<?php

namespace App\Controller;

use App\Entity\Transaction;
use App\Enum\TransactionType;
use App\Form\DepositType;
use App\Repository\BankAccountRepository;
use App\Service\TransactionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/transaction')]
final class DepositController extends AbstractController
{
    #[Route('/deposit', name: 'app_deposit')]
    public function makeDeposit(
        Request $request, 
        BankAccountRepository $bankAccountRepository,
        TransactionService $transactionService
    ): Response {
        $user = $this->getUser();
        $transaction = new Transaction();
        $form = $this->createForm(DepositType::class, $transaction, ['user' => $user]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $bankAccount = $bankAccountRepository->find($form->get('bankAccount')->getData());
            $amount = $form->get('amount')->getData();

            if (!$bankAccount || $bankAccount->getOwner() !== $user) {
                throw $this->createAccessDeniedException('You do not own this account.');
            }

            if (!$bankAccount->canDeposit($amount)) {
                throw $this->createAccessDeniedException('Deposit limit exceeded.');
            }

            $transactionService->processTransaction($bankAccount, $bankAccount, $amount, TransactionType::DEPOSIT);

            return $this->redirectToRoute('app_user');
        }

        return $this->render('transactions/deposit.html.twig', ['form' => $form->createView()]);
    }
}
