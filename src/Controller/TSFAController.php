<?php

namespace App\Controller;

use App\Entity\TFSAContribution;
use App\Entity\FHSAContribution;
use App\Entity\RRSPContribution;
use App\Form\TFSAContributionType;
use App\Form\FHSAContributionType;
use App\Form\RRSPContributionType;
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

        return $this->render('tax_accounts/tfsa.html.twig', [
            'contributions' => $tfsa,
            'settings' => $settings,
        ]);
    }

    #[Route('/fhsa', name: 'app_fhsa')]
    public function index2(): Response
    {
        $user = $this->getUser();
        $settings = $user->getSettings();
        $tfsa = $user->getFHSAContributions();

        if($tfsa->isEmpty()) {
            return $this->redirectToRoute('app_fhsa_add');
        }

        return $this->render('tax_accounts/fhsa.html.twig', [
            'contributions' => $tfsa,
            'settings' => $settings,
        ]);
    }

    #[Route('/rrsp', name: 'app_rrsp')]
    public function index3(): Response
    {
        $user = $this->getUser();
        $settings = $user->getSettings();
        $tfsa = $user->getRRSPContributions();

        if($tfsa->isEmpty()) {
            return $this->redirectToRoute('app_rrsp_add');
        }

        return $this->render('tax_accounts/rrsp.html.twig', [
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

    #[Route('/fhsa/add', name: 'app_fhsa_add')]
    public function add2(ManagerRegistry $doctrine, Request $request): Response
    {
        $user = $this->getUser();
        $settings = $user->getSettings();

        $tfsa = new FHSAContribution();
        $form = $this->createForm(FHSAContributionType::class, $tfsa);
        $form->handleRequest($request);

        if($form->isSubmitted()){
            $data = $form->getData();
            $em = $doctrine->getManager();
            $tfsa->setUser($user);
            $em->persist($tfsa);
            $em->flush();

            return $this->redirectToRoute('app_fhsa');
        }

        return $this->render('form/index.html.twig', [
            'controller_name' => 'TSFAController',
            'form' => $form->createView(),
            'error' => "",
            'settings' => $settings,
        ]);
    }

    #[Route('/rrsp/add', name: 'app_rrsp_add')]
    public function add3(ManagerRegistry $doctrine, Request $request): Response
    {
        $user = $this->getUser();
        $settings = $user->getSettings();

        $tfsa = new RRSPContribution();
        $form = $this->createForm(RRSPContributionType::class, $tfsa);
        $form->handleRequest($request);

        if($form->isSubmitted()){
            $data = $form->getData();
            $em = $doctrine->getManager();
            $tfsa->setUser($user);
            $em->persist($tfsa);
            $em->flush();

            return $this->redirectToRoute('app_rrsp');
        }

        return $this->render('form/index.html.twig', [
            'controller_name' => 'TSFAController',
            'form' => $form->createView(),
            'error' => "",
            'settings' => $settings,
        ]);
    }
}
