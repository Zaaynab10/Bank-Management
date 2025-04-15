<?php

namespace App\Controller;

use App\Entity\Transaction;
use App\Enum\TransactionType;
use App\Form\DepositType;
use App\Repository\BankAccountRepository;
use App\Service\TransactionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[Route('/transaction')]
final class DepositController extends AbstractController
{
    #[Route('/deposit', name: 'app_deposit')]
    #[IsGranted('ROLE_CUSTOMER')]
    public function MakeDeposit(
        Request $request, 
        EntityManagerInterface $entityManager, 
        TransactionService $transactionService, 
        BankAccountRepository $bankAccountRepository,
        SessionInterface $session
    ): Response {
        $user = $this->getUser();

        $bankAccountId = $session->get('bank_account_id');
        if (!$bankAccountId) {
            throw $this->createAccessDeniedException('No bank account selected in the session.');
        }

        $bankAccount = $bankAccountRepository->find($bankAccountId);
        if (!$bankAccount) {
            throw $this->createAccessDeniedException('Bank account not found.');
        }

        if ($bankAccount->getOwner() !== $user) {
            throw $this->createAccessDeniedException('You do not own this account.');
        }

        if (!$bankAccount->isActive()) {
            throw new AccessDeniedException('Le compte source est inactif. Transaction refusée.');
        }

        $transaction = new Transaction();
        $transaction->setSourceAccount($bankAccount); 
        $form = $this->createForm(DepositType::class, $transaction);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $amount = $form->get('amount')->getData();

            if (!$bankAccount->canDeposit($amount)) {
                throw $this->createAccessDeniedException('Deposit denied, the deposit limit is 25,000.');
            }

            $transactionService->processTransaction($amount, $bankAccount, $bankAccount, TransactionType::DEPOSIT);

            return $this->redirectToRoute('user_accounts');
        }

        return $this->render('transactions/deposit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
