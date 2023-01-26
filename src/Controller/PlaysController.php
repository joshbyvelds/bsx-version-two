<?php

namespace App\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use App\Form\PlayType;
use App\Entity\Play;

class PlaysController extends AbstractController
{
    #[Route('/plays', name: 'app_plays')]
    public function index(): Response
    {
        return $this->render('plays/index.html.twig', [
            'controller_name' => 'PlaysController',
        ]);
    }

    #[Route('/plays/add', name: 'app_plays_add')]
    public function add(ManagerRegistry $doctrine, Request $request): Response
    {
        $error = "";
        $play = new Play();
        $form = $this->createForm(PlayType::class, $play);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $doctrine->getManager();
            $data = $form->getData();

            $em->persist($play);
            $em->flush();
            return $this->redirectToRoute('dashboard');
        }

        return $this->render('form/index.html.twig', [
            'form' => $form->createView(),
            'error' => '',
            'controller_name' => 'PlaysController',
        ]);
    }
}
