<?php

namespace App\Controller;

use App\Entity\Dividend;
use App\Entity\Transaction;
use App\Entity\Stock;
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
    public function index(Request $request): Response
    {
        $user = $user = $this->getUser();
        $settings = $user->getSettings();
        $payments = $user->getDividends();
        $stocks = $user->getStocks();

        if ($request->query->get('type')){
            $type = $request->query->get('type');
        } else {
            $type = "payments";
        }

        return $this->render('dividend/index.html.twig', [
            'current_type' => $type,
            'dividend_payments' => $payments,
            'stocks' => $stocks,
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
        $em = $doctrine->getManager();

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            $data = $form->getData();

            $stock = $em->getRepository(Stock::class)->find($form->get("Stock")->getData());
            $dividend->setStock($stock);

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
            $transaction->setName('Dividend Payment - ' . $stock->getTicker());
            $transaction->setAmount($data->getAmount());
            $transaction->setDate($data->getPaymentDate());

            $em->persist($wallet);
            $em->persist($transaction);
            $em->persist($dividend);
            $em->flush();

            //return $this->redirectToRoute('dividends');
            return $this->redirectToRoute('dividends_add');
        }

        $myDiviStocks = $em->getConnection()->executeQuery(" SELECT * FROM stock p WHERE p.user_id = :user_id AND p.pays_dividend = :pays AND p.shares_owned > 0 ORDER BY p.id ASC", ['user_id' => $user->getId(), 'pays' => 1])->fetchAllAssociative();

        return $this->render('form/dividend.html.twig', [
            'form' => $form->createView(),
            'stocks' => $myDiviStocks,
            'error' => $error,
            'settings' => $settings
        ]);
    }
}
