<?php

namespace App\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Wallet;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'dashboard')]
    public function index(ManagerRegistry $doctrine): Response
    {
        $user = $user = $this->getUser();
        $name = ['username' => 'test', 'realname' => 'Test User'];
        //TODO:: Get the users wallet.. for now use the demo
        $wallet = $doctrine->getRepository(Wallet::class)->find(1);
        
        return $this->render('dashboard/index.html.twig', [
            'page_title' => 'Dashboard',
            'user' => $name,
            'wallet' => $wallet,
        ]);
    }
}
