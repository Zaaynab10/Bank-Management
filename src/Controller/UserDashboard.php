<?php
namespace App\Controller;

use App\Service\AccountService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


class UserDashboard extends AbstractController
{
    private $accountService;

    public function __construct(AccountService $accountService)
    {
        $this->accountService = $accountService;
    }

    #[Route('/user/accounts', name: 'user_accounts')]
    #[IsGranted('ROLE_CUSTOMER')] 

    public function showUserAccounts(): Response
    {
        $user = $this->getUser();
        $bankAccounts = $this->accountService->getUserAccounts($user);

        return $this->render('user/userAccounts.html.twig', [
            'bankAccounts' => $bankAccounts,
        ]);
    }

    #[Route('/user/account/{accountId}/transactions', name: 'user_account_transactions')]
    #[IsGranted('ROLE_CUSTOMER')] 

    public function showAccountTransactions(int $accountId): Response
    {
        $transactions = $this->accountService->getAccountTransactions($accountId);

        return $this->render('user/accountTransactions.html.twig', [
            'transactions' => $transactions,
            'accountId' => $accountId,
        ]);
    }
}
