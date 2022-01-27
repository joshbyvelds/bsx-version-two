<?php

namespace App\Controller;

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

class WalletController extends AbstractController
{
    #[Route('/wallet/withdrawl', name: 'wallet_withdrawl')]
    public function withdrawl(ManagerRegistry $doctrine, Request $request): Response
    {
        
        $form = $this->createForm(WalletAdjustmentType::class);
        $form->handleRequest($request);

        if($form->isSubmitted()){
            $data = $form->getData();
            $em = $doctrine->getManager();

            // Update Wallet
            //TODO:: Get the users wallet.. for now use the demo
            $wallet = $em->getRepository(Wallet::class)->find(1);
            $user = $em->getRepository(User::class)->find(10);
            $wallet->withdraw('USD', $data['usd']);
            $wallet->withdraw('CAN', $data['can']);
            $em->persist($wallet);
            $em->flush();

            // Create Transaction
            if($data['usd'] != 0){
                $transactionUsd = new Transaction();
                $date = new \DateTime();
                $transactionUsd->setUser($user);
                $transactionUsd->setType(3);
                $transactionUsd->setName('Widthdrawl from Wallet (USD)');
                $transactionUsd->setAmount( $data['usd'] * -1 );
                $transactionUsd->setDate($date);
                $em->persist($transactionUsd);
                $em->flush();
            }

            if($data['can'] != 0){
                $transactionCan = new Transaction();
                $date = new \DateTime();
                $transactionCan->setUser($user);
                $transactionCan->setType(3);
                $transactionCan->setName('Widthdrawl from Wallet (CAN)');
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
        ]);
    }

    #[Route('/wallet/deposit', name: 'wallet_deposit')]
    public function deposit(ManagerRegistry $doctrine, Request $request): Response
    {
        $form = $this->createForm(WalletAdjustmentType::class);
        $form->handleRequest($request);

        if($form->isSubmitted()){
            $data = $form->getData();
            $em = $doctrine->getManager();
            $user = $em->getRepository(User::class)->find(10);
           // Update Wallet
            //TODO:: Get the users wallet.. for now use the demo
            $wallet = $em->getRepository(Wallet::class)->find(1);
            $user = $em->getRepository(User::class)->find(10);
            $wallet->deposit('USD', $data['usd']);
            $wallet->deposit('CAN', $data['can']);
            $em->persist($wallet);
            $em->flush();

            // Create Transaction
            if($data['usd'] != 0){
                $transactionUsd = new Transaction();
                $date = new \DateTime();
                $transactionUsd->setUser($user);
                $transactionUsd->setType(4);
                $transactionUsd->setName('Deposit to Wallet (USD)');
                $transactionUsd->setAmount( $data['usd']);
                $transactionUsd->setDate($date);
                $em->persist($transactionUsd);
                $em->flush();
            }

            if($data['can'] != 0){
                $transactionCan = new Transaction();
                $date = new \DateTime();
                $transactionCan->setUser($user);
                $transactionCan->setType(4);
                $transactionCan->setName('Deposit to Wallet (CAN)');
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
        ]);
    }

    #[Route('/wallet/convert', name: 'wallet_convert')]
    public function convert(ManagerRegistry $doctrine, Request $request): Response
    {
        $form = $this->createForm(WalletConvertType::class);
        $form->handleRequest($request);

        if($form->isSubmitted()){
            $data = $form->getData();
            $em = $doctrine->getManager();
            $user = $em->getRepository(User::class)->find(10);
            // Update Wallet
            //TODO:: Get the users wallet.. for now use the demo
            $wallet = $em->getRepository(Wallet::class)->find(1);

            // Create Transaction
            if($data['type'] === 5){
                $wallet->withdraw('CAN', $data['can']);
                $wallet->deposit('USD', $data['usd']);
                $transaction = new Transaction();
                $date = new \DateTime();
                $transaction->setUser($user);
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

        return $this->render('form/index.html.twig', [
            'page_title' => 'Convert Currency',
            'form' => $form->createView(),
        ]);
    }
}
