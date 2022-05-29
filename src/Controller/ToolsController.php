<?php

namespace App\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\TenPercentPlanWeek;
use App\Form\UserPercentType;

class ToolsController extends AbstractController
{
    // #[Route('/tools', name: 'app_tools')]
    // public function index(): Response
    // {
    //     return $this->render('tools/index.html.twig', [
    //         'controller_name' => 'ToolsController',
    //     ]);
    // }

    #[Route('/tools/daytradecalc', name: 'tools_daytrade_calc')]
    public function daytrade(): Response
    {
        $user = $user = $this->getUser();
        return $this->render('tools/daytrade_calc.html.twig', [
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

    public static function updateTenPercentPlan($user, $doctrine)
    {
        $weeks = $user->getTenPercentPlanWeeks()->getValues();
        

        $enddate = (count($weeks) === 0) ? $user->getTenPercentStartDate()->modify('+1 week')->modify('+16 hours') : end($weeks)->getWeekEnds();
        $now = new \DateTime();

        if($now > $enddate){
            $current_total = $user->getWallet()->getUSD();
            $start_amount = (count($weeks) === 0) ? $user->getTenPercentStartAmount() : end($weeks)->getTotal();
            $target = ($start_amount / 10);
            $total_target = $start_amount + $target;
            $change_amount = $current_total - $start_amount;

            dump($current_total);
            dump($start_amount);
            dump($target);
            dump($total_target);
            dump($change_amount);
            
        
            // dates
            $weekenddate = $enddate;
            $weekenddate = $weekenddate->format('M jS Y');
            $weekstartdate = $enddate;
            $weekstartdate = $weekstartdate->modify('-4 days')->format('M jS Y');
            $nextweekenddate = new \DateTime($weekenddate);
            $nextweekenddate = $nextweekenddate->modify('+1 week');
            $date = $weekstartdate . " - " . $weekenddate;


            $em = $doctrine->getManager();
            $week = new TenPercentPlanWeek();
            $week->setUser($user);
            $week->setWeek(count($weeks) + 1);
            $week->setDate($date);
            $week->setAdded($change_amount);
            $week->setTarget($start_amount / 10);
            $week->setTotal($start_amount + $change_amount);
            $week->setTotalTarget($start_amount + ($start_amount / 10));
            $week->setPercent((($current_total - $start_amount) / $start_amount) * 100);
            $week->setNextWeekTarget($current_total + ($current_total / 10));
            $week->setWeekEnds($nextweekenddate);

            $em->persist($week);
            $em->flush();
        }
    }
}
