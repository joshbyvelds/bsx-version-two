<?php

namespace App\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use App\Form\StockType;
use App\Entity\Stock;

class StockController extends AbstractController
{
    #[Route('/stocks', name: 'stocks')]
    public function index(): Response
    {
        return $this->render('stock/index.html.twig', [
            'controller_name' => 'StockController',
        ]);
    }

    #[Route('/stocks/add', name: 'stocks_add')]
    public function add(ManagerRegistry $doctrine, Request $request): Response
    {
        $stock = new Stock();
        $form = $this->createForm(StockType::class, $stock);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $stock->setUser($this->getUser());
            $stock->setEarned(0);

            $em = $doctrine->getManager();
            $em->persist($stock);
            $em->flush();

            return $this->redirectToRoute('stocks');
        }

        return $this->render('form/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
