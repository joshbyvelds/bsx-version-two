<?php

namespace App\Controller;

use App\Entity\ShareBuy;
use App\Entity\Option;
use App\Entity\Play;
use App\Form\PlayType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;





class PlaysController extends AbstractController
{
    #[Route('/plays', name: 'app_plays')]
    public function index(ManagerRegistry $doctrine): Response
    {
        $user = $this->getUser();
        $settings = $user->getSettings();
        $plays = $user->getPlays();
        return $this->render('plays/index.html.twig', [
            'plays' => $plays,
            'controller_name' => 'PlaysController',
            'settings' => $settings,
        ]);
    }

    #[Route('/plays/add', name: 'app_plays_add')]
    public function add(ManagerRegistry $doctrine, Request $request): Response
    {
        $user = $this->getUser();
        $settings = $user->getSettings();
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
                $play->setFinished(false);
                $play->setEarned(0);
                $play->setUser($user);
                $em->persist($play);
                $em->flush();
                return $this->redirectToRoute('dashboard');
            }
        }

        return $this->render('form/plays_add.html.twig', [
            'form' => $form->createView(),
            'error' => $error,
            'controller_name' => 'PlaysController',
            'settings' => $settings,
        ]);
    }

    #[Route('/plays/edit/{id}', name: 'edit_plays')]
    public function edit(int $id, ManagerRegistry $doctrine, Request $request): Response
    {
        $play = $doctrine->getRepository(Play::class)->find($id);
        $user = $this->getUser();
        $settings = $user->getSettings();

        if( $play === null ){
            return $this->redirectToRoute('dashboard');
        }

        if( $user->getId() !== $play->getUser()->getId() ){
            return $this->redirectToRoute('dashboard');
        }

        $form = $this->createForm(PlayType::class, $play);
        $form->handleRequest($request);

        if($form->isSubmitted()){
            $now = new \DateTime();
            $em = $doctrine->getManager();
            $em->persist($play);
            $em->flush();
            return $this->redirectToRoute('dashboard');
        }

        return $this->render('form/plays_edit.html.twig', [
            'page_title' => 'Create New Portfolio',
            'form' => $form->createView(),
            'error' => "",
            'settings' => $settings,
        ]);
    }

    #[Route('/plays/selectstock', name: 'app_plays_selectstock')]
    public function selectstock(ManagerRegistry $doctrine, Request $request): Response
    {
        // get play from database..
        $stock = $request->get('stockid');
        $shares = $doctrine->getRepository(ShareBuy::class)->findBy(['stock' => $stock]);
        $options = $doctrine->getRepository(Option::class)->findBy(['stock' => $stock, 'expired' => 0]);

        $refined_shares = [];
        foreach ($shares as $share) {
            $refined_shares[] = ['id' => $share->getId(), 'name' => $share->getStock()->getName(), 'date' => $share->getDate()->format("F j, Y"), 'amount' => $share->getAmount(), 'price' => $share->getPrice()];
        }

        $refined_options = [];
        foreach ($options as $option) {
            $refined_options[] = ['id' => $option->getId(), 'name' => $option->getStock()->getName(), 'date' => $option->getExpiry()->format("F j, Y"), 'contracts' => $option->getContracts(), 'type'=> $option->getType(), 'strike' => number_format($option->getStrike(),2)];
        }

        dump($shares);
        dump($options);

        dump($refined_shares);
        dump($refined_options);

        $response = new JsonResponse();
        $response->setData([
            "shares" => $refined_shares,
            "options" => $refined_options,
        ]);

        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    #[Route('/plays/update', name: 'app_plays_update')]
    public function update(ManagerRegistry $doctrine, Request $request): Response
    {
        // get play from database..
        $id = $request->get('playid');
        $play = $doctrine->getRepository(Play::class)->find($id);

        $shares = $play->getShares();
        $options = $play->getOptions();
        $contracts = 0;
        $shareBuys = 0;

        foreach ($options as $option) {
            $contracts += (int)$option->getContracts();
        }

        foreach ($options as $shareBuy) {
            $shareBuys += ((int)$shareBuy->getAmount() - (int)$shareBuy->getSold());
        }

        dump($shares);
        dump($options);


        // get if it's a share or option play

        // if share
            // get new price
            // get bought price
            // see if us to canada currency
            // calculate new profit amount..

        // if option
            // get new price
            // get bought price
            // see if us to canada currency
            // calculate new profit amount..

        
        $response = new JsonResponse();
        $response->setData([
            "id" => $id,
            "profit" => 123,
        ]);

        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
}
