<?php

namespace App\Controller;


use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Settings;
use App\Form\SettingsType;

class SettingsController extends AbstractController
{
    #[Route('/settings', name: 'settings')]
    public function index(ManagerRegistry $doctrine, Request $request): Response
    {
        $user = $this->getUser();
        $em = $doctrine->getManager();
        $settings = $em->getRepository(Settings::class)->find($user->getId());


        $form = $this->createForm(SettingsType::class, $settings);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            return $this->redirectToRoute('dashboard');
        }

        return $this->render('settings/index.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
