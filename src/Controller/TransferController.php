<?php

namespace App\Controller;

use App\Entity\ShareBuy;
use App\Entity\ShareSell;
use App\Entity\Stock;
use App\Entity\Transaction;
use App\Entity\User;
use App\Form\TransferSharesType;
use App\Form\TransferCdnType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;



class TransferController extends AbstractController
{
//    #[Route('/transfer', name: 'app_transfer')]
//    public function index(ManagerRegistry $doctrine): Response
//    {
//        $user = $this->getUser();
//        $settings = $user->getSettings();
//
//        return $this->render('notes/index.html.twig', [
//            'settings' => $settings,
//        ]);
//    }

    #[Route('/transfer/cdn', name: 'app_transfer_cdn')]
    public function cdn(ManagerRegistry $doctrine, Request $request): Response
    {
        $user = $this->getUser();
        $settings = $user->getSettings();
        

        $error = "";
        $form = $this->createForm(TransferCdnType::class);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $em = $doctrine->getManager();
            $amount = (float)$form->get("amount")->getData();
            $wallet = $user->getWallet();
            $user2 = $form->get("user")->getData();
            $wallet2 = $user2->getWallet();

            if ($amount > (float)$wallet->getCAN()){
                $error = "Not enough Cash in wallet for this transfer";
                return $this->render('transfer/cdn.html.twig', [
                    'settings' => $settings,
                    'error' => $error,
                    'form' => $form->createView(),
                ]);
            }
            
            // move money..
            $wallet->withdraw("CAN", $amount);
            $wallet2->deposit("CAN", $amount);

            // create transaction for both users..
            $transaction_sender = new Transaction();
            $date = new \DateTime();
            $transaction_sender->setType(8);
            $transaction_sender->setAmount($amount);
            $transaction_sender->setCurrency(1);
            $transaction_sender->setDate($date);
            $transaction_sender->setUser($user);
            $transaction_sender->setName('Sent $'. $amount . ' CDN from ' . $user2->getUsername());
            $em->persist($transaction_sender);

            $transaction_receiver = new Transaction();
            $date = new \DateTime();
            $transaction_receiver->setType(8);
            $transaction_receiver->setAmount($amount);
            $transaction_receiver->setCurrency(1);
            $transaction_receiver->setDate($date);
            $transaction_receiver->setUser($user2);
            $transaction_receiver->setName('Received $'. number_format($amount, 2) . ' CDN from ' . $user->getUsername());
            $em->persist($transaction_receiver);
            $em->flush();

            return $this->redirectToRoute('dashboard');

        }

