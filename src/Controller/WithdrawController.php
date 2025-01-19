<?php

namespace App\Controller;

use App\Entity\Transaction;
use App\Enum\TransactionType;
use App\Form\WithdrawType;
use App\Repository\BankAccountRepository;
use App\Service\TransactionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/transaction')]
final class WithdrawController extends AbstractController
{
    #[Route('/withdraw', name: 'app_withdraw')]
    public function makeWithdrawal(
        Request $request,
        BankAccountRepository $bankAccountRepository,
        TransactionService $transactionService
    ): Response {
        $user = $this->getUser();
        $transaction = new Transaction();
        $form = $this->createForm(WithdrawType::class, $transaction, ['user' => $user]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $bankAccount = $bankAccountRepository->find($form->get('bankAccount')->getData());
            $amount = $form->get('amount')->getData();

            if (!$bankAccount || $bankAccount->getOwner() !== $user) {
                throw $this->createAccessDeniedException('You do not own this account.');
            }

            if (!$bankAccount->canWithdraw($amount)) {
                throw $this->createAccessDeniedException('Insufficient funds.');
            }

            $transactionService->processTransaction($bankAccount, $bankAccount, $amount, TransactionType::WITHDRAWAL);

            return $this->redirectToRoute('app_user');
        }

        return $this->render('transactions/withdraw.html.twig', ['form' => $form->createView()]);
    }
}
