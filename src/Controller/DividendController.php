<?php

namespace App\Controller;

use App\Entity\Dividend;
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
        return $this->render('dividend/index.html.twig', [
            'controller_name' => 'DividendController',
        ]);
    }

    #[Route('/dividends/add', name: 'dividends_add')]
    public function add(ManagerRegistry $doctrine, Request $request): Response
    {
        $dividend = new Dividend();
        $form = $this->createForm(DividendType::class, $dividend);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $doctrine->getManager();
            $em->persist($dividend);
            $em->flush();

            return $this->redirectToRoute('dividends');
        }

        return $this->render('form/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
