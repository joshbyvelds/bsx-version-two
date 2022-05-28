<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ToolsController extends AbstractController
{
    // #[Route('/tools', name: 'app_tools')]
    // public function index(): Response
    // {
    //     return $this->render('tools/index.html.twig', [
    //         'controller_name' => 'ToolsController',
    //     ]);
    // }

    #[Route('/tools/daytradecalc', name: 'tools_daytrade_calc')]
    public function index(): Response
    {
        return $this->render('tools/daytrade_calc.html.twig', [
            'show_nav' => true,
            'controller_name' => 'ToolsController',
        ]);
    }
}
