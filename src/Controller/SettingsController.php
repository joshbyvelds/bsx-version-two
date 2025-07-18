<?php

namespace App\Controller;


use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Settings;
use App\Form\SettingsType;

class SettingsController extends AbstractController
{
    #[Route('/settings', name: 'settings')]
    public function index(ManagerRegistry $doctrine, Request $request): Response
    {
        $user = $this->getUser();
        $em = $doctrine->getManager();
        $settings = $user->getSettings();
        
        if(empty($settings)){
            $settings = new Settings();
            $settings->setUser($user);
            $settings->setDashboardPortfolioLarge(false);
            $settings->setDashboardTransactions(6);
            $settings->setCashEquityBalance(0);
            $settings->setMaxPlayMoney(1000);
            $settings->setMaxPlays(6);
            $settings->setStocksUpdateSoldPrice(false);
            $settings->setStocksManualUpdateEnabled(false);
            $settings->setStocksDisableUpdateEnabled(false);
            $settings->setStocksDisableCanadianUpdateEnabled(false);
            $settings->setFuturesEnabled(false);
            $settings->setFuturesPlayBucketMax(1000);
            $settings->setFuturesProfitBucketMax(1000);
            $settings->setFuturesUseBrokerMargin(false);
            $settings->setFuturesBrokerMarginAmount(0);
            $settings->setFuturesUseSplitProfits(false);
            $settings->setFuturesWeeklyGoal(125);
            $settings->setFuturesDataFee(13);
            $settings->setFuturesProfitSplitLevel1Amount(0);
            $settings->setFuturesProfitSplitLevel2Amount(0);
            $settings->setFuturesProfitSplitLevel3Amount(0);
            $settings->setFuturesProfitSplitLevel4Amount(0);
            $settings->setFuturesProfitSplitLevel5Amount(0);
            $settings->setFuturesProfitSplitLevel6Amount(0);
            $settings->setFuturesProfitSplitLevel7Amount(0);
            $settings->setFuturesProfitSplitLevel1Ratio(0);
            $settings->setFuturesProfitSplitLevel2Ratio(0);
            $settings->setFuturesProfitSplitLevel3Ratio(0);
            $settings->setFuturesProfitSplitLevel4Ratio(0);
            $settings->setFuturesProfitSplitLevel5Ratio(0);
            $settings->setFuturesProfitSplitLevel6Ratio(0);
            $settings->setFuturesProfitSplitLevel7Ratio(0);
            $settings->setTheme(1);
            $settings->setCashEquityBalance(0);
            $settings->setDashboardUseHotColdMeter(false);
            $settings->setDashboardUseCashEquityBalance(false);
            $settings->setStocksCanadianUpdateAmountLimit(5);
            $settings->setStocksCanadianUpdateLimitTimeout(10);
            $settings->setPortfolioUpdateOnWeekend(false);
            $settings->setStockUpdateOnWeekend(false);
            $settings->setTFSALimit(0);
            $settings->setTFSAEnabled(false);
            $settings->setTFSATrackBalance(true);
            $settings->setFHSALimit(0);
            $settings->setFHSAEnabled(false);
            $settings->setRRSPLimit(0);
            $settings->setRRSPEnabled(false);
            $settings->setWeeklyTotalValue(false);
            $settings->useHISA(false);
            $em->persist($settings);
            $em->flush();
        }
        


        $form = $this->createForm(SettingsType::class, $settings);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            return $this->redirectToRoute('dashboard');
        }

        return $this->render('settings/index.html.twig', [
            'form' => $form->createView(),
            'settings' => $settings,
        ]);
    }
}
