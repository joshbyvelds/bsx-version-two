<?php

namespace App\Controller;

use App\Form\WalletLockedTransferType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\User;
use App\Entity\Transaction;
use App\Entity\Wallet;
use App\Form\WalletAdjustmentType;
use App\Form\WalletConvertType;

use App\Controller\ToolsController;

class WalletController extends AbstractController
{
    #[Route('/wallet/withdrawl', name: 'wallet_withdrawl')]
    public function withdrawl(ManagerRegistry $doctrine, Request $request): Response
    {
        $user = $this->getUser();
        $settings = $user->getSettings();
        $form = $this->createForm(WalletAdjustmentType::class);
        $form->handleRequest($request);

        if($form->isSubmitted()){
            $data = $form->getData();
            $em = $doctrine->getManager();

            // Update Wallet
            //TODO:: Get the users wallet.. for now use the demo
            $wallet = $em->getRepository(Wallet::class)->find($user->getId());
            $wallet->withdraw('USD', $data['usd']);
            $wallet->withdraw('CAN', $data['can']);
            $em->persist($wallet);
            $em->flush();

            // Create Transaction
            if($data['usd'] != 0){
                $transactionUsd = new Transaction();
                $date = new \DateTime();
                $transactionUsd->setUser($user);
                $transactionUsd->setCurrency(2);
                $transactionUsd->setType(3);
                $transactionUsd->setName('Widthdrawl from Wallet');
                $transactionUsd->setAmount( $data['usd'] * -1 );
                $transactionUsd->setDate($date);
                $em->persist($transactionUsd);
                $em->flush();
            }

            if($data['can'] != 0){
                $transactionCan = new Transaction();
                $date = new \DateTime();
                $transactionCan->setUser($user);
                $transactionCan->setCurrency(1);
                $transactionCan->setType(3);
                $transactionCan->setName('Widthdrawl from Wallet');
                $transactionCan->setAmount( $data['can'] * -1 );
                $transactionCan->setDate($date);
                $em->persist($transactionCan);
                $em->flush();
            }
            
            $em->flush();

           return $this->redirectToRoute('dashboard');
        }

        return $this->render('form/index.html.twig', [
            'page_title' => 'Withdrawl from Wallet',
            'form' => $form->createView(),
            'error' => "",
            'settings' => $settings,
        ]);
    }

    #[Route('/wallet/deposit', name: 'wallet_deposit')]
    public function deposit(ManagerRegistry $doctrine, Request $request): Response
    {
        $user = $this->getUser();
        $settings = $user->getSettings();
        $form = $this->createForm(WalletAdjustmentType::class);
        $form->handleRequest($request);

        if($form->isSubmitted()){
            $data = $form->getData();
            $em = $doctrine->getManager();

            // Check if we need to update 10%
            ToolsController::updateTenPercentPlan( $user, $doctrine );

            // Update Wallet
            $wallet = $em->getRepository(Wallet::class)->find($user->getId());
            $wallet->deposit('USD', $data['usd']);
            $wallet->deposit('CAN', $data['can']);
            $em->persist($wallet);
            $em->flush();

            // Create Transaction
            if($data['usd'] != 0){
                $transactionUsd = new Transaction();
                $date = new \DateTime();
                $transactionUsd->setUser($user);
                $transactionUsd->setCurrency(2);
                $transactionUsd->setType(4);
                $transactionUsd->setName('Deposit to Wallet');
                $transactionUsd->setAmount( $data['usd']);
                $transactionUsd->setDate($date);
                $em->persist($transactionUsd);
                $em->flush();
            }

            if($data['can'] != 0){
                $transactionCan = new Transaction();
                $date = new \DateTime();
                $transactionCan->setUser($user);
                $transactionCan->setCurrency(1);
                $transactionCan->setType(4);
                $transactionCan->setName('Deposit to Wallet');
                $transactionCan->setAmount( $data['can']);
                $transactionCan->setDate($date);
                $em->persist($transactionCan);
                $em->flush();
            }
            
            $em->flush();


            return $this->redirectToRoute('dashboard');
        }

        return $this->render('form/index.html.twig', [
            'page_title' => 'Deposit to Wallet',
            'form' => $form->createView(),
            'error' => "",
            'settings' => $settings,
        ]);
    }

    #[Route('/wallet/convert', name: 'wallet_convert')]
    public function convert(ManagerRegistry $doctrine, Request $request): Response
    {
        $user = $this->getUser();
        $settings = $user->getSettings();
        $form = $this->createForm(WalletConvertType::class);
        $form->handleRequest($request);

        if($form->isSubmitted()){
            $data = $form->getData();
            $em = $doctrine->getManager();
            
            // Check if we need to update 10%
            ToolsController::updateTenPercentPlan( $user, $doctrine );

            // Update Wallet
            $wallet = $em->getRepository(Wallet::class)->find($user->getId());

            // Create Transaction
            if($data['type'] === 5){
                $wallet->withdraw('CAN', $data['can']);
                $wallet->deposit('USD', $data['usd']);
                $transaction = new Transaction();
                $date = new \DateTime();
                $transaction->setUser($user);
                $transaction->setCurrency(3);
                $transaction->setType(5);
                $transaction->setName('Currency Conversion (CAN to USD)');
                $transaction->setAmount(0);
                $transaction->setConvertCdn( $data['can']);
                $transaction->setConvertUsd( $data['usd']);
                $transaction->setDate($date);
            }

            if($data['type'] === 6){
                $wallet->withdraw('USD', $data['usd']);
                $wallet->deposit('CAN', $data['can']);
                $transaction = new Transaction();
                $date = new \DateTime();
                $transaction->setUser($user);
                $transaction->setCurrency(3);
                $transaction->setType(6);
                $transaction->setName('Currency Conversion (USD to CDN)');
                $transaction->setAmount(0);
                $transaction->setConvertCdn( $data['can']);
                $transaction->setConvertUsd( $data['usd']);
                $transaction->setDate($date);
            }

            $em->persist($transaction);
            $em->persist($wallet);
            $em->flush();

            return $this->redirectToRoute('dashboard');
        }

        return $this->render('form/currency_convert.html.twig', [
            'page_title' => 'Convert Currency',
            'form_title' => 'Convert Currency',
            'error' => false,
            'form' => $form->createView(),
            'settings' => $settings,
        ]);
    }

