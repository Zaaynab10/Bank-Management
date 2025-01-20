<?php 
namespace App\Controller;

use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Enum\BankAccountStatus;
use App\Repository\BankAccountRepository;
use App\Service\AccountService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminManageUsersController extends AbstractController
{
    private $accountService;

    public function __construct(AccountService $accountService)
    {
        $this->accountService = $accountService;
    }

    #[Route('/admin/user/{id}/accounts', name: 'admin_user_accounts')]
    #[IsGranted('ROLE_ADMIN')] 
    public function showUserAccounts(int $id): Response
    {
        $bankAccounts = $this->accountService->getUserAccounts($id);

        return $this->render('admin/userAccounts.html.twig', [
            'bankAccounts' => $bankAccounts,
            'id' => $id,
        ]);
    }

    #[Route('/admin/user/{id}/account/{accountId}/transactions', name: 'admin_account_transactions')]
    #[IsGranted('ROLE_ADMIN')] 

    public function showAccountTransactions(int $accountId): Response
    {
        $transactions = $this->accountService->getAccountTransactions($accountId);

        return $this->render('admin/userAccountTransactions.html.twig', [
            'transactions' => $transactions,
            'accountId' => $accountId,
        ]);
    }

    #[Route('/admin/account/{id}/toggle-status', name: 'toggle_bank_account_status')]
    #[IsGranted('ROLE_ADMIN')] 

    public function toggleStatus(int $id, BankAccountRepository $bankAccountRepository, EntityManagerInterface $entityManager): Response
    {
        $bankAccount = $bankAccountRepository->find($id);
        
        if (!$bankAccount) {
            throw $this->createNotFoundException("Bank account not found.");
        }

        if ($bankAccount->getStatus() === BankAccountStatus::ACTIVE) {
            $bankAccount->setStatus(BankAccountStatus::CLOSE);
        } else {
            $bankAccount->setStatus(BankAccountStatus::ACTIVE);
        }

        $entityManager->flush();

        return $this->redirectToRoute('admin_user_accounts');
    }
}
