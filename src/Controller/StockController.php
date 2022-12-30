<?php

namespace App\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use App\Form\OptionType;
use App\Form\OptionBuyType;
use App\Form\OptionSellType;
use App\Form\StockType;
use App\Form\ShareBuyType;
use App\Form\ShareSellType;
use App\Entity\Option;
use App\Entity\Stock;
use App\Entity\ShareBuy;
use App\Entity\ShareSell;
use App\Entity\Transaction;
use App\Entity\Wallet;

class StockController extends AbstractController
{
    private function updateStockInfo(Stock $stock, $disable_can, $day_today, $hour_today)
    {
        if($stock->getCountry() == "CAN"){
            if(!$disable_can){
                if($day_today != "Sat" && $day_today != "Sun" && $hour_today >= 9 && $hour_today < 16) {
                    $json = file_get_contents('https://www.alphavantage.co/query?function=TIME_SERIES_DAILY_ADJUSTED&symbol=' . $stock->getTicker() .'.TRT&outputsize=compact&apikey=9OH2YI0MYLGXGW30');
                    dump("Update:" . $stock->getTicker());
                    //dump($json);
                    $data = json_decode($json,true);
                    $c_date = $data["Meta Data"]["3. Last Refreshed"];
                    $price = $data["Time Series (Daily)"][$c_date]["4. close"];
                    $stock->setCurrentPrice($price);
                } else {
                    dump("No Update, Canadian Stocks can only be updated when market is closed");
                }
            }
        }

        if($stock->getCountry() == "USD"){
            // get current price
            $result = file_get_contents("https://www.optionsprofitcalculator.com/ajax/getStockPrice?stock=". $stock->getTicker()  ."&reqId=0");
            dump("Update:" . $stock->getTicker());
            //dump($result);
            $price = json_decode($result)->price->last;
            $stock->setCurrentPrice($price);
            $date = new \DateTime();
            
            if(count($stock->getOptions()) > 0){
                foreach ($stock->getOptions() as $option) {
                    if(!$option->isExpired()){
                        if($option->getExpiry() < $date){
                            $option->setExpired(true);
                            continue;
                        }
                        $e = date_format($option->getExpiry(), "Y-m-d");
                        $t = ($stock->getType() === 1) ? "c":"p";
                        $s = number_format($option->getStrike(), 2);
                        $option_data = json_decode(file_get_contents('https://www.optionsprofitcalculator.com/ajax/getOptions?stock=' . $stock->getTicker() . '&reqId=1'), true);
                        $option_data = $option_data['options'];
                        $option_data = $option_data[$e];
                        $option_data = $option_data[$t];
                        $option_data = $option_data[$s];
                        $current = $option_data['b'];
                        $option->setCurrent($current);
                    }
                }
            }
        }
    }

