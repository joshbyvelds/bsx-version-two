<?php

namespace App\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class TransactionController extends AbstractController
{
    #[Route('/transactions', name: 'app_transaction')]
    public function index(Request $request, ManagerRegistry $doctrine): Response
    {

        $user = $this->getUser();
        $settings = $user->getSettings();
        $transactions = $stocks = $user->getTransactions();

        return $this->render('transaction/index.html.twig', [
            'transactions' => $transactions,
            'settings' => $settings,
        ]);
    }
}
