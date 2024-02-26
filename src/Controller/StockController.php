<?php

namespace App\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use App\Form\OptionType;
use App\Form\OptionBuyType;
use App\Form\OptionSellType;
use App\Form\StockType;
use App\Form\ShareBuyType;
use App\Form\ShareSellType;
use App\Form\WrittenOptionType;
use App\Entity\Atom;
use App\Entity\WrittenOption;
use App\Entity\Option;
use App\Entity\Settings;
use App\Entity\Stock;
use App\Entity\ShareBuy;
use App\Entity\ShareSell;
use App\Entity\Transaction;
use App\Entity\Wallet;
use App\Repository\StockRepository;
use App\Repository\AtomRepository;

class StockController extends AbstractController
{
    private $can_count = 0;
    private $settings;
    private $update_price = 0;
    private $can_updated = false;
    private $update_function = false;
    private $test_string = "";

    private function updateStockInfo($doctrine, Stock $stock, $disable_can, $day_today, $hour_today)
    {
        $this->update_function = true;
        if($stock->getCountry() == "CAN"){
            dump("Debug Dump #2 - Update Canadian Stock");
            if(!$disable_can && $stock->isBeingPlayedShares()){
                dump("Debug Dump #3 - Stock updates is enabled and we have shares");
                if($day_today != "Sat" && $day_today != "Sun" && $hour_today >= 10 && $hour_today < 16) {
                    dump("No Update, Canadian Stocks can only be updated when market is closed");
                    return "C1";
                } else {
                    $atomCount = $doctrine->getRepository(Atom::class)->findOneBy(array('name' => 'CanUpdateCount'));
                    $atomDate = $doctrine->getRepository(Atom::class)->findOneBy(array('name' => 'CanUpdateDate'));
                    $this->can_count = (int)$atomCount->getValue();
                    
                    // check how long it has been since last update
                    dump("Debug Dump #4 - This CDN stock has not been updated yet today");

                    if($this->can_count <= $this->settings->getStocksCanadianUpdateAmountLimit()){
                        dump("Debug Dump #5 - API update has limit not been reached ");
                        $this->can_updated = true;
                        // Call API, to get info..
                        $json = file_get_contents('https://www.alphavantage.co/query?function=TIME_SERIES_DAILY&symbol=' . $stock->getTicker() .'.TRT&outputsize=compact&apikey=9OH2YI0MYLGXGW30');
                        dump("Update:" . $stock->getTicker());
                        $this->can_count++;
                        $date = new \DateTime('now');
                        $now = $date->format('Y-m-d H:i:s');
                        $atomCount->setValue($this->can_count);
                        $atomDate->setValue($now);
                        $em = $doctrine->getManager();
                        $em->flush();
                        
                        $data = json_decode($json,true);
                        if(!$md = $data["Meta Data"]){
                            return "C3";
                        }

                        $c_date = $data["Meta Data"]["3. Last Refreshed"];


                        if($c_date){
                            $price = $data["Time Series (Daily)"][$c_date]["4. close"];
                            $old_price = $stock->getCurrentPrice();
                            $stock->setCurrentPrice($price);
                            $this->update_price = $price;


                            $stock->setCurrentPrice($price);
                            $this->update_price = $price;

                            $last = $stock->getLastPriceUpdate();
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
                            $marketClosedForDay = (($weekend_today && $this->settings->isStockUpdateOnWeekend()) || (!$weekend_today && $today_hour > 16));

                            if($marketClosedForDay) {
                                if ($last_day !== $today_day) {
                                    $stock->setPriceYesterday($old_price);
                                }

                                if ($last_week !== $today_week) {
                                    $stock->setPriceWeek($old_price);
                                }

                                if ($last_month !== $today_month) {
                                    $stock->setPriceMonth($old_price);
                                }

                                if ($last_year !== $today_year) {
                                    $stock->setPriceYear($old_price);
                                }
                            }

                            $em = $doctrine->getManager();
                            $em->flush();
                            return "U";
                        } else {
                            return "C4";
                        }
                        
                    } else {
                        // check if it has been longer than 10 minutes
                        $lastUpdate = \DateTime::createFromFormat('Y-m-d H:i:s', $atomDate->getValue());
                        $now = new \DateTime('now');
                        $interval = $lastUpdate->diff($now);
                        $minutes = (int)$interval->format('%i');

                        if($minutes >= $this->settings->getStocksCanadianUpdateLimitTimeout()){
                            $atomCount->setValue(0);
                            $em = $doctrine->getManager();
                            $em->flush();
                            return $this->updateStockInfo($doctrine, $stock, $disable_can, $day_today, $hour_today);
                        }


                        //updateStockInfo($doctrine, $stock, $disable_can, $day_today, $hour_today);

                        dump($stock->getTicker() . ": No Update, can only do 5 Candaian stock updates per minute");
                        return "C2";
                    }
                }
            }

            else{
                return "C5";
            }
        }

        if($stock->getCountry() == "USD"){
            // get current price
            if($stock->isBeingPlayedShares()){
                $result = file_get_contents("https://www.optionsprofitcalculator.com/ajax/getStockPrice?stock=". $stock->getTicker()  ."&reqId=0");
                dump("Update:" . $stock->getTicker());
                //dump($result);
                $price = json_decode($result)->price->last;
                $old_price = $stock->getCurrentPrice();
                $stock->setCurrentPrice($price);
                $this->update_price = $price;

                $last = $stock->getLastPriceUpdate();
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
                $marketClosedForDay = (($weekend_today && $this->settings->isStockUpdateOnWeekend()) || (!$weekend_today && $today_hour > 16));

                if($marketClosedForDay) {
                    if ($last_day !== $today_day) {
                        $stock->setPriceYesterday($old_price);
                    }

                    if ($last_week !== $today_week) {
                        $stock->setPriceWeek($old_price);
                    }

                    if ($last_month !== $today_month) {
                        $stock->setPriceMonth($old_price);
                    }

                    if ($last_year !== $today_year) {
                        $stock->setPriceYear($old_price);
                    }
                }
            }

            if($stock->isBeingPlayedOption() && count($stock->getOptions()) > 0){
                foreach ($stock->getOptions() as $option) {
                    if(!$option->isExpired()){
                        $date = new \DateTime();
                        if($option->getExpiry() < $date){
                            $option->setExpired(true);
                            $option->getStock()->setBeingPlayedOption(false);
                            continue;
                        }
                        $e = date_format($option->getExpiry(), "Y-m-d");
                        $t = ((int)$stock->getType() === 1) ? "c":"p";
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

            return "U";
        }

        dump("Debug Dump #1 - UpdateStock Info Function has run and reached the end without a return");
    }

    #[Route('/stocks', name: 'stocks')]
    public function index(Request $request, ManagerRegistry $doctrine): Response
    {
        $user = $this->getUser();
        $stocks = $user->getStocks();
        $settings = $user->getSettings();
        $update_status = "";
        $update_status_stock = "";
        $manual_update = $settings->isStocksManualUpdateEnabled(); // use this when the script is not working or you need a quick update.. 
        $disable_can = $settings->isStocksDisableCanadianUpdateEnabled(); 
        $disable_update = $settings->isStocksDisableUpdateEnabled();
        $em = $doctrine->getManager();
        $em->flush();


        if($request->query->get('disable_update')){
            $disable_update = true;
            $update_status = "Update Disabled by URI variable.";
        } else {
            if(!$disable_update) {
                if($manual_update) {$update_status .= "Manual Update.";}
                if($disable_can){$update_status .= "CAN disabled in settings.";}
            } else {
                $update_status = "Update Disabled by Settings";
            }
        }



        $update_status .= $update_status_stock;
        
        $em = $doctrine->getManager();
        $em->flush();

        return $this->render('stock/index.html.twig', [
            'controller_name' => 'StockController',
            'update_disabled' => $disable_update,
            'status' => $update_status,
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
            $stock->setBeingPlayedShares(true);
            $stock->setBeingPlayedOption(false);
            $stock->setSharesOwned(0);
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
            
            if($share_buy->getStock()->isNoFee()){
                $cost = $share_buy->getAmount() * $share_buy->getPrice();
            } else {
                $cost = $form->get("cost")->getData();
            }
            $currency = $form->get("payment_currency")->getData();
            $share_buy->setSold(0);
            $share_buy->getStock()->setBeingPlayedShares(1);
            $share_buy->getStock()->addSharesOwned($share_buy->getAmount());
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

            return $this->redirectToRoute('dashboard');
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
            $totalSharesLeft = 0;

            foreach($shareBuys as $sb){
                $bought = $sb->getAmount();
                $sold = $sb->getSold();
                if($bought > $sold){
                    $totalSharesLeft += ($bought - $sold);
                }
            }

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
            
            dump($amount_to_sell);
            dump($totalSharesLeft);

            // if we are selling the last amount of shares we own (or all of them), remove stock from the playing list.. 
            if($amount_to_sell === $totalSharesLeft){
                $data->getStock()->setBeingPlayedShares(false);
            } else {
                $data->getStock()->setSharesOwned($totalSharesLeft - $amount_to_sell);
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
            $option->getStock()->setBeingPlayedOption(true);

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
            $option->getStock()->setBeingPlayedOption(true);

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

            if($option->getContracts() === 0){
                $option->getStock()->setBeingPlayedOption(false);
            }


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

    #[Route('stocks/writtenoptions', name: 'stocks_written_options')]
    public function writtenOptions(ManagerRegistry $doctrine, Request $request): Response
    {
        $user = $this->getUser();
        $options = $user->getWrittenOptions();

        //loop though all the calls and see if any have expired
        $today = new \DateTime();
        $today->modify('-1 day');
        $em = $doctrine->getManager();

//        foreach($coveredCalls as $cc)
//        {
//            if($today > $cc->getExpiry()){
//                $cc->setExpired(true);
//                $em->flush();
//            }
//        }

        return $this->render('stock/writtenoptions.html.twig', [
            'controller_name' => 'StockController',
            'written_options' => $options,
        ]);
    }

    #[Route('/stocks/writtenoptions/write', name: 'stocks_writtenoptions_write')]
    public function sellWrittenOption(ManagerRegistry $doctrine, Request $request): Response
    {
        $user = $this->getUser();
        $error = "";
        $wo = new WrittenOption();
        $form = $this->createForm(WrittenOptionType::class, $wo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $doctrine->getManager();
            $wo->setUser($user);
            $wo->setExercised(false);
            $wo->setExpired(false);
            $wo->setStockExpiryPrice(0.00);
            $wo_expiry = date_format($wo->getExpiry(), 'Y-m-d');
            
            //create transaction for sale of option contract ( you did make money right?? )
            $transaction = new Transaction();
            $date = new \DateTime();
            $transaction->setType(1);
            $transaction->setDate($date);
            $transaction->setUser($user);

            if($wo->getContractType() === 1){
                $transaction->setName("Sold " . $wo->getContracts() . " " . $wo->getStock()->getTicker(). " " . $wo->getStrike() . " " . $wo_expiry . " Covered Call");
            } else {
                $transaction->setName("Sold " . $wo->getContracts() . " " . $wo->getStock()->getTicker(). " " . $wo->getStrike() . " " . $wo_expiry . " Cash Secured Put");
            }

            // Update Wallet
            $wallet = $em->getRepository(Wallet::class)->find($user->getId());
            $total = $form->get("total")->getData();
            $transaction->setAmount($total);
            $currency = $form->get("payment_currency")->getData();
            
            if ($currency == 'can'){
                $wallet->deposit('CAN', $total);
                $transaction->setCurrency(1);
            }

            if ($currency == 'usd'){
                $wallet->deposit('USD', $total);
                $transaction->setCurrency(2);
            }
            
            $lock_amount = (100 * $wo->getContracts() * $wo->getStrike());
            if($wo->getContractType() === 2){
                if ($currency == 'can'){
                    $wallet->lock('CAN', $lock_amount);
                }
    
                if ($currency == 'usd'){
                    $wallet->lock('USD', $lock_amount);
                }
            }

            $wo->getStock()->addEarned($total);

            $em->persist($transaction);
            $em->persist($wo);
            $em->flush();

            return $this->redirectToRoute('stocks_written_options');
        }

        return $this->render('form/write_option.html.twig', [
            'error' => "",
            'form' => $form->createView(),
        ]);
    }

    #[Route('/stocks/writtenoption/exercise/{id}', name: 'stocks_writtenoption_exercise')]
    public function exerciseWrittenOption(ManagerRegistry $doctrine, Request $request, int $id): Response
    {
        // get cc from id..
        $em = $doctrine->getManager();
        $wo = $em->getRepository(WrittenOption::class)->find($id);
        $wo->setExercised(true);

        // get stock from cc..
        $stock = $wo->getStock();

        // EXERCISE COVERED CALL ..
        if($wo->getContractType() === 1){
            // remove shares from shareBuy Objects
            $shareBuys = $stock->getShareBuys()->getValues();
            usort($shareBuys, [$this,"sort_buys_by_price"]);
            $totalSharesLeft = 0;

            foreach($shareBuys as $sb){
                $bought = $sb->getAmount();
                $sold = $sb->getSold();
                if($bought > $sold){
                    $totalSharesLeft += ($bought - $sold);
                }
            }
    
            $amount_to_sell = 100;
    
            $buys_length = count($shareBuys);
            $current_buy = 0;
    
            //loop though share buys and remove 
            while($amount_to_sell > 0){
                if($current_buy == $buys_length){
                    $amount_to_sell = 0;
                    // TODO:: Show an Error message here..
                    
                    // return $this->render('form/index.html.twig', [
                    //     'error' => 'You dont own enough of these shares',
                    //     'form' => $form->createView(),
                    // ]);
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
    
            // if we are selling the last amount of shares we own (or all of them), remove stock from the playing list.. 
            if($amount_to_sell === $totalSharesLeft){
                $stock->setBeingPlayedShares(false);
            } else {
                $stock->setSharesOwned($totalSharesLeft - $amount_to_sell);
            }
    
            // sell 100 shares of stock at strike
            $date = new \DateTime();
            $share_sell = new ShareSell();
            $share_sell->setStock($stock);
            $share_sell->setPrice($wo->getStrike());
            $share_sell->setAmount(100);
            $share_sell->setDate($date);
            
            $user = $share_sell->getStock()->getUser();
            $transaction = new Transaction();
            $transaction->setType(1);
            $transaction->setDate($date);
            $transaction->setUser($user);
            $transaction->setName($stock->getTicker() . ' - $' . $wo->getStrike() . ' Covered Call Exercised');
    
            $cost = ($wo->getStrike() * 100) - 9.95;
            $currency = $wo->getPaymentCurrency();
            $wallet = $em->getRepository(Wallet::class)->find($user->getId());
    
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
        
        
        // EXERCISE CSP...
        } else {
            $date = new \DateTime();
            $share_buy = new ShareBuy();
            $share_buy->setStock($stock);
            $share_buy->setPrice($wo->getStrike());
            $share_buy->setAmount($wo->getContracts() * 100);
            $share_buy->setAmount($wo->getContracts() * 100);
            $share_buy->setDate($date);
            $user = $share_buy->getStock()->getUser();
            $cost = $share_buy->getAmount() * $share_buy->getPrice();
            $currency = $wo->getPaymentCurrency();

            $share_buy->setSold(0);
            $share_buy->getStock()->setBeingPlayedShares(1);
            $share_buy->getStock()->addSharesOwned($share_buy->getAmount());
            
            $wallet = $em->getRepository(Wallet::class)->find($user->getId());

            $transaction = new Transaction();
            $transaction->setType(2);
            $transaction->setDate($date);
            $transaction->setUser($user);
            $word = ($share_buy->getAmount() === 1) ? 'Share' : 'Shares';
            
            $transaction->setName('Bought' . ' ' . $share_buy->getAmount() . ' ' . $share_buy->getStock()->getTicker() . ' ' . $word);
            
            if ($currency == 'can'){
                $wallet->unlock('CAN', $cost);
                $wallet->withdraw('CAN', $cost);
                $transaction->setCurrency(1);
            }

            if ($currency == 'usd'){
                $wallet->unlock('USD', $cost);
                $wallet->withdraw('USD', $cost);
                $transaction->setCurrency(2);
            }
            
            $transaction->setAmount($cost);

            $em->persist($transaction);
            $em->persist($share_buy);
        }

        $em->flush();

        return new JsonResponse(array('success' => true));
    }

    #[Route('/stocks/writtenoption/expire/{id}', name: 'stocks_writtenoption_expire')]
    public function WrittenOptionExpired(ManagerRegistry $doctrine, Request $request, int $id): Response
    {
        // get cc from id..
        $em = $doctrine->getManager();
        $wo = $em->getRepository(WrittenOption::class)->find($id);
        $wo->setExpired(true);
        $em->flush();

        return $this->redirectToRoute('stocks_written_options');
    }


    #[Route('/stocks/update', name: 'stock_update')]
    public function updateStock(Request $request, ManagerRegistry $doctrine): JsonResponse
    {
        $id = $request->get('stock_id');
        $user = $this->getUser();
        $disable_can = false; // $request->get('disable_can');
        $this->settings = $user->getSettings();
        $forceUpdate = $this->settings->isStocksManualUpdateEnabled();

        if ($request->isXMLHttpRequest()) {    
            $stock = $doctrine->getRepository(Stock::class)->find($id);
            $updated = false;

             // check to see if has been 2 days or longer since last update
             $lasttimestamp =  $stock->getLastPriceUpdate()->getTimestamp();
             $timeDiff = abs(time() - $lasttimestamp);
             $numberDaysSec = $timeDiff/86400;  // 86400 seconds in one day

             // and you might want to convert to integer
             $numberDays = intval($numberDaysSec);
             $update_day = date('D', $lasttimestamp);
             $update_hour = date('H', $lasttimestamp);

             // todays date and time
             $day_today = date('D');
             $hour_today = date('H');
             $updatedToday = ($stock->getLastPriceUpdate()->format('Y-m-d') === date('Y-m-d'));

            // check if it's the weekend today
            $weekend_today = ($day_today === "Sat" || $day_today === "Sun" || ($day_today === "Fri" && $hour_today > 16) || ($day_today === "Mon" && $hour_today < 9));

            // check if we last updated during the weekend..
            $updated_on_weekend = ($update_day === "Sat" || $update_hour === "Sun" || ($update_day === "Fri" && $update_hour > 16) || ($update_day === "Mon" && $update_hour < 9));

            $marketOpen = (!$weekend_today && $hour_today >= 10 && $hour_today < 16);

            $ustatus = "Start Check..";
            $this->test_string = "1";
            dump("Test 1");

            // check if force update is enabled in settings..
            if($forceUpdate)
            {
                $this->test_string = "2 - Force Update";
                dump("Test 2");
                $ustatus = $this->updateStockInfo($doctrine, $stock,$disable_can,$day_today,$hour_today);
                if($ustatus === "U"){
                    $date = new \DateTime();
                    $stock->setLastPriceUpdate($date);
                    $em = $doctrine->getManager();
                    $em->flush();
                    $updated = true;
                }
             }

             // first check if it's been over 3 days since the last update..
             if($numberDays >= 3){
                $this->test_string = "3 - Over 3 Days since last update";
                 dump("Test 3");
                $ustatus = $this->updateStockInfo($doctrine, $stock,$disable_can,$day_today,$hour_today);
                if($ustatus === "U"){
                    $date = new \DateTime();
                    $stock->setLastPriceUpdate($date);
                    $em = $doctrine->getManager();
                    $em->flush();
                    $updated = true;
                }
             }

             // now check if it's now the weekend and if we already updated this weekend
             if($weekend_today){
                if(!$updated_on_weekend){
                    $this->test_string = "3.1 - Weekend Update";
                    dump("Test 3.1");
                    $ustatus = $this->updateStockInfo($doctrine, $stock,$disable_can,$day_today,$hour_today);
                    if($ustatus === "U"){
                        $date = new \DateTime();
                        $stock->setLastPriceUpdate($date);
                        $em = $doctrine->getManager();
                        $em->flush();
                        $updated = true;
                    }
                } else {
                    $this->test_string = "3.2 - Trying to update again this weekend when we already have";
                    dump("Test 3.2");
                    $updated = true;
                    $status_code = 1;
                    $update_status_stock = "You already updated this stock this weekend, price will not change.";
                }
             }
             
             // if we are trying to do a mid-day update..
             if(!$updated && !$weekend_today && $updatedToday){

                // so if we are just getting a mid-day update..
                if($marketOpen){
                    $this->test_string = "4 - Mid Day Update";
                    dump("Test 4");
                    $ustatus = $this->updateStockInfo($doctrine, $stock,$disable_can,$day_today,$hour_today);
                    if($ustatus === "U"){
                        $date = new \DateTime();
                        $stock->setLastPriceUpdate($date);
                        $em = $doctrine->getManager();
                        $em->flush();
                        $updated = true;
                    }
                } else {
                    if($hour_today <= 9){
                        $this->test_string = "5.1 - Trying to update again before Pre-Market";
                        dump("Test 5.1");
                        $updated = true;
                        $status_code = 1;
                        $update_status_stock = "You already updated this stock today before open.. price will not change.";
                    } else {
                        $this->test_string = "5.2 - Trying to update again after Post-Market";
                        dump("Test 5.2");
                        $updated = true;
                        $status_code = 1;
                        $update_status_stock = "You already updated this stock today after close.. price will not change.";
                    }
                }
             }

             // no other conditions.. update please..
             if(!$updated){
                $this->test_string = "6 - Normal Update";
                 dump("Test 6");
                $ustatus = $this->updateStockInfo($doctrine, $stock,$disable_can,$day_today,$hour_today);
                if($ustatus === "U"){
                    $date = new \DateTime();
                    $stock->setLastPriceUpdate($date);
                    $em = $doctrine->getManager();
                    $em->flush();
                    $updated = true;
                }
            }

             dump($ustatus);


            if($ustatus !== "U")
            {
                switch($ustatus){
                    case("C1"):
                        $update_status_stock = "No Update, Canadian Stocks can only be updated when market is closed";
                        dump("Test C1");
                        break;

                    case("C2"):
                        $update_status_stock = "No Update, can only do 5 Canadian stock updates per minute";
                        dump("Test C2");
                        break;

                    case("C3"):
                        $update_status_stock = "Something wrong with the API metadata";
                        dump("Test C3");
                        break;

                    case("C4"):
                        $update_status_stock = "Something wrong with the API metadata";
                        dump("Test C4");
                        break;

                    case("C5"):
                        $update_status_stock = "Canadian Stocks Disabled in Settings OR Stock is not being played";
                        dump("Test C5");
                        break;

                    default:
                        $update_status_stock = "UStatus:" . $ustatus;
                        dump("Test C5");
                }

                $status_code = 1;
                $status_message = $update_status_stock;
            } else {
                $status_code = 2;
                $status_message = "Stock Price Updated";
                dump("Test 7");
            }

            dump("Update:" . $stock->getTicker());
        

            return new JsonResponse(array('success' => true, 'Day' => $update_day, 'ticker' => $stock->getTicker(), 'TEST' => $this->test_string, 'update_function' => $this->update_function, 'can_updated' => $this->can_updated, 'UStatus' => $ustatus, 'status_code' => $status_code, 'status_message' =>  $status_message,  'days' => $numberDaysSec, 'price' => $this->update_price));
        }

        return new JsonResponse(array('success' => false, 'reason' => "Non XMLHttp Request"));
    }

    private function sort_buys_by_price($a, $b)
    {
        if($a->getPrice() == $b->getPrice()){ return 0; }
        return ($a->getPrice() < $b->getPrice()) ? -1 : 1;
    }
}
