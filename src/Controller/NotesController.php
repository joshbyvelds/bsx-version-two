<?php

namespace App\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use App\Form\NoteType;
use App\Entity\Note;


class NotesController extends AbstractController
{
    #[Route('/notes', name: 'app_notes')]
    public function index(ManagerRegistry $doctrine): Response
    {
        $user = $this->getUser();
        $settings = $user->getSettings();
        $allNotes = $doctrine->getRepository(Note::class)->findAll();

        return $this->render('notes/index.html.twig', [
            'controller_name' => 'NotesController',
            'user' => $user->getId(),
            'notes' => $allNotes,
            'settings' => $settings,
        ]);
    }

    #[Route('/notes/write', name: 'app_notes_write')]
    public function write(ManagerRegistry $doctrine, Request $request): Response
    {
        $user = $this->getUser();
        $settings = $user->getSettings();
        $error = "";
        $note = new Note();
        $form = $this->createForm(NoteType::class, $note);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $doctrine->getManager();
            $data = $form->getData();
            $user = $user = $this->getUser();
            $note->setUser($user);

            $em->persist($note);
            $em->flush();
            return $this->redirectToRoute('app_notes');
        }

        return $this->render('form/index.html.twig', [
            'settings' => $settings,
            'form' => $form->createView(),
            'error' => '',
            'controller_name' => 'NotesController',
        ]);
    }
}
