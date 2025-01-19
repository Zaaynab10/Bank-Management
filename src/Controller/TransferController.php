<?php

namespace App\Controller;

use App\Entity\Transaction;
use App\Enum\TransactionType;
use App\Form\TransferType;
use App\Repository\BankAccountRepository;
use App\Service\TransactionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/transaction')]
final class TransferController extends AbstractController
{
    #[Route('/transfer', name: 'app_transfer')]
    public function makeTransfer(
        Request $request,
        BankAccountRepository $bankAccountRepository,
        TransactionService $transactionService
    ): Response {
        $user = $this->getUser();
        $bankAccounts = $bankAccountRepository->findBy(['owner' => $user]);

        $transaction = new Transaction();
        $form = $this->createForm(TransferType::class, $transaction, ['user' => $user, 'bank_accounts' => $bankAccounts]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $sourceAccount = $form->get('source_account')->getData();
            $destinationAccount = $bankAccountRepository->findOneBy(['account_number' => $form->get('destination_account_number')->getData()]);
            $amount = $form->get('amount')->getData();

            if (!$sourceAccount || $sourceAccount->getOwner() !== $user) {
                throw $this->createAccessDeniedException('You do not own the source account.');
            }

            if (!$destinationAccount) {
                throw $this->createNotFoundException('Destination account not found.');
            }

            $transactionService->processTransaction($sourceAccount, $destinationAccount, $amount, TransactionType::TRANSFER);

            return $this->json(['status' => 'ok']);
        }

        return $this->render('transactions/transfer.html.twig', ['form' => $form->createView()]);
    }
}