        return $this->render('transfer/cdn.html.twig', [
            'settings' => $settings,
            'error' => $error,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/transfer/usd', name: 'app_transfer_usd')]
    public function usd(ManagerRegistry $doctrine, Request $request): Response
    {
        $user = $this->getUser();
        $settings = $user->getSettings();

        $error = "";
        $form = $this->createForm(TransferCdnType::class);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $em = $doctrine->getManager();
            $amount = (float)$form->get("amount")->getData();
            $wallet = $user->getWallet();
            $user2 = $form->get("user")->getData();
            $wallet2 = $user2->getWallet();

            if ($amount > (float)$wallet->getUSD()){
                $error = "Not enough Cash in wallet for this transfer";
                return $this->render('transfer/usd.html.twig', [
                    'settings' => $settings,
                    'error' => $error,
                    'form' => $form->createView(),
                ]);
            }

            // move money..
            $wallet->withdraw("USD", $amount);
            $wallet2->deposit("USD", $amount);

            // create transaction for both users..
            $transaction_sender = new Transaction();
            $date = new \DateTime();
            $transaction_sender->setType(8);
            $transaction_sender->setAmount($amount);
            $transaction_sender->setCurrency(2);
            $transaction_sender->setDate($date);
            $transaction_sender->setUser($user);
            $transaction_sender->setName('Sent $'. $amount . ' USD to ' . $user2->getUsername());
            $em->persist($transaction_sender);

            $transaction_receiver = new Transaction();
            $date = new \DateTime();
            $transaction_receiver->setType(8);
            $transaction_receiver->setAmount($amount);
            $transaction_receiver->setCurrency(2);
            $transaction_receiver->setDate($date);
            $transaction_receiver->setUser($user2);
            $transaction_receiver->setName('Received $'. number_format($amount, 2) . ' USD from ' . $user->getUsername());
            $em->persist($transaction_receiver);
            $em->flush();

            return $this->redirectToRoute('dashboard');

        }

        return $this->render('transfer/usd.html.twig', [
            'settings' => $settings,
            'error' => $error,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/transfer/shares', name: 'app_transfer_shares')]
    public function shares(ManagerRegistry $doctrine, Request $request): Response
    {
        $user = $this->getUser();
        $settings = $user->getSettings();

        $error = "";
        $form = $this->createForm(TransferSharesType::class);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $em = $doctrine->getManager();

            $date = new \DateTime();

            // Get data from form..
            $transfer_user = $form->get("user")->getData();
            $stock = $form->get("stock")->getData(); //$doctrine->getRepository(Stock::class)->find($form->get("stock")->getData());
            $shareBuy = $doctrine->getRepository(shareBuy::class)->find($form->get("shareBuy")->getData());
            $amount = $form->get("amount")->getData();
            $lastShares = $form->get("last_shares")->getData();

            $shareBuy->addSold($amount);
            $em->persist($shareBuy);


            //create shareSell Object
            $shareSell = new ShareSell();
            $shareSell->setStock($stock);
            $shareSell->setPrice(0.00);
            $shareSell->setAmount((int)$amount);
            $shareSell->setDate($date);
            $shareSell->setNofee(true);
            $shareSell->setTransfer(true);
            $em->persist($shareSell);


            // if we are selling the last amount of shares we own (or all of them), remove stock from the playing list..
            if($lastShares){
                $stock->getStock()->setBeingPlayedShares(false);
            } else {
                $stock->setSharesOwned($stock->getSharesOwned() - $amount);
            }

            //Create Transaction Record..
            $transaction = new Transaction();
            $date = new \DateTime();
            $transaction->setType(7);
            $transaction->setAmount(0);
            $transaction->setCurrency(1);
            $transaction->setDate($date);
            $transaction->setUser($user);
            $word = ($amount) ? 'Share' : 'Shares';
            $transaction->setName('Transferred '. $amount . ' ' . $stock->getTicker() . ' ' . $word .' to ' . $transfer_user->getUsername());
            $em->persist($transaction);


            // check if the user stock is transfer too already has that stock?
            $transfer_stock = $doctrine->getRepository(Stock::class)->findOneBy(['user' =>  $transfer_user, 'ticker' => $stock->getTicker()]);
            if (is_null($transfer_stock)) {
                $date = new \DateTime();
                $transfer_stock = new Stock();
                $transfer_stock->setUser($transfer_user);
                $transfer_stock->setName($stock->getName());
                $transfer_stock->setTicker($stock->getTicker());
                $transfer_stock->setEarned(0);
                $transfer_stock->setLastBought($date);
                $transfer_stock->setCountry($stock->getCountry());
                $transfer_stock->setType($stock->getType());
                $transfer_stock->setCurrentPrice($stock->getCurrentPrice());
                $transfer_stock->setPriceYesterday($stock->getPriceYesterday());
                $transfer_stock->setPriceWeek($stock->getPriceWeek());
                $transfer_stock->setPriceMonth($stock->getPriceMonth());
                $transfer_stock->setPriceYear($stock->getPriceYear());
                $transfer_stock->setLastPriceUpdate($stock->getLastPriceUpdate());
                $transfer_stock->setBgColor($stock->getBgColor());
                $transfer_stock->setPaysDividend($stock->isPaysDividend());
                $transfer_stock->setDelisted($stock->isDelisted());
                $transfer_stock->setBeingPlayedShares(true);
                $transfer_stock->setBeingPlayedOption(false);
                $transfer_stock->setSharesOwned($amount);
                $transfer_stock->setNoFee($stock->isNoFee());
            } else {
                $transfer_stock->setSharesOwned($transfer_stock->getSharesOwned() + $amount);
            }

            $em->persist($transfer_stock);

            // create a new buy share object
            $shareBuy = new ShareBuy();
            $shareBuy->setStock($transfer_stock);
            $shareBuy->setPrice(0.00);
            $shareBuy->setAmount($amount);
            $shareBuy->setDate($date);
            $shareBuy->setSold(0);
            $shareBuy->setNofee(true);
            $shareBuy->setTransfer(true);
            $em->persist($shareBuy);

            $transaction_reciever = new Transaction();
            $date = new \DateTime();
            $transaction_reciever->setType(8);
            $transaction_reciever->setAmount(0);
            $transaction_reciever->setCurrency(1);
            $transaction_reciever->setDate($date);
            $transaction_reciever->setUser($transfer_user);
            $word = ($amount) ? 'Share' : 'Shares';
            $transaction_reciever->setName('Received '. $amount . ' ' . $stock->getTicker() . ' ' . $word .' from ' . $user->getUsername());
            $em->persist($transaction_reciever);
            $em->flush();

            return $this->redirectToRoute('dashboard');
        }

        return $this->render('transfer/shares.html.twig', [
            'form' => $form->createView(),
            'error' => $error,
            'settings' => $settings,
        ]);
    }

    #[Route('/transfer/shares/getbuys', name: 'app_transfer_shares_getbuys')]
    public function sharesGetBuys(ManagerRegistry $doctrine, Request $request): Response
    {
        $id = $request->get('share_id');
        $stock = $doctrine->getRepository(Stock::class)->find($id);
        $shareBuys = $doctrine->getRepository(ShareBuy::class)->findBy(['stock' => $stock]);


        $refined_buys = [];
        foreach($shareBuys as $buy)
        {
            if($buy->getSold() < $buy->getAmount()) {
                $leftover = $buy->getAmount() - $buy->getSold();
                $refined_buys[] = ['id' => $buy->getId(), 'max' => $leftover, 'name' => $buy->getStock()->getName() . " - " . $buy->getDate()->format("F j, Y") . " - " . $buy->getAmount() . " * $" . number_format($buy->getPrice(), 2) . " - " . $leftover . " Remaining"];
            }
        }

        dump($refined_buys);

        $response = new JsonResponse();
        $response->setData([
            "buys" => $refined_buys,
        ]);

        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    #[Route('/transfer/options', name: 'app_transfer_options')]
    public function options(ManagerRegistry $doctrine): Response
    {
        $user = $this->getUser();
        $settings = $user->getSettings();

        return $this->render('transfer/options.html.twig', [
            'settings' => $settings,
        ]);
    }
}
