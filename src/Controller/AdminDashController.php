<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Repository\TransactionRepository;
use App\Repository\BankAccountRepository;

final class AdminDashController extends AbstractController
{
    #[Route('/admin/dash', name: 'home_admin_dash')]
    public function index(): Response
    {
        $user = $this->getUser(); // L'utilisateur connecté

        return $this->render('admin/index.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/admin/search', name: 'admin_search', methods: ['GET'])]
    public function search(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $query = $request->query->get('q', ''); // Récupère le terme recherché ou vide par défaut

        if (empty($query)) {
            // Si aucun terme recherché, retourne les utilisateurs les plus récents
            $users = $entityManager->createQueryBuilder()
                ->select('u')
                ->from(User::class, 'u')
                ->orderBy('u.id', 'DESC')
                ->setMaxResults(50) // Limite de résultats affichés par défaut
                ->getQuery()
                ->getResult();
        } else {
            // Si un terme est recherché, applique les filtres
            $users = $entityManager->createQueryBuilder()
                ->select('u')
                ->from(User::class, 'u')
                ->where('LOWER(u.email) LIKE LOWER(:query)')
                ->orWhere('LOWER(u.firstName) LIKE LOWER(:query)')
                ->orWhere('LOWER(u.lastName) LIKE LOWER(:query)')
                ->orWhere('u.phone LIKE :query')
                ->setParameter('query', '%' . $query . '%')
                ->orderBy('u.id', 'DESC')
                ->setMaxResults(50)
                ->getQuery()
                ->getResult();
        }

        // Préparer les données pour le retour JSON
        $data = [];
        foreach ($users as $user) {
            $data[] = [
                'id' => $user->getId(),
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastName(),
                'email' => $user->getEmail(),
                'phone' => $user->getPhone(),
                'profilePicture' => '/images/user.svg', // Remplace par la vraie image si disponible
            ];
        }

        return new JsonResponse($data);
    }

    // src/Controller/UserDetailController.php
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

        $bankAccounts = $bankAccountRepository->findBy(['owner' => $id]);
        $transactions = $transactionRepository->findTransactionsByUserId($id);

        return $this->render('admin/user_detail.html.twig', [
            'user' => $user,
            'bankAccounts' => $bankAccounts,
            'transactions' => $transactions,
        ]);
    }


}
