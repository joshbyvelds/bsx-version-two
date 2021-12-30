<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'dashboard')]
    public function index(): Response
    {
        $user = $user = $this->getUser();
        $name = ['username' => 'test', 'realname' => 'Test User'];
        return $this->render('dashboard/index.html.twig', [
            'user' => $name,
        ]);
    }
}