    //// PROFIT WALLET...

    #[Route('/wallet/percent/withdrawl', name: 'wallet_percent_withdrawl')]
    public function percentWithdrawl(ManagerRegistry $doctrine, Request $request): Response
    {
        $user = $this->getUser();
        $settings = $user->getSettings();
        $form = $this->createForm(WalletAdjustmentType::class);
        $form->handleRequest($request);

        if($form->isSubmitted()){
            $data = $form->getData();
            $em = $doctrine->getManager();

            // Update Wallet
            //TODO:: Get the users wallet.. for now use the demo
            $wallet = $em->getRepository(Wallet::class)->find($user->getId());
            $wallet->percentWithdraw('USD', $data['usd']);
            $wallet->percentWithdraw('CAN', $data['can']);
            $em->persist($wallet);
            $em->flush();

            return $this->redirectToRoute('dashboard');
        }

        return $this->render('form/index.html.twig', [
            'form_title' => 'Withdrawl from Profit Wallet',
            'page_title' => 'Withdrawl from Profit Wallet',
            'form' => $form->createView(),
            'error' => "",
            'settings' => $settings,
        ]);
    }

    #[Route('/wallet/percent/deposit', name: 'wallet_percent_deposit')]
    public function percentDeposit(ManagerRegistry $doctrine, Request $request): Response
    {
        $user = $this->getUser();
        $settings = $user->getSettings();
        $form = $this->createForm(WalletAdjustmentType::class);
        $form->handleRequest($request);

        if($form->isSubmitted()){
            $data = $form->getData();
            $em = $doctrine->getManager();

            // Update Wallet
            $wallet = $em->getRepository(Wallet::class)->find($user->getId());
            $wallet->percentDeposit('USD', $data['usd']);
            $wallet->percentDeposit('CAN', $data['can']);
            $em->persist($wallet);
            $em->flush();

            return $this->redirectToRoute('dashboard');
        }

        return $this->render('form/index.html.twig', [
            'page_title' => 'Deposit to Profit Wallet',
            'form_title' => 'Deposit to Profit Wallet',
            'form' => $form->createView(),
            'error' => "",
            'settings' => $settings,
        ]);
    }

    /// LOCKED WALLET...
    #[Route('/wallet/unlockall', name: 'wallet_unlock_all')]
    public function unlockAll(ManagerRegistry $doctrine, Request $request): Response
    {
        $user = $this->getUser();
        $em = $doctrine->getManager();

        // Update Wallet
        //TODO:: Get the users wallet.. for now use the demo
        $wallet = $em->getRepository(Wallet::class)->find($user->getId());
        $wallet->unlock('USD', $wallet->getLockedUsd());
        $wallet->unlock('CAN', $wallet->getLockedCdn());
        $em->persist($wallet);
        $em->flush();
        return $this->redirectToRoute('dashboard');
    }

    #[Route('/wallet/unlockusd', name: 'wallet_unlock_usd')]
    public function unlockUsd(ManagerRegistry $doctrine, Request $request): Response
    {
        $user = $this->getUser();
        $em = $doctrine->getManager();

        // Update Wallet
        //TODO:: Get the users wallet.. for now use the demo
        $wallet = $em->getRepository(Wallet::class)->find($user->getId());
        $wallet->unlock('USD', $wallet->getLockedUsd());
        $em->persist($wallet);
        $em->flush();
        return $this->redirectToRoute('dashboard');
    }

    #[Route('/wallet/unlockcdn', name: 'wallet_unlock_cdn')]
    public function unlockCdn(ManagerRegistry $doctrine, Request $request): Response
    {
        $user = $this->getUser();
        $em = $doctrine->getManager();

        // Update Wallet
        //TODO:: Get the users wallet.. for now use the demo
        $wallet = $em->getRepository(Wallet::class)->find($user->getId());
        $wallet->unlock('CAN', $wallet->getLockedCdn());
        $em->persist($wallet);
        $em->flush();
        return $this->redirectToRoute('dashboard');
    }

    #[Route('/wallet/locktransfer', name: 'wallet_lock_transfer')]
    public function locktransfer(ManagerRegistry $doctrine, Request $request): Response
    {
        $user = $this->getUser();
        $em = $doctrine->getManager();
        $settings = $user->getSettings();

        // Update Wallet
        //TODO:: Get the users wallet.. for now use the demo
        $wallet = $em->getRepository(Wallet::class)->find($user->getId());
        $wallet->unlock('CAN', $wallet->getLockedCdn());
        $form = $this->createForm(WalletLockedTransferType::class);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $em = $doctrine->getManager();

            $currency = $form->get("currency")->getData();
            $type = $form->get("type")->getData();
            $amount = $form->get("amount")->getData();

            $wallet->transfer($currency, $type, $amount);

            $em->persist($wallet);
            $em->flush();
            return $this->redirectToRoute('dashboard');
        } else {
            foreach ($form->getErrors(true) as $error) {
                echo $error->getMessage();
            }
        }

        return $this->render('form/lock_transfer.html.twig', [
            'error' => '',
            'form' => $form->createView(),
            'settings' => $settings,
        ]);
    }
}
