<?php

namespace App\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Wallet;
use App\Form\WalletAdjustmentType;

class WalletController extends AbstractController
{
    #[Route('/wallet/withdrawl', name: 'wallet_withdrawl')]
    public function withdrawl(ManagerRegistry $doctrine, Request $request): Response
    {
        
        $form = $this->createForm(WalletAdjustmentType::class);
        $form->handleRequest($request);

        if($form->isSubmitted()){
            $data = $form->getData();;
            $em = $doctrine->getManager();
            //TODO:: Get the users wallet.. for now use the demo
            $wallet = $em->getRepository(Wallet::class)->find(1);
            $wallet->withdraw('USD', $data['usd']);
            $wallet->withdraw('CAN', $data['can']);
            
            $em->persist($wallet);
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
            //TODO:: Get the users wallet.. for now use the demo
            $wallet = $em->getRepository(Wallet::class)->find(1);
            $wallet->deposit('USD', $data['usd']);
            $wallet->deposit('CAN', $data['can']);
            
            $em->persist($wallet);
            $em->flush();

            return $this->redirectToRoute('dashboard');
        }

        return $this->render('form/index.html.twig', [
            'page_title' => 'deposit to Wallet',
            'form' => $form->createView(),
        ]);
    }
}
