<?php

namespace App\Controller;


use DateTime;
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
use App\Form\WrittenOptionBuytocloseType;
use App\Form\WrittenOptionType;
use App\Form\WrittenOptionRolloverType;
use App\Entity\Atom;
use App\Entity\WrittenOption;
use App\Entity\Option;
use App\Entity\Settings;
use App\Entity\Stock;
use App\Entity\ShareBuy;
use App\Entity\ShareSell;
use App\Entity\Transaction;
use App\Entity\Wallet;
use App\Repository\OptionRepository;
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

    private function updateStockInfo($user, $doctrine, Stock $stock, $disable_can, $day_today, $hour_today)
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
                        $date = new DateTime('now');
                        $now = $date->format('Y-m-d H:i:s');
                        $atomCount->setValue($this->can_count);
                        $atomDate->setValue($now);
                        $em = $doctrine->getManager();
                        $em->flush();
                        
                        $data = json_decode($json,true);
                        if(!$md = (isset($data["Meta Data"])) ? $data["Meta Data"] : false){
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
                            $today = new DateTime();

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
                        $lastUpdate = DateTime::createFromFormat('Y-m-d H:i:s', $atomDate->getValue());
                        $now = new DateTime('now');
                        $interval = $lastUpdate->diff($now);
                        $minutes = (int)$interval->format('%i');

                        if($minutes >= $this->settings->getStocksCanadianUpdateLimitTimeout()){
                            $atomCount->setValue(0);
                            $em = $doctrine->getManager();
                            $em->flush();
                            return $this->updateStockInfo($user, $doctrine, $stock, $disable_can, $day_today, $hour_today);
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
                $opc_link = 'https://www.optionsprofitcalculator.com/ajax/getStockPrice?stock=' . $stock->getTicker() . '&reqId=0';
                dump($opc_link);
                $result = file_get_contents($opc_link);
                dump("Update:" . $stock->getTicker());
                //dump($result);
                $price = json_decode($result)->price->last;
                $old_price = $stock->getCurrentPrice();
                $stock->setCurrentPrice($price);
                $this->update_price = $price;

                $last = $stock->getLastPriceUpdate();
                $today = new DateTime();

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

                // update covered call ask if there are any..
                forEach($user->getWrittenOptions() as $option){
                    if($option->getStock() === $stock && !$option->isExpired() && !$option->isExercised()){
                        $e = date_format($option->getExpiry(), "Y-m-d");
                        $t = ((int)$option->getType() === 1) ? "c":"p";
                        $s = number_format($option->getStrike(), 2);
                        $option_data = json_decode(file_get_contents('https://www.optionsprofitcalculator.com/ajax/getOptions?stock=' . $stock->getTicker() . '&reqId=1'), true);
                        $option_data = $option_data['options'];
                        $option_data = $option_data[$e];
                        $option_data = $option_data[$t];
                        $option_data = $option_data[$s];
                        $current = $option_data['a'];
                        $option->setAsk($current);
                    }
                }
            }

            if($stock->isBeingPlayedOption() && count($stock->getOptions()) > 0){
                foreach ($stock->getOptions() as $option) {
                    if(!$option->isExpired()){
                        $date = new DateTime();
                        if($option->getExpiry() < $date){
                            $option->setExpired(true);
                            $option->getStock()->setBeingPlayedOption(false);
                            continue;
                        }
                        $e = date_format($option->getExpiry(), "Y-m-d");
                        $t = ((int)$option->getType() === 1) ? "c":"p";
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
        $settings = $user->getSettings();
        $stocks = $user->getStocks();
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
            'settings' => $settings,
        ]);
    }

    #[Route('/stocks/average/{stock_id}', name: 'stocks_average')]
    public function getStockAverage(Request $request, ManagerRegistry $doctrine, int $stock_id): JsonResponse
    {
        $shares = 0;
        $avg = 0;
        $total = 0;

        $em = $doctrine->getManager();
        $stock = $em->getRepository(Stock::class)->find($stock_id);

        foreach($stock->getShareBuys() as $buy)
        {
            if($buy->getSold() < $buy->getAmount()) {
                $remaining = $buy->getAmount() - $buy->getSold();
                $shares += $remaining;
                $total += ($remaining * $buy->getPrice()) + (($stock->isNoFee() || $buy->isNofee()) ? 0 : 9.95);
            }
        }

        if($shares > 0) {
            $avg = $total / $shares;
        }

        return new JsonResponse(array('shares' => $shares, 'average' => round($avg, 2,PHP_ROUND_HALF_UP)));
    }

    #[Route('/stocks/details/{stock_id}', name: 'stock_details')]
    public function details(Request $request, ManagerRegistry $doctrine, int $stock_id): Response
    {
        $user = $this->getUser();
        $settings = $user->getSettings();
        $em = $doctrine->getManager();
        $stock = $em->getRepository(Stock::class)->find($stock_id);

        // make sure this stock exists..
        if(!isset($stock)) {
            return $this->redirectToRoute('stocks');
        }

        // Make sure this stock belongs to the user...
        if($user !== $stock->getUser())
            return $this->redirectToRoute('dashboard');

        return $this->render('stock/details.html.twig', [
            'stock' => $stock,
            'settings' => $settings,
        ]);
    }

    #[Route('/stocks/add', name: 'stocks_add')]
    public function add(ManagerRegistry $doctrine, Request $request): Response
    {
        $user = $this->getUser();
        $settings = $user->getSettings();
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
            $date = new DateTime();
            $stock->setLastPriceUpdate($date);
            $stock->setBgColor("ffffff");

            $em = $doctrine->getManager();
            $em->persist($stock);
            $em->flush();

            return $this->redirectToRoute('stocks_add');
        }

        return $this->render('form/stock.html.twig', [
            'error' => "",
            'form' => $form->createView(),
            'settings' => $settings,
        ]);
    }

    #[Route('/stocks/shares/buy', name: 'stocks_buy_shares')]
    public function buyShares(ManagerRegistry $doctrine, Request $request): Response
    {
        $user = $this->getUser();
        $settings = $user->getSettings();
        $em = $doctrine->getManager();
        $share_buy = new ShareBuy();
        $form = $this->createForm(ShareBuyType::class, $share_buy);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $share_buy->setTransfer(false);

            $STOCK = $em->getRepository(Stock::class)->find($form->get("Stock")->getData());
            $share_buy->setStock($STOCK);
            
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
            $date = new DateTime();
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

        $myStocks = $em->getConnection()->executeQuery(" SELECT * FROM stock p WHERE p.user_id = :user_id ORDER BY p.id ASC", ['user_id' => $user->getId(), 'pays' => 1])->fetchAllAssociative();

        return $this->render('form/share_buy.html.twig', [
            'error' => "",
            'stocks' => $myStocks,
            'form' => $form->createView(),
            'settings' => $settings,
        ]);
    }

    #[Route('/stocks/shares/sell', name: 'stocks_sell_shares')]
    public function sellShares(ManagerRegistry $doctrine, Request $request): Response
    {
        $em = $doctrine->getManager();
        $user = $this->getUser();
        $settings = $user->getSettings();
        $error = "";
        $share_sell = new ShareSell();
        $share_sell->setTransfer(false);
        $form = $this->createForm(ShareSellType::class, $share_sell);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

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
            $date = new DateTime();
            $transaction->setType(1);
            $transaction->setDate($date);
            $transaction->setUser($user);
            $word = ($data->getAmount() === 1) ? 'Share' : 'Shares';
            $transaction->setName('Sold' . ' ' . $data->getAmount() . ' ' . $share_sell->getStock()->getTicker() . ' ' . $word);

            $cost = $form->get("cost")->getData();
            $currency = $form->get("payment_currency")->getData();

            dump($currency);
            
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

        $myStocks = $em->getConnection()->executeQuery(" SELECT * FROM stock p WHERE p.user_id = :user_id ORDER BY p.id ASC", ['user_id' => $user->getId(), 'pays' => 1])->fetchAllAssociative();

        return $this->render('form/share_sell.html.twig', [
            'stocks' => $myStocks,
            'error' => $error,
            'form' => $form->createView(),
            'settings' => $settings,
        ]);
    }

    #[Route('/stocks/options/add', name: 'stocks_add_option')]
    public function addOption(ManagerRegistry $doctrine, Request $request): Response
    {
        $user = $this->getUser();
        $settings = $user->getSettings();
        $error = "";
        $option = new Option();
        $form = $this->createForm(OptionType::class, $option);
        $form->handleRequest($request);
        $em = $doctrine->getManager();

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $user = $option->getStock()->getUser();
            $wallet = $em->getRepository(Wallet::class)->find($user->getId());

            $date = new DateTime();

            $option->setBuys(1);
            $option->setExpired(false);
            $option->setUser($user);
            $option->setCurrent(0.0);
            $option->setBuyDate($date);
            $option->setSellDate();
            $option->setSellPrice(0.0);
            $option->setTotalContracts($form->get("contracts")->getData());
            $option->setTotalContractsSold(0);
            $option->setSells(0);

            $transaction = new Transaction();
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

        $myStocks = $em->getConnection()->executeQuery(" SELECT * FROM stock p WHERE p.user_id = :user_id ORDER BY p.id ASC", ['user_id' => $user->getId()])->fetchAllAssociative();

        return $this->render('form/option_add.html.twig', [
            'error' => $error,
            'form' => $form->createView(),
            'stocks' =>$myStocks,
            'settings' => $settings,
        ]);
    }

    #[Route('/stocks/options/buy', name: 'stocks_buy_option')]
    public function buyOption(ManagerRegistry $doctrine, Request $request): Response
    {
        $user = $this->getUser();
        $settings = $user->getSettings();
        $error = "";
        $form = $this->createForm(OptionBuyType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $doctrine->getManager();
            $data = $form->getData();
            $option = $form->get("option")->getData();

            $date = new DateTime();

            $user = $option->getStock()->getUser();
            $wallet = $em->getRepository(Wallet::class)->find($user->getId());
            
            $contracts = $option->getContracts() + $form->get("contracts")->getData();
            $average = (($option->getAverage() * $option->getContracts()) + ($form->get("contracts")->getData() * $form->get("average")->getData())) / $contracts;
            

            $option->setAverage($average);
            $option->setContracts($contracts);
            $option->setBuys((int)$option->getBuys() + 1);
            $option->setBuyDate($date);
            $option->setSellPrice(0);

            $transaction = new Transaction();
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
            'settings' => $settings,
        ]);
    }

    #[Route('/stocks/options/sell', name: 'stocks_sell_option')]
    public function sellOption(ManagerRegistry $doctrine, Request $request): Response
    {
        $user = $this->getUser();
        $settings = $user->getSettings();
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
            $date = new DateTime();
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
            'settings' => $settings,
        ]);
    }

    #[Route('stocks/writtenoptions', name: 'stocks_written_options')]
    public function writtenOptions(ManagerRegistry $doctrine, Request $request): Response
    {
        $user = $this->getUser();
        $settings = $user->getSettings();
        $options = $user->getWrittenOptions();

        //loop though all the calls and see if any have expired
        $today = new DateTime();
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
            'settings' => $settings,
        ]);
    }

    #[Route('/stocks/writtenoptions/write', name: 'stocks_writtenoptions_write')]
    public function sellWrittenOption(ManagerRegistry $doctrine, Request $request): Response
    {
        $user = $this->getUser();
        $settings = $user->getSettings();
        $em = $doctrine->getManager();
        $error = "";
        $wo = new WrittenOption();
        $form = $this->createForm(WrittenOptionType::class, $wo);
        $form->handleRequest($request);

        $payment_locked = $form->get("payment_locked")->getData();

        if ($form->isSubmitted() && $form->isValid()) {

            $stock = $em->getRepository(Stock::class)->find($form->get("Stock")->getData());
            $wo->setStock($stock);

            $wo->setUser($user);
            $wo->setExercised(false);
            $wo->setExpired(false);
            $wo->setStockExpiryPrice(0.00);
            $wo->setBuyout(0);
            $wo->setBuyoutPrice(0);
            $wo_expiry = date_format($wo->getExpiry(), 'Y-m-d');
            
            //create transaction for sale of option contract ( you did make money right?? )
            $transaction = new Transaction();
            $date = new DateTime();
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
                if ($form->get("payment_locked")->getData() === true) {
                    $wallet->lock('CAN', $total);
                } else {
                    $wallet->deposit('CAN', $total);
                }

                $transaction->setCurrency(1);
            }

            if ($currency == 'usd'){
                if ($form->get("payment_locked")->getData() === true) {
                    $wallet->lock('USD', $total);
                } else {
                    $wallet->deposit('USD', $total);
                }
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

        $myStocks = $em->getConnection()->executeQuery(" SELECT * FROM stock p WHERE p.user_id = :user_id AND p.shares_owned > 99 ORDER BY p.id ASC", ['user_id' => $user->getId(), 'pays' => 1])->fetchAllAssociative();

        return $this->render('form/write_option.html.twig', [
            'stocks' => $myStocks,
            'error' => "",
            'form' => $form->createView(),
            'settings' => $settings,
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
    
            $amount_to_sell = 100 * $wo->getContracts();
    
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
            $date = new DateTime();
            $share_sell = new ShareSell();
            $share_sell->setStock($stock);
            $share_sell->setPrice($wo->getStrike());
            $share_sell->setAmount(100 * $wo->getContracts());
            $share_sell->setDate($date);
            $share_sell->setNofee(false);
            $share_sell->setTransfer(false);
            
            $user = $share_sell->getStock()->getUser();
            $transaction = new Transaction();
            $transaction->setType(1);
            $transaction->setDate($date);
            $transaction->setUser($user);
            $transaction->setName($stock->getTicker() . ' - '. $wo->getContracts() .'* $' . number_format($wo->getStrike(),2) . ' Covered Call Exercised');
    
            $cost = ($wo->getStrike() * (100 * $wo->getContracts())) - 43.05;
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
            $date = new DateTime();
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

    #[Route('/stocks/writtenoption/rollover/{id}', name: 'stocks_writtenoption_rollover')]
    public function rolloverWrittenOption(ManagerRegistry $doctrine, Request $request, int $id): Response
    {
        $user = $this->getUser();
        $settings = $user->getSettings();

        // get cc from id..
        $em = $doctrine->getManager();
        $wo = $em->getRepository(WrittenOption::class)->find($id);

        // get stock from cc..
        $stock = $wo->getStock();

        $nwo = new WrittenOption();
        $nwo->setContracts($wo->getContracts());
        $form = $this->createForm(WrittenOptionRolloverType::class, $nwo);
        $form->handleRequest($request);

        $use_locked_funds = ((int)$form->get("use_locked_funds")->getData() === 1);
        $payment_locked = ((int)$form->get("payment_locked")->getData() === 1);

        if ($form->isSubmitted() && $form->isValid()) {
            $total = (float)$form->get("total")->getData();
            $wo->setExpiry($nwo->getExpiry());
            $wo->setStrike($nwo->getStrike());
            $price = (float)$wo->getPrice() + (($total / 100) / $wo->getContracts());
            $wo->setPrice(round($price, 2));

            $transaction = new Transaction();
            $date = new DateTime();
            $transaction->setType(1);
            $transaction->setDate($date);
            $transaction->setUser($user);
            $transaction->setAmount($total);

            $currency = $form->get("payment_currency")->getData();
            $wallet = $em->getRepository(Wallet::class)->find($user->getId());

            //if total is negative..
            if ($total < 0) {
                if ($currency == 'can') {
                    if ($use_locked_funds === true) {
                        $locked_total = $wallet->getLockedCdn();
                        $totalABS = abs($total);
                        $locked_funds_to_use = ($totalABS <= $locked_total) ? $totalABS : $locked_total;
                        $wallet->unlock('CAN', $locked_funds_to_use);
                    }
                }

                if ($currency == 'usd'){
                    if ($use_locked_funds === true) {
                        $locked_total = $wallet->getLockedUsd();
                        $totalABS = abs($total);
                        $locked_funds_to_use = ($totalABS <= $locked_total) ? $totalABS : $locked_total;
                        $wallet->unlock('USD', $locked_funds_to_use);
                    }
                }
            }

            if ($currency == 'can'){
                if ($payment_locked === true) {
                    $wallet->lock('CAN', $total);
                } else {
                    $wallet->deposit('CAN', $total);
                }

                $transaction->setCurrency(1);
            }

            if ($currency == 'usd'){
                if ($payment_locked === true) {
                    $wallet->lock('USD', $total);
                } else {
                    $wallet->deposit('USD', $total);
                }
                $transaction->setCurrency(2);
            }

            $lock_amount = (100 * $wo->getContracts() * (float)$form->get("total")->getData());
            if($wo->getContractType() === 2){
                if ($currency == 'can'){
                    $wallet->lock('CAN', $lock_amount);
                }

                if ($currency == 'usd'){
                    $wallet->lock('USD', $lock_amount);
                }
            }

            $wo_expiry = date_format($wo->getExpiry(), 'Y-m-d');
            if($wo->getContractType() === 1){
                $transaction->setName("Covered Call Rollover - " . $wo->getContracts() . " " . $wo->getStock()->getTicker(). " $" . $wo->getStrike() . " " . $wo_expiry);
            } else {
                $transaction->setName("Cash Secured Put Rollover - " . $wo->getContracts() . " " . $wo->getStock()->getTicker(). " $" . $wo->getStrike() . " " . $wo_expiry);
            }

            $em->persist($transaction);
            $em->flush();

            return $this->redirectToRoute('stocks_written_options');
        }

        return $this->render('form/rollover_written_option.html.twig', [
            'old_option' => $wo,
            'error' => "",
            'form' => $form->createView(),
            'settings' => $settings,
        ]);

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

    #[Route('/stocks/writtenoption/buytoclose/{option}', name: 'stocks_written_options_buytoclose')]
    public function sellToClose(ManagerRegistry $doctrine, Request $request, int $option): Response
    {
        $user = $this->getUser();
        $settings = $user->getSettings();
        $em = $doctrine->getManager();
        $wo = $em->getRepository(WrittenOption::class)->find($option);
        $wo_expiry = date_format($wo->getExpiry(), 'Y-m-d');

        $info = ["option" => $option, "name" => $wo->getStock()->getTicker(). " - $" . $wo->getStrike() . " - " . $wo_expiry];

        $form = $this->createForm(WrittenOptionBuytocloseType::class, $info);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $currency = $form->get("payment_currency")->getData();
            $basic_fee = 9.95;
            $contract_fee = 1.25;
            $contracts = $form->get("contracts")->getData();
            $total_cost = (($form->get("price")->getData() * 100) * $contracts) + $basic_fee + ($contract_fee * $contracts);
            $wo->setExpired(true);
            $wo->setStockExpiryPrice($form->get("stock_price")->getData());
            $wo->setBuyout(true);
            $wo->setBuyoutPrice($total_cost);

            $transaction = new Transaction();
            $date = new DateTime();
            $transaction->setType(2);
            $transaction->setDate($date);
            $transaction->setUser($user);
            $option_strike = "$". number_format($wo->getStrike(), 2);
            $option_expiry = date_format($wo->getExpiry(), 'Y-m-d');
            $word = ($contracts === 1) ? " Covered Call Option " : " Covered Call Options ";
            $transaction->setName('Bought Back' . ' ' . $contracts . ' ' . $wo->getStock()->getTicker() . ' ' . $option_strike . ' ' . $option_expiry . ' ' . $word);
            $transaction->setAmount($total_cost);

            $wallet = $em->getRepository(Wallet::class)->find($user->getId());
            if ($currency == 'can'){
                if($form->get("use_locked_funds")->getData()){
                    $wallet->unlock('CAN', $total_cost);
                }
                $wallet->withdraw('CAN', $total_cost);
                $transaction->setCurrency(1);
            }

            if ($currency == 'usd'){
                if($form->get("use_locked_funds")->getData()){
                    $wallet->unlock('CAN', $total_cost);
                }
                $wallet->withdraw('USD', $total_cost);
                $transaction->setCurrency(2);
            }

            $em->persist($transaction);
            $em->flush();

            //return $this->redirectToRoute('stocks_buy_shares');
            return $this->redirectToRoute('stocks_written_options');
        }

        return $this->render('form/written_option_buyback.html.twig', [
            'error' => "",
            'form' => $form->createView(),
            'settings' => $settings,
        ]);
    }

    #[Route('/stocks/update', name: 'stock_update')]
    public function updateStock(Request $request, ManagerRegistry $doctrine): JsonResponse
    {
        $id = $request->get('stock_id');
        $user = $this->getUser();
        $disable_can = $request->get('disable_can');
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
            $updated_on_weekend = ($update_day === "Sat" || $update_day === "Sun" || ($update_day === "Fri" && $update_hour > 16) || ($update_day === "Mon" && $update_hour < 9));
            dump($update_day);

            $marketOpen = (!$weekend_today && $hour_today >= 10 && $hour_today < 16);

            $ustatus = "Start Check..";
            $this->test_string = "1";
            dump("Test 1");

            // check if force update is enabled in settings..
            if($forceUpdate)
            {
                $this->test_string = "2 - Force Update";
                dump("Test 2");
                $ustatus = $this->updateStockInfo($user, $doctrine, $stock,$disable_can,$day_today,$hour_today);
                if($ustatus === "U"){
                    $date = new DateTime();
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
                $ustatus = $this->updateStockInfo($user, $doctrine, $stock,$disable_can,$day_today,$hour_today);
                if($ustatus === "U"){
                    $date = new DateTime();
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
                    $ustatus = $this->updateStockInfo($user, $doctrine, $stock,$disable_can,$day_today,$hour_today);
                    if($ustatus === "U"){
                        $date = new DateTime();
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
                    $ustatus = $this->updateStockInfo($user, $doctrine, $stock,$disable_can,$day_today,$hour_today);
                    if($ustatus === "U"){
                        $date = new DateTime();
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
                $ustatus = $this->updateStockInfo($user, $doctrine, $stock,$disable_can,$day_today,$hour_today);
                if($ustatus === "U"){
                    $date = new DateTime();
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
