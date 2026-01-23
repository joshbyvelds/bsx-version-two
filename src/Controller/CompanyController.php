<?php

namespace App\Controller;

use App\Entity\Sector;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Company;
use App\Form\CompanyType;

class CompanyController extends AbstractController
{
    #[Route('/company/add', name: 'add_company')]
    public function index(ManagerRegistry $doctrine, Request $request): Response
    {
        $user = $this->getUser();
        $settings = $user->getSettings();
        $company = new Company();
        $form = $this->createForm(CompanyType::class, $company);
        $form->handleRequest($request);

        $last = new \DateTime('1999-01-01 12:12:12');

        $company->setBgColor("FFFFFF");
        $company->setLastPriceUpdate($last);
        $company->setDelisted(false);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $doctrine->getManager();
            $data = $form->getData();

            $sector = $em->getRepository(Sector::class)->find((int)$data['sector']);
            $company->setSector($sector);

            $em->persist($company);
            $em->flush();

            return $this->redirectToRoute('stocks_add');
        }

        return $this->render('form/company.html.twig', [
            'settings' => $settings,
            'controller_name' => 'CompanyController',
            'form' => $form->createView(),
            'error' => ''
        ]);
    }
}
