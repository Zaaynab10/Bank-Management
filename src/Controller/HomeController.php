<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security as SecurityBundleSecurity;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class HomeController extends AbstractController
{
    #[Route('/', name: 'app_homepage')]
    public function index(SecurityBundleSecurity $security): Response
    {
        if ($security->getUser()) {
            return $this->redirectToRoute('user_accounts'); 
        }

        return $this->render('home/index.html.twig');
}
}