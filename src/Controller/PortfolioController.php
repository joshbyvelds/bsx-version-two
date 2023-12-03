<?php

namespace App\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\User;
use App\Entity\Portfolio;
use App\Form\PortfolioType;

use App\Controller\ToolsController;

class PortfolioController extends AbstractController
{
    #[Route('/portfolio/create', name: 'create_portfolio')]
    public function create(ManagerRegistry $doctrine, Request $request): Response
    {
        $portfolio = new Portfolio();
        $user = $this->getUser();
        $form = $this->createForm(PortfolioType::class, $portfolio);
        $form->handleRequest($request);

        if($form->isSubmitted()){
            $data = $form->getData();
            $now = new \DateTime();
            $portfolio->setUser($user);
            $portfolio->setUpdated($now);
            $portfolio->setWorth(0);
            $portfolio->setYesterday(0);
            $portfolio->setLastWeek(0);
            $portfolio->setLastMonth(0);
            $portfolio->setLastYear(0);
            $em = $doctrine->getManager();
            $em->persist($portfolio);
            $em->flush();

           return $this->redirectToRoute('dashboard');
        }

        return $this->render('form/index.html.twig', [
            'page_title' => 'Create New Portfolio',
            'form' => $form->createView(),
            'error' => "",
        ]);
    }

    #[Route('/portfolio/edit/{id}', name: 'edit_portfolio')]
    public function edit(int $id, ManagerRegistry $doctrine, Request $request): Response
    {
        $portfolio = $doctrine->getRepository(Portfolio::class)->find($id);
        $user = $this->getUser();

        if( $portfolio === null ){
            return $this->redirectToRoute('dashboard');
        }

        if( $user->getId() !== $portfolio->getUser()->getId() ){
            return $this->redirectToRoute('dashboard');
        }

        $form = $this->createForm(PortfolioType::class, $portfolio);
        $form->handleRequest($request);

        if($form->isSubmitted()){
            $now = new \DateTime();
            $portfolio->setUpdated($now);
            $em = $doctrine->getManager();
            $em->persist($portfolio);
            $em->flush();
            return $this->redirectToRoute('dashboard');
        }

        return $this->render('form/index.html.twig', [
            'page_title' => 'Create New Portfolio',
            'form' => $form->createView(),
            'error' => "",
        ]);
    }

    #[Route('/portfolio/update/{id}', name: 'update_portfolio')]
    public function update(int $id, ManagerRegistry $doctrine, Request $request): JsonResponse
    {
        if ($request->isXMLHttpRequest()) {

            $portfolio = $doctrine->getRepository(Portfolio::class)->find($id);
            $user = $this->getUser();

            $settings = $user->getSettings();
    
            if( $portfolio === null ){
                return new JsonResponse(array('success' => false, 'message' => "Portfolio not found."));
            }
    
            if( $user->getId() !== $portfolio->getUser()->getId() ){
                return new JsonResponse(array('success' => false, 'message' => "Portfolio does not belong to user."));
            }

            $stocks = $portfolio->getStocks();
            $port_total_cost = 0;
            $worth = 0;
            $gain = 0;
            $total = 0;
            
            foreach($stocks as $stock)
            {
                $stock_shares = 0;
                $stock_average = 0;
                $stock_total_cost = 0;
                $stock_breakeven = 0;
                $stock_pl = 0;
                $fee = 0;
                $divi = 0;
                $buys = $stock->getShareBuys();


                foreach ($buys as $buy) {
                    if ($buy->getSold() < $buy->getAmount()){
                        $buy_shares = $buy->getAmount() - $buy->getSold();
                        $stock_total_cost += $buy_shares * $buy->getPrice();
                        $stock_shares += $buy_shares;
                    }
                }
                
                if ($stock_shares > 0) {
                    $stock_average = $stock_total_cost / $stock_shares;
                }
                

                if(!$stock->isNoFee()){
                    $fee = ((count($buys) * 9.95) + 9.95);
                }

                $stock_worth = $stock->getCurrentPrice() * $stock_shares;
                $stock_breakeven = $fee + $stock_total_cost;
                $stock_pl = $stock_worth - $stock_breakeven;

                $port_total_cost += ($fee + $stock_total_cost);
                $worth += $stock_worth;
                $gain += $stock_pl;
                
                foreach($stock->getDividends() as $dividend){
                    $divi += $dividend->getAmount();
                }
                
                $total += $divi;
            }

            $total += $worth;

            // update database

            // get date next day..
            $test = [];
            $last = $portfolio->getUpdated();
            $today = new \DateTime();

            $today_hour = $today->format('H');

            $last_day = $last->format('D');
            $today_day = $today->format('D');

            $last_week = $last->format('W');
            $today_week = $today->format('W');

            $last_month = $last->format('M');
            $today_month = $today->format('M');

            $last_year = $last->format('Y');
            $today_year = $today->format('Y');

            $weekend_today = ($today_day === "Sat" || $today_day === "Sun" || ($today_day === "Mon" && $today_hour < 9));
            $marketClosedForDay = (($weekend_today && $settings->isPortfolioUpdateOnWeekend()) || (!$weekend_today && $today_hour > 16));

            // $test['closedforday'] = $marketClosedForDay;
            // $test['weekend'] = $weekend_today;
            // $test['hour'] = $today_hour;

            if($marketClosedForDay){
                $test['today'] = 'abc';
                if($last_day !== $today_day){
                    $portfolio->setYesterday($portfolio->getWorth());
                }
    
                if($last_week !== $today_week){
                    $portfolio->setLastWeek($portfolio->getWorth());
                }

                if($last_month !== $today_month){
                    $portfolio->setLastMonth($portfolio->getWorth());
                }

                if($last_year !== $today_year){
                    $portfolio->setLastYear($portfolio->getWorth());
                }

                $portfolio->setUpdated($today);
                $portfolio->setWorth($worth);
                $em = $doctrine->getManager();
                $em->flush();
            }

            $portStats = [
                'worth' => number_format($worth,2),
                'gain' => number_format($gain,2),
                'gainP' => number_format((($worth) - ($port_total_cost)) / ($port_total_cost) * 100, 2, '.', ''),
                'day' => number_format($worth -  (float)$portfolio->getYesterday(),2, '.', ''),
                'dayP' => number_format((($worth) - ((float)$portfolio->getYesterday())) / ((float)$portfolio->getYesterday()) * 100, 2, '.', ''),
                'week' => number_format($worth - (float)$portfolio->getLastWeek(),2, '.', ''),
                'weekP' => number_format((($worth) - ((float)$portfolio->getLastWeek())) / ((float)$portfolio->getLastWeek()) * 100, 2, '.', ''),
                'month' => number_format($worth -  (float)$portfolio->getLastMonth(),2, '.', ''),
                'monthP' => number_format((($worth) - ((float)$portfolio->getLastMonth())) / ((float)$portfolio->getLastMonth()) * 100, 2, '.', ''),
                'year' => number_format($worth - (float)$portfolio->getLastYear(),2, '.', ''),
                'yearP' => number_format((($worth) - ((float)$portfolio->getLastYear())) / ((float)$portfolio->getLastYear()) * 100, 2,'.', ''),
                'total' => number_format($total - ($port_total_cost),2,'.', ''),
                'totalP' => number_format((($total) - ($port_total_cost)) / ($port_total_cost) * 100, 2,'.', ''),
            ];

            return new JsonResponse(array('success' => true, 'port' => $portStats, 'test' => $test));
        }
    }
}
