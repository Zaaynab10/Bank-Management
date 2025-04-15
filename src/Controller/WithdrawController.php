<?php

namespace App\Controller;

use Symfony\Component\Security\Http\Attribute\IsGranted;
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
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/transaction')]
final class WithdrawController extends AbstractController
{
    #[Route('/withdraw', name: 'app_withdraw')]
    #[IsGranted('ROLE_CUSTOMER')]
    public function MakeWithdrawal(
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

        // Vérifier que le compte appartient à l'utilisateur
        if ($bankAccount->getOwner() !== $user) {
            throw $this->createAccessDeniedException('You do not own this account.');
        }

        // Vérifier si le compte est actif
        if (!$bankAccount->isActive()) {
            throw new AccessDeniedException('Le compte source est inactif. Transaction refusée.');
        }

        // Créer une nouvelle transaction
        $transaction = new Transaction();
        $transaction->setSourceAccount($bankAccount); // Définir le compte automatiquement comme source

        // Créer le formulaire de retrait (sans champ pour le compte)
        $form = $this->createForm(WithdrawType::class, $transaction);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $amount = $form->get('amount')->getData();

            if (!$bankAccount->canWithdraw($amount)) {
                throw $this->createAccessDeniedException('Withdrawal denied, insufficient funds or limit exceeded.');
            }

            // Traiter la transaction de retrait
            $transactionService->processTransaction($amount, $bankAccount, $bankAccount, TransactionType::WITHDRAWAL);

            // Redirection après succès
            return $this->redirectToRoute('user_accounts');
        }

        return $this->render('transactions/withdraw.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
