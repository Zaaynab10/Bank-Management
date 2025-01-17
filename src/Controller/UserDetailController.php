<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\UserRepository;

final class UserDetailController extends AbstractController{
    // #[Route('/admin/user/{id}', name: 'admin_user_detail')]
    // public function userDetail(int $id, UserRepository $userRepository): Response
    // {
    //     $user = $userRepository->find($id);

    //     if (!$user) {
    //         throw $this->createNotFoundException('Utilisateur non trouvé');
    //     }

    //     return $this->render('admin/user_detail.html.twig', [
    //         'user' => $user,
    //     ]);
    // }

}
