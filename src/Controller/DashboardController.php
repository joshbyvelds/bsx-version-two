<?php

namespace App\Controller;

use App\Entity\ShareSell;
use App\Entity\Transaction;
use App\Form\ShareSellType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Debt;
use App\Entity\User;
use App\Entity\Wallet;
use App\Entity\TotalValue;
use App\Entity\FuturesWeek;

use App\Controller\ToolsController;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'dashboard')]
    public function index(ManagerRegistry $doctrine): Response
    {
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
        $portfolios = $user->getPortfolios();


        //TODO: limit this to the last 6..
        $transactions = $user->getTransactions()->getValues();

        // check if ten percent plan needs to be updated
        ToolsController::updateTenPercentPlan( $user, $doctrine );

        // check if we need stock infomation
        $stocks = $user->getStocks();

        //check if we need to update the Total Value Chart
        $totalValues = $doctrine->getRepository(TotalValue::class)->findOneBy(['user' => $user->getId()]);

        // get Debt if any..
        $debt = $doctrine->getRepository(Debt::class)->findBy(['user' => $user->getId()]);


        return $this->render('dashboard/index.old.html.twig', [
            'page_title' => 'Dashboard',
            'show_nav' => true,
            'wallet' => $wallet,
            'futures' => $buckets,
            'futures_enabled' => $buckets,
            'current_futures_week' => $current_futures_week,
            'settings' => $settings,
            'stocks' => $stocks,
            'portfolios' => $portfolios,
            'plays' => $plays,
            'transactions' => array_slice($transactions, -$transactions_limit),
            'totalValues' => $totalValues,
            'debts' => $debt,
        ]);
    }

    #[Route('/dashboard/settotalvaluecolumn', name: 'dashboard_setTotalValue')]
    public function setTotalValueColumn(ManagerRegistry $doctrine, Request $request): Response
    {
        $em = $doctrine->getManager();
        $user = $this->getUser();
        $settings = $user->getSettings();
        $total = $request->get('total');
        $total = (empty($total)) ? 0.0 : $total;
        $totalValues = $doctrine->getRepository(TotalValue::class)->findOneBy(['user' => $user->getId()]);
        $date = new \DateTime();

        $progress = 0;
        $weekly = ($settings->isWeeklyTotalValue() && ($date->format('W') !== $totalValues->getDate()->format('W') && $date->format('w') === "5"));
        $daily = (!$settings->isWeeklyTotalValue() && $date->format('d') !== $totalValues->getDate()->format('d') && in_array($date->format('w'), ["1", "2", "3", "4", "5"]));

        if($daily || $weekly)
        {
            $progress = 1;
            if ($totalValues->getFill() < 20)
            {
                $progress = 2;
                switch ($totalValues->getFill() + 1)
                {
                    case(1):
                        $totalValues->setValue1($total);
                        $totalValues->setValueDate1($date);
                        break;
                    case(2):
                        $totalValues->setValue2($total);
                        $totalValues->setValueDate2($date);
                        break;
                    case(3):
                        $totalValues->setValue3($total);
                        $totalValues->setValueDate3($date);
                        break;
                    case(4):
                        $totalValues->setValue4($total);
                        $totalValues->setValueDate4($date);
                        break;
                    case(5):
                        $totalValues->setValue5($total);
                        $totalValues->setValueDate5($date);
                        break;
                    case(6):
                        $totalValues->setValue6($total);
                        $totalValues->setValueDate6($date);
                        break;
                    case(7):
                        $totalValues->setValue7($total);
                        $totalValues->setValueDate7($date);
                        break;
                    case(8):
                        $totalValues->setValue8($total);
                        $totalValues->setValueDate8($date);
                        break;
                    case(9):
                        $totalValues->setValue9($total);
                        $totalValues->setValueDate9($date);
                        break;
                    case(10):
                        $totalValues->setValue10($total);
                        $totalValues->setValueDate10($date);
                        break;
                    case(11):
                        $totalValues->setValue11($total);
                        $totalValues->setValueDate11($date);
                        break;
                    case(12):
                        $totalValues->setValue12($total);
                        $totalValues->setValueDate12($date);
                        break;
                    case(13):
                        $totalValues->setValue13($total);
                        $totalValues->setValueDate13($date);
                        break;
                    case(14):
                        $totalValues->setValue14($total);
                        $totalValues->setValueDate14($date);
                        break;
                    case(15):
                        $totalValues->setValue15($total);
                        $totalValues->setValueDate15($date);
                        break;
                    case(16):
                        $totalValues->setValue16($total);
                        $totalValues->setValueDate16($date);
                        break;
                    case(17):
                        $totalValues->setValue17($total);
                        $totalValues->setValueDate17($date);
                        break;
                    case(18):
                        $totalValues->setValue18($total);
                        $totalValues->setValueDate18($date);
                        break;
                    case(19):
                        $totalValues->setValue19($total);
                        $totalValues->setValueDate19($date);
                        break;
                    case(20):
                        $totalValues->setValue20($total);
                        $totalValues->setValueDate20($date);
                        break;
                }
                $totalValues->increaseFill();
            } else {
                $totalValues->moveLeft();
                $totalValues->setValue20($total);
                $totalValues->setValueDate20($date);
            }
            $totalValues->setDate($date);
            $em->flush();
        }

        return new JsonResponse(array('success' => false, 'd' => $daily, 'w' => $weekly, 'progress' => $progress, 'weekly'=> $settings->isWeeklyTotalValue(), 'date'=> $totalValues->getDate()->format('w'), 'date2'=> $date->format('w'), 'total' => $total, 'message' => "Portfolio does not belong to user."));
    }
}
