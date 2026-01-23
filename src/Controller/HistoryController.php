<?php

namespace App\Controller;

use App\Entity\WeeklyPortfolioTotal;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HistoryController extends AbstractController
{
    #[Route('/portfolio-history', name: 'app_portfolio_history')]
    public function index(ManagerRegistry $doctrine): Response
    {
        $user = $this->getUser();
        $settings = $user->getSettings();
        $weeks = $user->getWeeklyPortfolioTotals();

        return $this->render('history/portfolio.html.twig', [
            'controller_name' => 'HistoryController',
            'settings' => $settings,
            'weeks' => $weeks,
        ]);
    }
}