    #[Route('/stocks', name: 'stocks')]
    public function index(Request $request, ManagerRegistry $doctrine): Response
    {
        $user = $this->getUser();
        $stocks = $user->getStocks();
        $settings = $user->getSettings();

        foreach($stocks as $stock){
            $updated = false;
            $manual_update = $settings->isStocksManualUpdateEnabled(); // use this when the script is not working or you need a quick update..
            $disable_update = $settings->isStocksDisableUpdateEnabled(); 
            $disable_can = $settings->isStocksDisableCanadianUpdateEnabled(); 

            if($request->query->get('disable_update')){
                $updated = true;
            }

            if($disable_update){$updated = true;}

            // check if stock has been delisted..
            if($stock->isDelisted()){
                continue;
            }
            
            if(!$stock->isBeingPlayed() && !$settings->isStocksUpdateSoldPrice()){
                continue;
            }


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
            
            if(!$updated && $numberDays > 2){
                $this->updateStockInfo($stock,$disable_can,$day_today,$hour_today);
                $date = new \DateTime();
                $stock->setLastPriceUpdate($date);
                $em = $doctrine->getManager();
                $em->flush();
                $updated = true;
            }

            if(!$updated && $day != "Sat" && $day != "Sun") {
                // get current price
                
                if(!$updated && $hour >= 4 && $hour < 20) {
                    $this->updateStockInfo($stock,$disable_can,$day_today,$hour_today);
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
                    $this->updateStockInfo($stock,$disable_can,$day_today,$hour_today);
                    $date = new \DateTime();
                    $stock->setLastPriceUpdate($date);
                    $em = $doctrine->getManager();
                    $em->flush();
                    $updated = true;
                }
            }
            
            // manual update
            if(!$updated && $manual_update) {
                $this->updateStockInfo($stock,$disable_can,$day_today,$hour_today);
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
            $stock->setDelisted(false);
            $stock->setBeingPlayed(false);
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
            $em = $doctrine->getManager();
            $data = $form->getData();
            $cost = $form->get("cost")->getData();
            $currency = $form->get("payment_currency")->getData();
            $share_buy->setSold(0);
            $user = $share_buy->getStock()->getUser();
            $wallet = $em->getRepository(Wallet::class)->find($user->getId());

            $transaction = new Transaction();
            $date = new \DateTime();
            $transaction->setType(2);
            $transaction->setDate($date);
            $transaction->setUser($user);
            $word = ($share_buy->getAmount() === 1) ? 'Share' : 'Shares';
            $transaction->setName('Bought' . ' ' . $share_buy->getAmount() . ' ' . $share_buy->getStock()->getTicker() . ' ' . $word);
            
            if ($currency == 'can'){
                $wallet->withdraw('CAN', $cost);
                $transaction->setCurrency(1);
            }

            if ($currency == 'usd'){
                $wallet->withdraw('USD', $cost);
                $transaction->setCurrency(2);
            }
            
            $transaction->setAmount($cost);

            $em->persist($transaction);
            $em->persist($share_buy);
            $em->flush();

            return $this->redirectToRoute('stocks');
        }

        return $this->render('form/share_buy.html.twig', [
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

            $amount_to_sell = $data->getAmount();
 
            $buys_length = count($shareBuys);
            $current_buy = 0;

            //loop though share buys and remove 
            while($amount_to_sell > 0){
                if($current_buy == $buys_length){
                    $amount_to_sell = 0;
                    return $this->render('form/index.html.twig', [
                        'error' => 'You dont own enough of these shares',
                        'form' => $form->createView(),
                    ]);
                }

                $a = (int)$shareBuys[$current_buy]->getAmount() - $shareBuys[$current_buy]->getSold();
                
                // if we have enough shares in the earlist buy to cover sell
                if($a >= $amount_to_sell){
                    $shareBuys[$current_buy]->addSold($amount_to_sell);
                    $amount_to_sell = 0;
                    $em->persist($shareBuys[$current_buy]);
                } else {
                    $shareBuys[$current_buy]->addSold($a);
                    $amount_to_sell -= $a;
                    $current_buy++;
                }
            }

            $user = $share_sell->getStock()->getUser();
            $wallet = $em->getRepository(Wallet::class)->find($user->getId());

            $transaction = new Transaction();
            $date = new \DateTime();
            $transaction->setType(1);
            $transaction->setDate($date);
            $transaction->setUser($user);
            $word = ($data->getAmount() === 1) ? 'Share' : 'Shares';
            $transaction->setName('Sold' . ' ' . $data->getAmount() . ' ' . $share_sell->getStock()->getTicker() . ' ' . $word);

            $cost = $form->get("cost")->getData();
            $currency = $form->get("payment_currency")->getData();
            
            if ($currency == 'can'){
                $wallet->deposit('CAN', $cost);
                $transaction->setCurrency(1);
            }

            if ($currency == 'usd'){
                $wallet->deposit('USD', $cost);
                $transaction->setCurrency(2);
            }
            
            $transaction->setAmount($cost);

            $em->persist($transaction);
            $em->persist($share_sell);
            $em->flush();

            return $this->redirectToRoute('stocks_sell_shares');
        }

        return $this->render('form/share_sell.html.twig', [
            'error' => $error,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/stocks/options/add', name: 'stocks_add_option')]
    public function addOption(ManagerRegistry $doctrine, Request $request): Response
    {
        $error = "";
        $option = new Option();
        $form = $this->createForm(OptionType::class, $option);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $em = $doctrine->getManager();

            $user = $option->getStock()->getUser();
            $wallet = $em->getRepository(Wallet::class)->find($user->getId());


            $option->setBuys(1);
            $option->setExpired(false);
            $option->setUser($user);
            $option->setCurrent(0.0);
            $transaction = new Transaction();
            $date = new \DateTime();
            $transaction->setType(2);
            $transaction->setDate($date);
            $transaction->setUser($user);
            $word = ($form->get("contracts")->getData() === 1) ? 'Contract' : 'Contracts';
            $option_type = ($form->get("type")->getData() === 1) ? 'C' : 'P';
            $option_strike = $form->get("strike")->getData();
            $option_expiry = date_format($form->get("expiry")->getData(), 'Y-m-d');
            $transaction->setName('Bought' . ' ' . $form->get("contracts")->getData() . ' ' . $option->getStock()->getTicker() . ' ' . $option_strike . $option_type . ' ' . $option_expiry . ' ' . $word);
            
            $option->setName($option->getStock()->getTicker() . ' ' . $option_strike . $option_type . ' ' . $option_expiry);
            //dump($transaction->getName());

            $cost = $form->get("cost")->getData();
            $currency = $form->get("payment_currency")->getData();
            
            if ($currency == 'can'){
                $wallet->withdraw('CAN', $cost);
                $transaction->setCurrency(1);
            }

            if ($currency == 'usd'){
                $wallet->withdraw('USD', $cost);
                $transaction->setCurrency(2);
            }

            $transaction->setAmount($cost);

            $option->getStock()->subtractEarned($cost);

            $em->persist($transaction);
            $em->persist($option);
            $em->flush();
            return $this->redirectToRoute('dashboard');
        }

        return $this->render('form/option_add.html.twig', [
            'error' => $error,
            'form' => $form->createView(),
        ]);
    }


    #[Route('/stocks/options/buy', name: 'stocks_buy_option')]
    public function buyOption(ManagerRegistry $doctrine, Request $request): Response
    {
        $error = "";
        $form = $this->createForm(OptionBuyType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $doctrine->getManager();
            $data = $form->getData();
            $option = $form->get("option")->getData();

            $user = $option->getStock()->getUser();
            $wallet = $em->getRepository(Wallet::class)->find($user->getId());
            
            $contracts = $option->getContracts() + $form->get("contracts")->getData();
            $average = (($option->getAverage() * $option->getContracts()) + ($form->get("contracts")->getData() * $form->get("average")->getData())) / $contracts;
            

            $option->setAverage($average);
            $option->setContracts($contracts);
            $option->setBuys((int)$option->getBuys() + 1);
            $transaction = new Transaction();
            $date = new \DateTime();
            $transaction->setType(2);
            $transaction->setDate($date);
            $transaction->setUser($user);
            $word = ($option->getContracts() === 1) ? 'Contract' : 'Contracts';
            $option_type = ($option->getType() === 1) ? 'C' : 'P';
            $option_strike = $option->getStrike();
            $option_expiry = date_format($option->getExpiry(), 'Y-m-d');
            $transaction->setName('Bought' . ' ' . $form->get("contracts")->getData() . ' ' . $option->getStock()->getTicker() . ' ' . $option_strike . $option_type . ' ' . $option_expiry . ' ' . $word);

            //dump($transaction->getName());

            $cost = $form->get("cost")->getData();
            $currency = $form->get("payment_currency")->getData();
            
            if ($currency == 'can'){
                $wallet->withdraw('CAN', $cost);
                $transaction->setCurrency(1);
            }

            if ($currency == 'usd'){
                $wallet->withdraw('USD', $cost);
                $transaction->setCurrency(2);
            }

            $transaction->setAmount($cost);

            $option->getStock()->subtractEarned($cost);

            $em->persist($transaction);
            $em->flush();
            return $this->redirectToRoute('dashboard');
        }

        return $this->render('form/option_buy.html.twig', [
            'error' => $error,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/stocks/options/sell', name: 'stocks_sell_option')]
    public function sellOption(ManagerRegistry $doctrine, Request $request): Response
    {
        $error = "";
        $form = $this->createForm(OptionSellType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $em = $doctrine->getManager();
            $option = $form->get("option")->getData();

            $user = $option->getStock()->getUser();
            $wallet = $em->getRepository(Wallet::class)->find($user->getId());
            
            $option->setContracts($option->getContracts() - $form->get("contracts")->getData());
            $transaction = new Transaction();
            $date = new \DateTime();
            $transaction->setType(1);
            $transaction->setDate($date);
            $transaction->setUser($user);
            $word = ($option->getContracts() === 1) ? 'Contract' : 'Contracts';
            $option_type = ($option->getType() === 1) ? 'C' : 'P';
            $option_strike = $option->getStrike();
            $option_expiry = date_format($option->getExpiry(), 'Y-m-d');
            $transaction->setName('Sold' . ' ' . $form->get("contracts")->getData() . ' ' . $option->getStock()->getTicker() . ' ' . $option_strike . $option_type . ' ' . $option_expiry . ' ' . $word);

            //dump($transaction->getName());

            $total = $form->get("total")->getData();
            $currency = $form->get("payment_currency")->getData();
            
            if ($currency == 'can'){
                $wallet->deposit('CAN', $total);
                $transaction->setCurrency(1);
            }

            if ($currency == 'usd'){
                $wallet->deposit('USD', $total);
                $transaction->setCurrency(2);
            }

            $transaction->setAmount($total);

            $option->getStock()->addEarned($total);


            $em->persist($transaction);
            $em->persist($option);
            $em->flush();
            return $this->redirectToRoute('dashboard');
        }

        return $this->render('form/option_sell.html.twig', [
            'error' => $error,
            'form' => $form->createView(),
        ]);
    }

    private function sort_buys_by_price($a, $b)
    {
        if($a->getPrice() == $b->getPrice()){ return 0; }
        return ($a->getPrice() < $b->getPrice()) ? -1 : 1;
    }
}
