<?php

namespace App\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\User;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'dashboard')]
    public function index(ManagerRegistry $doctrine): Response
    {
        //$user = $user = $this->getUser();
        $user = $doctrine->getRepository(User::class)->find(10);

        // Fake User for Now
        $name = ['username' => 'test', 'realname' => 'Test User'];
        
        //TODO:: Get the users wallet.. for now use the demo
        $wallet = $user->getWallet();

        //TODO: limit this to the last 6..
        $transactions = $user->getTransactions();
        
        return $this->render('dashboard/index.html.twig', [
            'page_title' => 'Dashboard',
            'show_nav' => true,
            'user' => $name,
            'wallet' => $wallet,
            'transactions' => $transactions,
        ]);
    }
}
