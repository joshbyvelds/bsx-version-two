<?php

namespace App\Controller;

use App\Entity\TFSAContribution;
use App\Form\TFSAContributionType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TSFAController extends AbstractController
{
    #[Route('/tfsa', name: 'app_tfsa')]
    public function index(): Response
    {
        $user = $this->getUser();
        $settings = $user->getSettings();
        $tfsa = $user->getTFSAContributions();

        if($tfsa->isEmpty()) {
            return $this->redirectToRoute('app_tfsa_add');
        }

        return $this->render('tsfa/index.html.twig', [
            'contributions' => $tfsa,
            'settings' => $settings,
        ]);
    }

    #[Route('/tfsa/add', name: 'app_tfsa_add')]
    public function add(ManagerRegistry $doctrine, Request $request): Response
    {
        $user = $this->getUser();
        $settings = $user->getSettings();

        $tfsa = new TFSAContribution();
        $form = $this->createForm(TFSAContributionType::class, $tfsa);
        $form->handleRequest($request);

        if($form->isSubmitted()){
            $data = $form->getData();
            $em = $doctrine->getManager();
            $tfsa->setUser($user);
            $em->persist($tfsa);
            $em->flush();

            return $this->redirectToRoute('app_tfsa');
        }

        return $this->render('form/index.html.twig', [
            'controller_name' => 'TSFAController',
            'form' => $form->createView(),
            'error' => "",
            'settings' => $settings,
        ]);
    }
}
