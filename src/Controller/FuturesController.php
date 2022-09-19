<?php

namespace App\Controller;

use App\Entity\FuturesBuckets;
use App\Entity\FuturesDay;
use App\Form\FuturesBucketsType;
use App\Form\FuturesDayType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class FuturesController extends AbstractController
{
    #[Route('/futures', name: 'app_futures')]
    public function index(): Response
    {
        $user = $user = $this->getUser();

        // create buckets entity if user does not have one
        if($user->getFuturesBuckets()->isEmpty()){
            $buckets = "NO BUCKETS";
            $buckets_form = new FuturesBuckets();
            $form = $this->createForm(FuturesBucketsType::class, $buckets_form, ['action' => $this->generateUrl('app_futures_create_buckets'), 'method' => 'POST',]);
            $view = $form->createView();
        } else {
            $buckets = $user->getFuturesBuckets()[0];
            $view = "NO FORM";
        }

        $plays = $user->getFuturesDays();
        
        return $this->render('futures/index.html.twig', [
            'buckets' => $buckets,
            'plays' => $plays,
            'form' => $view,
            'controller_name' => 'FuturesController',
        ]);
    }

    #[Route('/futures/createbuckets', name: 'app_futures_create_buckets')]
    public function futuresCreateBuckets(EntityManagerInterface $entityManager, Request $request): Response
    {   
        $buckets = new FuturesBuckets();
        $form = $this->createForm(FuturesBucketsType::class, $buckets);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $user = $user = $this->getUser();
            $buckets->setUser($user);
            $user->addFuturesBucket($buckets);
            $entityManager->persist($buckets);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_futures');
    }

    #[Route('/futures/makeplay', name: 'app_futures_create_play')]
    public function futuresCreatePlay(EntityManagerInterface $entityManager, Request $request): Response
    {
        $day = new FuturesDay();
        $date = new \DateTime();
        $day->setDate($date);
        $form = $this->createForm(FuturesDayType::class, $day);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $user = $user = $this->getUser();
            $day->setUser($user);
            
            
            // TODO:: Make these into settings..
            $play_max = 1000;
            $use_profit_split = true;
            $profit_split_ratio = [0.20, 0.25, 0.33, 0.50];
            
            // Remember this assumes a user already has buckets..
            $buckets = $user->getFuturesBuckets()[0];

            $data = $form->getData();
            $total = $data->getTotal(); // 250
            
            //Check if we made profit today..
            if($total > 0){

                $play_amount = $buckets->getPlay();
                if ($play_amount <= 400){$split_level = 0;} else if($play_amount <= 500){$split_level = 1;} else if($play_amount <= 750) {$split_level = 2;} else {$split_level = 3;}
                $profit_ratio = $profit_split_ratio[$split_level];
                $play_ratio = 1.00 - $profit_ratio;
                $split_play_total = $total * $play_ratio;
                $split_profit_total = $total * $profit_ratio;

                if($use_profit_split){
                    $play = $play_amount + $split_play_total;
                    $extra = $play - $play_max;
                    $play = $play - (($extra > 0) ? $extra : 0);  
                    $profit = $split_profit_total + (($extra > 0) ? $extra : 0);
                    $day->setPlay(($extra > 0) ? $split_play_total - $extra : $split_play_total);
                } else {
                    $play = $play_amount + $total;
                    $extra = $play - $play_max;
                    $play = $play - (($extra > 0) ? $extra : 0);  
                    $profit = (($extra > 0) ? $extra : 0);
                    $day->setPlay(($extra > 0) ? $total - $extra : $total);
                }

                $day->setProfit($profit);
                $buckets->setPlay($play);
                $buckets->addProfit($profit);
            
            // ... Fail to make money..
            } else {
                $day->setPlay($total);
                $day->setProfit(0);
                $buckets->lostPlayMoney(abs($total));
            }
            
            $entityManager->persist($day);
            $entityManager->flush();
        } else {
            dump($form->getErrors(true));
        }

        return $this->redirectToRoute('app_futures');
    }

    #[Route('/futures/emptyprofitbucket/{cdn}', name: 'app_futures_empty_profit_bucket')]
    public function futuresEmptyProfitBucket(EntityManagerInterface $entityManager, int $cdn): Response
    {
        $user = $user = $this->getUser();
        $buckets = $user->getFuturesBuckets()[0];
        $buckets->dumpProfitBucket($cdn);
        $entityManager->flush();
        return new JsonResponse(['result' => 'ok']);
    }
}
