<?php

namespace App\Controller;

use App\Entity\FuturesDay;
use App\Form\FuturesDayType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\TenPercentPlanWeek;
use App\Form\UserPercentType;
use App\Repository\TenPercentPlanWeekRepository;

class ToolsController extends AbstractController
{
    // #[Route('/tools', name: 'app_tools')]
    // public function index(): Response
    // {
    //     return $this->render('tools/index.html.twig', [
    //         'controller_name' => 'ToolsController',
    //     ]);
    // }

    #[Route('/tools/optionstradecalc', name: 'tools_options_daytrade_calc')]
    public function daytrade(): Response
    {
        $user = $user = $this->getUser();
        return $this->render('tools/options_daytrade_calc.html.twig', [
            'controller_name' => 'ToolsController',
        ]);
    }

    #[Route('/tools/futurestradecalc', name: 'tools_futures_daytrade_calc')]
    public function furturesdaytrade(): Response
    {
        $user = $this->getUser();
        $f_day_form = new FuturesDay();
        $date = new \DateTime();
        $f_day_form->setDate($date);
        $form = $this->createForm(FuturesDayType::class, $f_day_form, ['action' => $this->generateUrl('app_futures_create_play'), 'method' => 'POST']);
        return $this->render('tools/futures_daytrade_calc.html.twig', [
            'controller_name' => 'ToolsController',
            'day_form' => $form->createView(),
        ]);
    }

    #[Route('/tools/articles', name: 'tools_articles')]
    public function articles(): Response
    {
        return $this->render('tools/articles.html.twig', [
            'controller_name' => 'ToolsController',
        ]);
    }

    #[Route('/tools/tenpercentplan', name: 'tools_ten_percent_plan')]
    public function ten(ManagerRegistry $doctrine, Request $request): Response
    {
        $target = 0;
        $total_target = 0;
        $date_format = 0;
        $time_format = 0;
        $user = $this->getUser();
        
        // Create form if the user has not set a start date and amount yet..
        $em = $doctrine->getManager();
        $form = $this->createForm(UserPercentType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            return $this->redirectToRoute('tools_ten_percent_plan');
        }

        $form = $form->createView();
        
        // if user has set start date and amount, calculate what the target is and when the first deadline/ check will be.
        if($start_amount = $user->getTenPercentStartAmount()){
            $target = ($start_amount / 10);
            $total_target = $start_amount + $target;
            $date = $user->getTenPercentStartDate();
            $date->modify('+1 week');
            $date->modify('+16 hours');
            $date_format = $date->format('F jS');
            $time_format = $date->format('h:i A');
            $form = null;
        }
        
        // if user already has done the first week, show all current weeks as a table..
        $weeks = $user->getTenPercentPlanWeeks();
        

        return $this->render('tools/ten.html.twig', [
            'weeks' => $weeks,
            'form' => $form,
            'total_target' => $total_target,
            'target' => $target,
            'date' => $date_format,
            'time' => $time_format,
        ]);
    }

    /**
     * Update the ten percent plan..
     *
     * @param [type] $user
     * @param [type] $doctrine
     * @return void
     */
    public static function updateTenPercentPlan($user, $doctrine)
    {
        if(!$user->getTenPercentStartDate()){
            return;
        }

        $em = $doctrine->getManager();
        $weeks = $em->getRepository(TenPercentPlanWeek::class)->findBy(['User' => $user->getId()]);
        $enddate = new \DateTime((count($weeks) > 0) ? end($weeks)->getWeekEnds()->format('M jS Y') : $user->getTenPercentStartDate()->modify('+1 week')->modify('+16 hours')->format('M jS Y'));
        $now = new \DateTime();

        if($now > $enddate){
            $current_total = $user->getWallet()->getUSD();
            $start_amount = (count($weeks) > 0) ? end($weeks)->getTotal() : $user->getTenPercentStartAmount();
            $target = ($start_amount / 10);
            $total_target = $start_amount + $target;
            $change_amount = $current_total - $start_amount;
                    
            // dates
            $weekenddate = new \DateTime($enddate->format('M jS Y'));
            $weekstartdate = new \DateTime($enddate->format('M jS Y'));
            $nextweekenddate = new \DateTime($enddate->format('M jS Y'));

            $weekstartdate = $weekstartdate->modify('-4 days');
            $nextweekenddate = $nextweekenddate->modify('+1 week');
            $date = $weekstartdate->format('M jS Y') . " - " . $weekenddate->format('M jS Y');


            // Create new week entity with this weeks result..
            $week = new TenPercentPlanWeek();
            $week->setUser($user);
            $week->setWeek(count($weeks) + 1);
            $week->setDate($date);
            $week->setAdded($change_amount);
            $week->setTarget($target);
            $week->setTotal($start_amount + $change_amount);
            $week->setTotalTarget($total_target);
            $week->setPercent((($current_total - $start_amount) / $start_amount) * 100);
            $week->setNextWeekTarget($current_total + ($current_total / 10));
            $week->setWeekEnds($nextweekenddate);
            
            // Submit to the database..
            $em->persist($week);
            $em->flush();

            // Now check again, incase it been more than 2 weeks..
            ToolsController::updateTenPercentPlan( $user, $doctrine );
        }
    }
}
