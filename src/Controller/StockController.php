<?php

namespace App\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use App\Form\StockType;
use App\Form\ShareBuyType;
use App\Form\ShareSellType;
use App\Entity\Stock;
use App\Entity\ShareBuy;
use App\Entity\ShareSell;

class StockController extends AbstractController
{
    #[Route('/stocks', name: 'stocks')]
    public function index(ManagerRegistry $doctrine): Response
    {
        $user = $this->getUser();
        $stocks = $user->getStocks();

        foreach($stocks as $stock){
            $updated = false;
            $manual_update = false; // use this whgen the script is not working or you need a quick update..

            // check to see if has been 2 days or longer since last update
            $lasttimestamp =  $stock->getLastPriceUpdate()->getTimestamp();
            $timeDiff = abs(time() - $lasttimestamp);
            $numberDays = $timeDiff/86400;  // 86400 seconds in one day

            // and you might want to convert to integer
            $numberDays = intval($numberDays);
            $day = date('D', $lasttimestamp);
            $hour = date('H', $lasttimestamp);

            $day_today = date('D');
            $hour_today = date('H');
            
            if($numberDays > 2){
                if($stock->getCountry() == "CAN"){
                    $json = file_get_contents('https://www.alphavantage.co/query?function=TIME_SERIES_DAILY&symbol=' . $stock->getTicker() .'.TRT&outputsize=compact&apikey=9OH2YI0MYLGXGW30');
                    $data = json_decode($json,true);
                    $c_date = $data["Meta Data"]["3. Last Refreshed"];
                    $price = $data["Time Series (Daily)"][$c_date]["4. close"];
                    $stock->setCurrentPrice($price);
                }

                if($stock->getCountry() == "USD"){
                    // get current price
                    $result = file_get_contents("https://www.optionsprofitcalculator.com/ajax/getStockPrice?stock=". $stock->getTicker()  ."&reqId=0");
                    $price = json_decode($result)->price->last;
                    $stock->setCurrentPrice($price);
                }
                
                // update .. uh, the update time.
                $date = new \DateTime();
                $stock->setLastPriceUpdate($date);
                $em = $doctrine->getManager();
                $em->flush();
                $updated = true;
            }

            if(!$updated && $day != "Sat" && $day != "Sun") {
                // get current price
                
                if(!$updated && $hour >= 4 && $hour < 20) {
                    if($stock->getCountry() == "CAN"){
                        $json = file_get_contents('https://www.alphavantage.co/query?function=TIME_SERIES_DAILY&symbol=' . $stock->getTicker() .'.TRT&outputsize=compact&apikey=9OH2YI0MYLGXGW30');
                        $data = json_decode($json,true);
                        $c_date = $data["Meta Data"]["3. Last Refreshed"];
                        $price = $data["Time Series (Daily)"][$c_date]["4. close"];
                        $stock->setCurrentPrice($price);
                    }

                    if($stock->getCountry() == "USD"){
                        // make sure last update was during market hours.. otherwise there will be no difference..
                        $result = file_get_contents("https://www.optionsprofitcalculator.com/ajax/getStockPrice?stock=". $stock->getTicker()  ."&reqId=0");
                        $price = json_decode($result)->price->last;
                        $stock->setCurrentPrice($price);
                        //dump($result);
                    }
                    
                    // update .. uh, the update time.
                    $date = new \DateTime();
                    $stock->setLastPriceUpdate($date);
                    $em = $doctrine->getManager();
                    $em->flush();
                    $updated = true;
                }
            }
            
            // if the price was last checked on a weekend.. make sure the current time is during market hours..
            if(!$updated && $day_today != "Sat" && $day_today != "Sun") {
                if($hour_today >= 4 && $hour_today < 20) {
                    if($stock->getCountry() == "CAN"){
                        $json = file_get_contents('https://www.alphavantage.co/query?function=TIME_SERIES_DAILY&symbol=' . $stock->getTicker() .'.TRT&outputsize=compact&apikey=9OH2YI0MYLGXGW30');
                        $data = json_decode($json,true);
                        $c_date = $data["Meta Data"]["3. Last Refreshed"];
                        $price = $data["Time Series (Daily)"][$c_date]["4. close"];
                        $stock->setCurrentPrice($price);
                    }

                    if($stock->getCountry() == "USD"){
                        // get current price
                        $result = file_get_contents("https://www.optionsprofitcalculator.com/ajax/getStockPrice?stock=". $stock->getTicker()  ."&reqId=0");
                        $price = json_decode($result)->price->last;
                        $stock->setCurrentPrice($price);
                    }
                    
                    // update .. uh, the update time.
                    $date = new \DateTime();
                    $stock->setLastPriceUpdate($date);
                    $em = $doctrine->getManager();
                    $em->flush();
                    $updated = true;
                }
            }
            
            // manual update
            if(!$updated && $manual_update) {
                if($stock->getCountry() == "CAN"){
                    $json = file_get_contents('https://www.alphavantage.co/query?function=TIME_SERIES_DAILY&symbol=' . $stock->getTicker() .'.TRT&outputsize=compact&apikey=9OH2YI0MYLGXGW30');
                    $data = json_decode($json,true);
                    $c_date = $data["Meta Data"]["3. Last Refreshed"];
                    $price = $data["Time Series (Daily)"][$c_date]["4. close"];
                    $stock->setCurrentPrice($price);
                }
                
                if($stock->getCountry() == "USD"){
                    // get current price
                    $result = file_get_contents("https://www.optionsprofitcalculator.com/ajax/getStockPrice?stock=". $stock->getTicker()  ."&reqId=0");
                    $price = json_decode($result)->price->last;
                    $stock->setCurrentPrice($price);
                }
                 
                 // update .. uh, the update time.
                 $date = new \DateTime();
                 $stock->setLastPriceUpdate($date);
                 $em = $doctrine->getManager();
                 $em->flush();
                 $updated = true;
            }
        }  

        return $this->render('stock/index.html.twig', [
            'controller_name' => 'StockController',
            'stocks' => $stocks,
        ]);
    }

