<?php

namespace App\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\User;
use App\Entity\Wallet;
use App\Entity\FuturesWeek;

use App\Controller\ToolsController;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'dashboard')]
    public function index(ManagerRegistry $doctrine): Response
    {
        // $locale = "en_US";
        // \Locale::setDefault($locale);
        // setlocale(LC_ALL, 'en_US');
        // setlocale(LC_MESSAGES, $locale);
        // setlocale(LC_TIME, $locale);

        $user = $user = $this->getUser();
        $settings = $user->getSettings();
        $transactions_limit = $user->getSettings()->getDashboardTransactions();
        
        // Stocks Wallet..
        $wallet = $doctrine->getRepository(Wallet::class)->find($user->getId());

        // Futures Panel..
        if($user->getFuturesBuckets()->isEmpty()){
            $buckets = "NO BUCKETS";
        } else {
            $buckets = $user->getFuturesBuckets()[0];
        }

        $weeks = $doctrine->getRepository(FuturesWeek::class)->findAll(array('user_id' => $user->getId()));

        if($weeks){
            $current_futures_week = array_pop($weeks);
        } else {
            $current_futures_week = null;
        }

        $plays = $user->getPlays();


        //TODO: limit this to the last 6..
        $transactions = $user->getTransactions()->getValues();

        // check if ten percent plan needs to be updated
        ToolsController::updateTenPercentPlan( $user, $doctrine );
        
        return $this->render('dashboard/index.html.twig', [
            'page_title' => 'Dashboard',
            'show_nav' => true,
            'wallet' => $wallet,
            'futures' => $buckets,
            'current_futures_week' => $current_futures_week,
            'settings' => $settings,
            'plays' => $plays,
            'transactions' => array_slice($transactions, -$transactions_limit),
        ]);
    }
}
