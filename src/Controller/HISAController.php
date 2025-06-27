<?php

namespace App\Controller;

use App\Entity\HighInterestSavingsAccount;
use App\Entity\Settings;
use App\Entity\Transaction;
use App\Entity\Wallet;
use App\Form\HighInterestSavingsAccountType;
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
}
