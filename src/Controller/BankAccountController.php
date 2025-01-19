<?php

namespace App\Controller;

use App\Entity\BankAccount;
use App\Enum\BankAccountType;
use App\Enum\BankAccountStatus;
use App\Form\BankAccountType as BankAccountFormType;
use App\Repository\BankAccountRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class BankAccountController extends AbstractController
{
    #[Route('/bank/account/')]
    #[Route('/bank/account/{id}', name: 'app_bank_account')]
    public function index(int $id, BankAccountRepository $bankAccountRepository): Response
    {
        $bankAccount = $bankAccountRepository->find($id);

        if ($bankAccount === null) {
            throw $this->createNotFoundException();
        }

        if ($bankAccount->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $balance = $bankAccount->getBalance();

        return $this->json([
            'balance' => $balance, 
            'type' => $bankAccount->getType()->value, 
            'id' => $bankAccount->getId()
        ]);
    }

    #[Route('/bank/accounts/create', name: 'app_create_bank_account')]
    public function create(Request $request, EntityManagerInterface $entityManager, BankAccountRepository $bankAccountRepository): Response
    {
        $bankAccounts = $bankAccountRepository->findBy(['owner' => $this->getUser()]);
        if (count($bankAccounts) >= 5) {
            throw $this->createAccessDeniedException('You can only have up to 5 bank accounts');
        }

        $isSavingsAccount = $request->get('type') === BankAccountType::SAVINGS->value;
        if ($isSavingsAccount) {
            $hasSufficientBalance = $this->hasValidCurrentAccount($bankAccounts);
            if (!$hasSufficientBalance) {
                throw $this->createAccessDeniedException('You must have at least one current account with a balance of 10 or more to create a savings account');
            }
        }

        $bankAccount = new BankAccount();
        $form = $this->createForm(BankAccountFormType::class, $bankAccount);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $bankAccount->setOwner($this->getUser());

            $accountNumber = rand(1000000000, 9999999999);
            $bankAccount->setAccountNumber((string)$accountNumber);

            $bankAccount->setBalance(100);

            $bankAccount->setStatus(BankAccountStatus::ACTIVE);

            $entityManager->persist($bankAccount);
            $entityManager->flush();

            return $this->redirectToRoute('customer_dashboard');
        }

        return $this->render('bank_account/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    private function hasValidCurrentAccount(array $bankAccounts): bool
    {
        foreach ($bankAccounts as $account) {
            if ($account->getType() === BankAccountType::CURRENT && $account->getBalance() >= 10) {
                return true;
            }
        }
        return false;
    }
}
