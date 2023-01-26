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
            
            // Make sure user has selected at least one shareBuy or Option
            if(count($data->getShares()) === 0 && count($data->getOptions()) === 0){
                dump("Nothing Selected");
            }

            $stock = $data->getStock();

            /*
             Since I'm not sure how to filter the collection dropdown in the form builder, 
             we are going to use vaildation here to make sure the share buys and options
             match the selected stock (and user). 
            */

            // Loop though each selected share buy
            foreach($data->getShares() as $sb){
                if($sb->getStock() != $stock){
                    $error = 'One of the selected share buys does not match the selected stock.';
                }
            }
            
            // Loop though each selected option
            foreach($data->getOptions() as $o){;
                if($o->getStock() != $stock){
                    $error = 'One of the selected options does not match the selected stock.';
                }
            }

            if($error === ""){
                $play->setUser($this->getUser());
                $em->persist($play);
                $em->flush();
                return $this->redirectToRoute('dashboard');
            }
        }

        return $this->render('form/index.html.twig', [
            'form' => $form->createView(),
            'error' => $error,
            'controller_name' => 'PlaysController',
        ]);
    }
}
