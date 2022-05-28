<?php

namespace App\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\User;
use App\Entity\Wallet;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'dashboard')]
    public function index(ManagerRegistry $doctrine): Response
    {
        $user = $user = $this->getUser();
        
        // TODO:: Make this a user option in the settings..
        $transactions_limit = 6;
        
        $wallet = $doctrine->getRepository(Wallet::class)->find($user->getId());

        //TODO: limit this to the last 6..
        $transactions = $user->getTransactions()->getValues();
        
        return $this->render('dashboard/index.html.twig', [
            'page_title' => 'Dashboard',
            'show_nav' => true,
            'wallet' => $wallet,
            'transactions' => array_slice($transactions, -$transactions_limit),
        ]);
    }
}
