<?php

namespace App\Controller;

use App\Entity\HighInterestSavingsAccount;
use App\Entity\Settings;
use App\Entity\Transaction;
use App\Entity\Wallet;
use App\Form\HighInterestSavingsAccountDepositType;
use App\Form\HighInterestSavingsAccountType;
use App\Form\HighInterestSavingsAccountWithdrawlType;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class HISAController extends AbstractController
{
    #[Route('/hisa/new', name: 'app_hisa_new')]
    public function add(ManagerRegistry $doctrine, Request $request): Response
    {
        $em = $doctrine->getManager();
        $user = $this->getUser();
        $settings = $user->getSettings();
        $hisa = new HighInterestSavingsAccount();
        $form = $this->createForm(HighInterestSavingsAccountType::class, $hisa);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $hisa->setUser($this->getUser());
            $currency = $form->get("currency")->getData();
            $amount= $form->get("amount")->getData();
            $wallet = $em->getRepository(Wallet::class)->find($user->getId());

            $hisa->setCurrency($currency);

            $transaction = new Transaction();
            $date = new DateTime();
            $transaction->setType(9);
            $transaction->setDate($date);
            $transaction->setUser($user);

            $transaction->setName('HISA Deposit of ' . ' ' . $hisa->getAmount());

            if ($currency == 'can'){
                $wallet->withdraw('CAN', $amount);
                $transaction->setCurrency(1);
            }

            if ($currency == 'usd'){
                $wallet->withdraw('USD', $amount);
                $transaction->setCurrency(2);
            }

            $transaction->setAmount($amount);

            $em->persist($transaction);
            $em->persist($hisa);
            $em->flush();

            return $this->redirectToRoute('dashboard');
        }

        return $this->render('form/hisa.html.twig', [
            'error' => "",
            'form' => $form->createView(),
            'settings' => $settings,
        ]);
    }

    #[Route('/hisa/deposit/{hisa_id}', name: 'app_hisa_deposit')]
    public function deposit(ManagerRegistry $doctrine, Request $request, int $hisa_id): Response
    {
        $em = $doctrine->getManager();
        $user = $this->getUser();
        $settings = $user->getSettings();
        $hisa = $em->getRepository(HighInterestSavingsAccount::class)->find($hisa_id);
        $form = $this->createForm(HighInterestSavingsAccountDepositType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $currency = $hisa->getCurrency();
            $amount= $form->get("amount_deposit")->getData();
            $wallet = $em->getRepository(Wallet::class)->find($user->getId());


            $transaction = new Transaction();
            $date = new DateTime();
            $transaction->setType(9);
            $transaction->setDate($date);
            $transaction->setUser($user);

            $transaction->setName('HISA Deposit of ' . ' $' . number_format($amount, 2));

            if ($currency == 'can'){
                $wallet->withdraw('CAN', $amount);
                $hisa->deposit($amount);
                $transaction->setCurrency(1);
            }

            if ($currency == 'usd'){
                $wallet->withdraw('USD', $amount);
                $hisa->deposit($amount);
                $transaction->setCurrency(2);
            }

            $transaction->setAmount($amount);
            $em->persist($transaction);
            $em->flush();

            return $this->redirectToRoute('dashboard');
        }

        return $this->render('form/index.html.twig', [
            'error' => "",
            'form' => $form->createView(),
            'settings' => $settings,
        ]);
    }


    #[Route('/hisa/withdrawl/{hisa_id}', name: 'app_hisa_withdrawl')]
    public function withdrawl(ManagerRegistry $doctrine, Request $request, int $hisa_id): Response
    {
        $em = $doctrine->getManager();
        $user = $this->getUser();
        $settings = $user->getSettings();
        $hisa = $em->getRepository(HighInterestSavingsAccount::class)->find($hisa_id);
        $form = $this->createForm(HighInterestSavingsAccountWithdrawlType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $currency = $hisa->getCurrency();
            $amount= $form->get("amount_withdrawn")->getData();
            $wallet = $em->getRepository(Wallet::class)->find($user->getId());

            $transaction = new Transaction();
            $date = new DateTime();
            $transaction->setType(9);
            $transaction->setDate($date);
            $transaction->setUser($user);

            $transaction->setName('HISA Withdrawl of ' . ' ' . $hisa->getAmount());

            if ($currency == 'can'){
                $wallet->deposit('CAN', $amount);
                $hisa->withdraw($amount);
                $transaction->setCurrency(1);
            }

            if ($currency == 'usd'){
                $wallet->deposit('USD', $amount);
                $hisa->withdraw($amount);
                $transaction->setCurrency(2);
            }

            $transaction->setAmount($amount);
            $em->persist($transaction);
            $em->flush();

            return $this->redirectToRoute('dashboard');
        }

        return $this->render('form/index.html.twig', [
            'error' => "",
            'form' => $form->createView(),
            'settings' => $settings,
        ]);
    }

    #[Route('/hisa/interest/{hisa_id}', name: 'app_hisa_interest')]
    public function interest(ManagerRegistry $doctrine, Request $request, int $hisa_id): Response
    {
        $em = $doctrine->getManager();
        $user = $this->getUser();
        $settings = $user->getSettings();
        $hisa = $em->getRepository(HighInterestSavingsAccount::class)->find($hisa_id);

        $currency = $hisa->getCurrency();
        $amount = $hisa->getAmount() * (($hisa->getInterestRate() / 100) / 12);
        $wallet = $em->getRepository(Wallet::class)->find($user->getId());

        $transaction = new Transaction();
        $date = new DateTime();
        $transaction->setType(9);
        $transaction->setDate($date);
        $transaction->setUser($user);

        $transaction->setName('HISA ('. $hisa->getName() .') Interest Payment');

        if ($currency == 'can'){
            $hisa->deposit($amount);
            $transaction->setCurrency(1);
        }

        if ($currency == 'usd'){
            $hisa->deposit($amount);
            $transaction->setCurrency(2);
        }

        $transaction->setAmount($amount);
        $em->persist($transaction);
        $em->flush();

        return $this->redirectToRoute('dashboard');
    }
}
