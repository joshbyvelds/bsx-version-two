<?php

namespace App\Controller;

use App\Entity\Dividend;
use App\Entity\Transaction;
use App\Entity\Stock;
use App\Entity\User;
use App\Entity\Wallet;
use App\Form\DividendType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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

    #[Route('/getDividendStocks/{id}', name: 'dividends_get_stocks')]
    public function getDiviStocks(ManagerRegistry $doctrine, Request $request, int $id): JsonResponse
    {

        $superadmin = ($this->isGranted('ROLE_SUPERADMIN'));

        if (!$superadmin) {
            return new JsonResponse([
                'stocks' => [],
            ]);
        }

        $em = $doctrine->getManager();
        $selectedUser = $em->getRepository(User::class)->find($id);

        if (!$selectedUser) {
            return new JsonResponse([
                'stocks' => [],
            ]);
        }

        $sql = "SELECT p.id AS id, c.ticker AS ticker, c.name AS name FROM stock p INNER JOIN company c ON p.company_id = c.id WHERE p.user_id = :user_id AND c.pays_dividend = :pays AND p.shares_owned > 0 ORDER BY c.ticker ASC";

        $myDiviStocks = $em->getConnection()->executeQuery($sql, [
            'user_id' => $selectedUser->getId(),
            'pays' => 1
        ])->fetchAllAssociative();

        $myDiviStocks = array_map(static fn(array $stock) => [
            'id' => (int) $stock['id'],
            'ticker' => $stock['ticker'],
            'name' => $stock['name'],
        ], $myDiviStocks);

        return new JsonResponse([
            'stocks' => $myDiviStocks,
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
        $superadmin = ($this->isGranted('ROLE_SUPERADMIN'));

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            $data = $form->getData();

            if ($superadmin) {
                $user = $em->getRepository(User::class)->find($form->get("Account")->getData());
            }

            // The ten percent wallet rules below belong to whoever the dividend is
            // being filed for, not to the (possibly super admin) user submitting it.
            $settings = $user->getSettings();

            $stock = $em->getRepository(Stock::class)->find($form->get("Stock")->getData());
            $dividend->setStock($stock);
            $dividend->setUser($user);



            // Update Wallet..
            $wallet = $em->getRepository(Wallet::class)->find($user->getId());

            $total = $data->getAmount();
            $currency = $form->get("currency")->getData();

            // get currency
            if ($currency === "can") {
                $wallet->deposit('CAN', $total);
                $transaction->setCurrency(1);
            } else {
                $wallet->deposit('USD', $total);
                $transaction->setCurrency(2);
            }

            // Create Transaction..
            $transaction->setUser($user);
            $transaction->setType(4);
            $transaction->setName('Dividend Payment - ' . $stock->getCompanyTicker());
            $transaction->setAmount($data->getAmount());
            $transaction->setDate($data->getPaymentDate());

            $profit_percent = $settings->getTenPercentDepositPercentage();

            if(($total * $profit_percent) > 0.01) {
                if ($settings->isUseTenPercentWallet() && $settings->isTenPercentAutoDeposit()) {
                    $profit_wallet_amount = round($total * $profit_percent, 2);
                    $wallet->percentDeposit(strtoupper($currency), $profit_wallet_amount);
                }
            }

            $em->persist($wallet);
            $em->persist($transaction);
            $em->persist($dividend);
            $em->flush();

            //return $this->redirectToRoute('dividends');
            return $this->redirectToRoute('dividends_add');
        }

        $sql = "SELECT p.id AS id, c.ticker AS ticker, c.name AS name FROM stock p INNER JOIN company c ON p.company_id = c.id WHERE p.user_id = :user_id AND c.pays_dividend = :pays AND p.shares_owned > 0 ORDER BY c.ticker ASC";

        $myDiviStocks = $em->getConnection()->executeQuery($sql, [
            'user_id' => $user->getId(),
            'pays' => 1
        ])->fetchAllAssociative();

        $myDiviStocks = array_map(static fn(array $stock) => [
            'id' => (int) $stock['id'],
            'ticker' => $stock['ticker'],
            'name' => $stock['name'],
        ], $myDiviStocks);



        $currentUser = $this->getUser();

        return $this->render('form/dividend.html.twig', [
            'super_admin' => $superadmin,
            'form' => $form->createView(),
            'stocks' => $myDiviStocks,
            'account' => [
                'id' => $currentUser->getId(),
                'name' => $currentUser->getRealname(),
                'username' => $currentUser->getUsername(),
            ],
            'error' => $error,
            'settings' => $settings
        ]);
    }
}