    #[Route('/stocks/add', name: 'stocks_add')]
    public function add(ManagerRegistry $doctrine, Request $request): Response
    {
        $stock = new Stock();
        $form = $this->createForm(StockType::class, $stock);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $stock->setUser($this->getUser());
            $stock->setEarned(0);
            $stock->setCurrentPrice(0);
            $date = new \DateTime();
            $stock->setLastPriceUpdate($date);
            $stock->setBgColor("ffffff");

            $em = $doctrine->getManager();
            $em->persist($stock);
            $em->flush();

            //return $this->redirectToRoute('stocks_buy_shares');
            return $this->redirectToRoute('stocks_add');
        }

        return $this->render('form/index.html.twig', [
            'error' => "",
            'form' => $form->createView(),
        ]);
    }

    #[Route('/stocks/shares/buy', name: 'stocks_buy_shares')]
    public function buyShares(ManagerRegistry $doctrine, Request $request): Response
    {
        $share_buy = new ShareBuy();
        $form = $this->createForm(ShareBuyType::class, $share_buy);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $share_buy->setSold(0);
            $em = $doctrine->getManager();
            $em->persist($share_buy);
            $em->flush();

            return $this->redirectToRoute('stocks_buy_shares');
        }

        return $this->render('form/index.html.twig', [
            'error' => "",
            'form' => $form->createView(),
        ]);
    }

    #[Route('/stocks/shares/sell', name: 'stocks_sell_shares')]
    public function sellShares(ManagerRegistry $doctrine, Request $request): Response
    {
        $error = "";
        $share_sell = new ShareSell();
        $form = $this->createForm(ShareSellType::class, $share_sell);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $em = $doctrine->getManager();

            $shareBuys = $data->getStock()->getShareBuys()->getValues();

            usort($shareBuys, [$this,"sort_buys_by_price"]);
           //dump($shareBuys);
            $amount_to_sell = $data->getAmount();
            //dump($amount_to_sell);
            $buys_length = count($shareBuys);
            $current_buy = 0;
            //dump($buys_length);

            //loop though share buys and remove 
            while($amount_to_sell > 0){
                // make sure we have enough shares to sell...
                if($current_buy == $buys_length){
                    $amount_to_sell = 0;
                   //dump("Current buy:" . $current_buy);
                    return $this->render('form/index.html.twig', [
                        'error' => 'You dont own enough of these shares',
                        'form' => $form->createView(),
                    ]);
                }

                $a = (int)$shareBuys[$current_buy]->getAmount() - $shareBuys[$current_buy]->getSold();
                //dump("shareBuy Amount:" . $a);
                
                // if we have enough shares in the earlist buy to cover sell
                if($a >= $amount_to_sell){
                   //dump("A");
                    $shareBuys[$current_buy]->addSold($amount_to_sell);
                    $amount_to_sell = 0;
                    $em->persist($shareBuys[$current_buy]);
                } else {
                    //dump("B");
                    $shareBuys[$current_buy]->addSold($a);
                    $amount_to_sell -= $a;
//                    dump("Amount:" . $amount_to_sell);
                    $current_buy++;
                }
            }

            $em->persist($share_sell);
            $em->flush();

            return $this->redirectToRoute('stocks_sell_shares');
        }

        return $this->render('form/index.html.twig', [
            'error' => $error,
            'form' => $form->createView(),
        ]);
    }


    private function sort_buys_by_price($a, $b) {
        if($a->getPrice() == $b->getPrice()){ return 0; }
        return ($a->getPrice() < $b->getPrice()) ? -1 : 1;
    }
}
