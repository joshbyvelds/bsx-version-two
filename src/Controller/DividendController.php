<?php

namespace App\Controller;

use App\Entity\Dividend;
use App\Entity\Transaction;
use App\Entity\Wallet;
use App\Form\DividendType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DividendController extends AbstractController
{
    #[Route('/dividends', name: 'dividends')]
    public function index(): Response
    {
        $user = $user = $this->getUser();
        $settings = $user->getSettings();
        $payments = $user->getDividends();
        dump($payments);

        return $this->render('dividend/index.html.twig', [
            'dividend_payments' => $payments,
            'settings' => $settings
        ]);
    }

    #[Route('/dividends/stock', name: 'dividends_stock')]
    public function stock(): Response
    {
        $user = $this->getUser();
        $stocks = $user->getStocks();
        $settings = $user->getSettings();
        dump($stocks);

        return $this->render('dividend/stock.html.twig', [
            'stocks' => $stocks,
            'settings' => $settings
        ]);
    }

    #[Route('/dividends/month', name: 'dividends_month')]
    public function month(): Response
    {
        $user = $this->getUser();
        $payments = $user->getDividends();
        $settings = $user->getSettings();
        dump($payments);

        return $this->render('dividend/month.html.twig', [
            'dividend_payments' => $payments,
            'settings' => $settings
        ]);
    }

    #[Route('/dividends/quarter', name: 'dividends_quarter')]
    public function quarter(): Response
    {
        $user = $this->getUser();
        $payments = $user->getDividends();
        $settings = $user->getSettings();
        dump($payments);

        return $this->render('dividend/quarter.html.twig', [
            'dividend_payments' => $payments,
            'settings' => $settings
        ]);
    }

    #[Route('/dividends/year', name: 'dividends_year')]
    public function year(): Response
    {
        $user = $this->getUser();
        $payments = $user->getDividends();
        $settings = $user->getSettings();
        dump($payments);

        return $this->render('dividend/year.html.twig', [
            'dividend_payments' => $payments,
            'settings' => $settings
        ]);
    }

    #[Route('/dividends/add', name: 'dividends_add')]
    public function add(ManagerRegistry $doctrine, Request $request): Response
    {
        $user = $this->getUser();
        $settings = $user->getSettings();
        $error = "";
        $dividend = new Dividend();
        $transaction = new Transaction();
        $dividend->setUser($this->getUser());
        $form = $this->createForm(DividendType::class, $dividend);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            $data = $form->getData();
            $em = $doctrine->getManager();

            // Update Wallet..
            $wallet = $em->getRepository(Wallet::class)->find($user->getId());

            // get currency
            if ($form->get("currency")->getData() === "can") {
                $wallet->deposit('CAN', $data->getAmount());
                $transaction->setCurrency(1);
            } else {
                $wallet->deposit('USD', $data->getAmount());
                $transaction->setCurrency(2);
            }

            // Create Transaction..
            $transaction->setUser($user);
            $transaction->setType(4);
            $transaction->setName('Dividend Payment - ' . $data->getStock()->getTicker());
            $transaction->setAmount($data->getAmount());
            $transaction->setDate($data->getPaymentDate());
            
            $em->persist($wallet);
            $em->persist($transaction);
            $em->persist($dividend);
            $em->flush();

            //return $this->redirectToRoute('dividends');
            return $this->redirectToRoute('dividends_add');
        }

        return $this->render('form/index.html.twig', [
            'form' => $form->createView(),
            'error' => $error,
            'settings' => $settings
        ]);
    }
}
