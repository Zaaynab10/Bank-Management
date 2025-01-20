<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Repository\TransactionRepository;
use App\Repository\BankAccountRepository;
use App\Entity\TransactionStatus;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\Response;
use App\Service\UserSearchService;

final class AdminDashController extends AbstractController
{
    private UserSearchService $userSearchService;

    public function __construct(UserSearchService $userSearchService)
    {
        $this->userSearchService = $userSearchService;
    }

    #[Route('/admin/dash', name: 'home_admin_dash')]
    public function index(
        UserRepository $userRepository,
        BankAccountRepository $bankAccountRepository,
        TransactionRepository $transactionRepository
    ): Response {
        $user = $this->getUser(); // L'utilisateur connecté

        // Récupérer les statistiques globales
        $totalClients = $userRepository->count([]);
        $totalAccounts = $bankAccountRepository->count([]);
        $totalTransactions = $transactionRepository->count([]);
        $totalTransactionAmount = $transactionRepository->getTotalTransactionAmount();

        return $this->render('admin/index.html.twig', [
            'user' => $user,
            'totalClients' => $totalClients,
            'totalAccounts' => $totalAccounts,
            'totalTransactions' => $totalTransactions,
            'totalTransactionAmount' => $totalTransactionAmount,
        ]);
    }

    #[Route('/admin/search', name: 'admin_search', methods: ['GET'])]
    public function search(Request $request): JsonResponse
    {
        $query = $request->query->get('q', '');

        $users = $this->userSearchService->searchUsers($query);

        $data = [];
        foreach ($users as $user) {
            $data[] = [
                'id' => $user->getId(),
                'name' => $user->getFirstName(),
                'email' => $user->getEmail(),
                'phone' => $user->getPhone(),
            ];
        }

        return new JsonResponse($data);
    }

    #[Route('/admin/user/{id}', name: 'admin_user_detail')]
    public function userDetail(
        int $id,
        UserRepository $userRepository,
        BankAccountRepository $bankAccountRepository,
        TransactionRepository $transactionRepository
    ): Response {
        $user = $userRepository->find($id);
        if (!$user) {
            throw $this->createNotFoundException("L'utilisateur demandé n'existe pas.");
        }

        $transactions = $transactionRepository->findTransactionsByUserId($id);
        $bankAccounts = $bankAccountRepository->findBy(['owner' => $id]);

        return $this->render('admin/userDetail.html.twig', [
            'user' => $user,
            'bankAccounts' => $bankAccounts,
            'transactions' => $transactions,
        ]);
    }

    #[Route('/admin/add-user', name: 'admin_add_user', methods: ['POST'])]
    public function addUser(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new User();

        $user->setFirstName($request->request->get('firstName'));
        $user->setLastName($request->request->get('lastName'));
        $user->setEmail($request->request->get('email'));
        $user->setPhone($request->request->get('phone'));
        $user->setRoles([$request->request->get('roles')]);

        // Hash du mot de passe
        $hashedPassword = $passwordHasher->hashPassword($user, $request->request->get('password'));
        $user->setPassword($hashedPassword);

        // Sauvegarde du nouvel utilisateur
        $entityManager->persist($user);
        $entityManager->flush();

        $this->addFlash('success', 'Utilisateur ajouté avec succès !');

        return $this->redirectToRoute('home_admin_dash');
    }
}
