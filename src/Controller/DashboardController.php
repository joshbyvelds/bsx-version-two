<?php

namespace App\Controller;

use App\Entity\Company;
use App\Entity\HighInterestSavingsAccount;
use App\Entity\ShareSell;
use App\Entity\Stock;
use App\Entity\Transaction;
use App\Entity\WeeklyPortfolioTotal;
use App\Form\ShareSellType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Debt;
use App\Entity\Sector;
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

        $plays = $settings->isDashboardPlaysPanel() ? $user->getPlays() : false;

        $portfolios = $user->getPortfolios();


        //TODO: limit this to the last 6..
        $transactions = $user->getTransactions()->getValues();

        // check if ten percent plan needs to be updated
        ToolsController::updateTenPercentPlan( $user, $doctrine );

        // check if we need stock infomation
        $stocks = $user->getStocks();

        //check if we need to update the Total Value Chart
        $weeklyPortfolioTotals = $user->getWeeklyPortfolioTotals()->toArray();

        $lastStartDate = $weeklyPortfolioTotals[count($weeklyPortfolioTotals) - 1]->getStartDate();
        if($lastStartDate >= new \DateTime('now')){
            unset($weeklyPortfolioTotals[count($weeklyPortfolioTotals) - 1]);
        }

        $n = 20;
        $lastWeeklyPortfolioTotals = array_slice($weeklyPortfolioTotals, -$n);
        //$totalValues = $doctrine->getRepository(TotalValue::class)->findOneBy(['user' => $user->getId()]);

        // get Debt if any..
        $debt = $doctrine->getRepository(Debt::class)->findBy(['user' => $user->getId()]);

        $cc = $settings->isDashboardCcPanel() ? $user->getWrittenOptions() : false;

        $weights = $this->getTopWeights($stocks);

        $sectors = $this->getSectorPercentages($doctrine->getRepository(Sector::class)->findAll(), $user);

        $hisas = null;

        if ($settings->isUseHisa()){
            $hisas =  $doctrine->getRepository(HighInterestSavingsAccount::class)->findBy(['user' => $user->getId()]);
        }

        return $this->render('dashboard/index.old.html.twig', [
            'page_title' => 'Dashboard',
            'show_nav' => true,
            'wallet' => $wallet,
            'futures' => $buckets,
            'futures_enabled' => $buckets,
            'current_futures_week' => $current_futures_week,
            'settings' => $settings,
            'stocks' => $stocks,
            'weights' => $weights,
            'sectors' => $sectors,
            'portfolios' => $portfolios,
            'plays' => $plays,
            'transactions' => array_slice($transactions, -$transactions_limit),
            'totalValues' => $lastWeeklyPortfolioTotals,
            'covered_calls' => $cc,
            'debts' => $debt,
            'hisas' => $hisas
        ]);
    }

    #[Route('/dashboard/updateWeeklyPortfolioTotal', name: 'dashboard_set_weekly_portfolio_total')]
    public function setWeeklyPortfolioTotal(ManagerRegistry $doctrine, Request $request): Response
    {
        $em = $doctrine->getManager();
        $user = $this->getUser();
        $settings = $user->getSettings();
        $new = false;

        $total = $request->get('total');
        $total = (empty($total)) ? 0.0 : $total;
        $weeklyPortfolioTotals = $user->getWeeklyPortfolioTotals()->toArray();
        $lastEntry = $weeklyPortfolioTotals[count($weeklyPortfolioTotals) - 1];
        $lastEndDate = $lastEntry->getEndDate()->modify('+1 day');

        $d_m1 = false;
        $d_m2 = false;
        $d_m3 = false;

        $d_y1 = false;
        $d_y2 = false;
        $d_y3 = false;

        $monday = false;
        $friday = false;

        if($lastEndDate < new \DateTime('now')){
            $new = true;
            $lastEntry->setCurrent(false);

            $monday = clone $lastEndDate;
            $friday = clone $lastEndDate;
            $monday = $monday->modify('next monday');
            $friday = $friday->modify('next friday');

            $newWeeklyTotal = new WeeklyPortfolioTotal();
            $newWeeklyTotal->setUser($user);
            $newWeeklyTotal->setStartDate($monday);
            $newWeeklyTotal->setEndDate($friday);
            $newWeeklyTotal->setAmount(round($total,2));
            $newWeeklyTotal->setCurrent(true);
            $newWeeklyTotal->setEndofmonth(false);
            $newWeeklyTotal->setEndofyear(false);

            $month1 = $lastEndDate->format('Y-m');
            $month2 = $monday->format('Y-m');
            $month3 = $friday->format('Y-m');

            $year1 = $lastEndDate->format('Y');
            $year2 = $monday->format('Y');
            $year3 = $friday->format('Y');

            $d_m1 = $month1;
            $d_m2 = $month2;
            $d_m3 = $month3;

            $d_y1 = $year1;
            $d_y2 = $year2;
            $d_y3 = $year3;

            if ($month1 !== $month2) {
                $lastEntry->setEndofmonth(true);
            }

            if ($month2 !== $month3) {
                $newWeeklyTotal->setEndofmonth(true);
            }

            if ($year1 !== $year2) {
                $lastEntry->setEndofyear(true);
            }

            if ($year2 !== $year3) {
                $newWeeklyTotal->setEndofyear(true);
            }

            $em->persist($newWeeklyTotal);
        } else {
            $lastEntry->setAmount(round($total,2));
        }

        $em->flush();

        return new JsonResponse(array('new' => $new, 'lastEndDate' => $lastEndDate, 'monday' => $monday, 'friday' => $friday, 'm1' => $d_m1, 'm2' => $d_m2, 'm3' => $d_m3, 'y1' => $d_y1, 'y2' => $d_y2, 'y3' => $d_y3));
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

        // hack if we miss updates window..
        // $date->modify("-1 day"); // how many days missed...

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

    private function getTopWeights($stocks): array{
        $weight_stocks = [];
        $total_value = 0;

        foreach ($stocks as $stock){
            $company = $stock->getCompany();
            $weight_stock_object = ['ticker' => $company->getTicker(), 'name' => $company->getName(), 'total' => 0, 'weight' => 0];
            $stock_total_shares = 0;
            $stock_total_value = 0;

            if($stock->getSharesOwned() > 0) {

                foreach ($stock->getShareBuys() as $buy) {
                    if ($buy->getSold() < $buy->getAmount()) {
                        $stock_total_shares += $buy->getAmount() - $buy->getSold();
                    }
                }

                foreach ($stock->getCoveredCalls() as $cc) {
                    if (!$cc->isExpired() && !$cc->isExercised()) {
                        if ($cc->getStrike() > $company->getCurrentPrice()) {
                            $stock_total_shares -= 100 * $cc->getContracts();
                            $stock_total_value += $cc->getContracts() * (($company->getCountry() === "USD") ? ($cc->getStrike() * 100 * 1.36) : $cc->getStrike() * 100);
                        }
                    }
                }

                if($stock_total_shares > 0) {
                    $stock_total_value += ($company->getCountry() === "USD") ? (($stock_total_shares * $company->getCurrentPrice()) * 1.36) : ($stock_total_shares * $company->getCurrentPrice());
                    $weight_stock_object['total_value'] = $stock_total_value;
                    $weight_stocks[] = $weight_stock_object;
                    $total_value += $stock_total_value;
                }
            }
        }

        $returned_ws = [];
        foreach ($weight_stocks as $wso)
        {
            $wso['weight'] = $wso['total_value'] / $total_value;
            $returned_ws[] = $wso;
        }

        usort($returned_ws, function($a, $b) {
            return strcmp($b['weight'], $a['weight']);
        });

        return array_slice($returned_ws, 0, 10);
    }

    public function getSectorPercentages($sectors, $user): array
    {
        $total_value = 0;
        $sector_values = [];
        foreach ($sectors as $sector){
            $sector_value = ['name' => $sector->getName(), 'total' => 0, 'percent' => 0];
            $companies = $sector->getCompanies();

            foreach ($companies as $company){
                foreach($company->getStocks() as $stock){
                    if($stock->getUser() === $user){
                        $stock_total_shares = 0;
                        if($stock->getSharesOwned() > 0) {

                            foreach ($stock->getShareBuys() as $buy) {
                                if ($buy->getSold() < $buy->getAmount()) {
                                    $stock_total_shares += $buy->getAmount() - $buy->getSold();
                                }
                            }

                            foreach ($stock->getCoveredCalls() as $cc) {
                                if (!$cc->isExpired() && !$cc->isExercised()) {
                                    if ($cc->getStrike() > $company->getCurrentPrice()) {
                                        $stock_total_shares -= 100 * $cc->getContracts();
                                        $sector_value['total'] += $cc->getContracts() * (($company->getCountry() === "USD") ? ($cc->getStrike() * 100 * 1.36) : $cc->getStrike() * 100);
                                    }
                                }
                            }

                            if($stock_total_shares > 0) {
                                $sector_value['total'] += ($company->getCountry() === "USD") ? (($stock_total_shares * $company->getCurrentPrice()) * 1.36) : ($stock_total_shares * $company->getCurrentPrice());
                            }
                        }
                    }
                }
            }



            $sector_values[] = $sector_value;
            $total_value += $sector_value['total'];
        }

        $new_sector_values = [];
        foreach($sector_values as $sector_value){
            $new_sector_values[] = ['name' => $sector_value['name'], 'total' => $sector_value['total'], 'percent' => ($sector_value['total']/$total_value)];
        }

        dump($new_sector_values);
        return $new_sector_values;
    }
}
