<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PostLoginController extends AbstractController
{
    #[Route('/post-login', name: 'post_login')]
    public function redirectAfterLogin(): Response
    {
        // Vérifie les rôles et redirige en conséquence
        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('home_admin_dash'); // Admin dashboard
        }

        if ($this->isGranted('ROLE_CUSTOMER')) {
            return $this->redirectToRoute('customer_dashboard'); // Customer dashboard (à changer/créer)
        }

        // Si aucun rôle spécifique, redirige vers la page d'accueil
        return $this->redirectToRoute('app_home');
    }
}
