<?php

namespace App\Controller;

use App\Entity\Session;
use App\Form\SessionType;
use App\Repository\SessionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/entraineur')]
#[IsGranted('ROLE_ENTRAINEUR')]
class EntraineurController extends AbstractController
{
    #[Route('/planning', name: 'app_entraineur_planning', methods: ['GET'])]
    public function planning(SessionRepository $sessionRepository): Response
    {
        $entraineur = $this->getUser();
        $sessions = $sessionRepository->findByEntraineur($entraineur);

        return $this->render('entraineur/planning.html.twig', [
            'sessions' => $sessions,
        ]);
    }

    #[Route('/session/{id}/edit', name: 'app_entraineur_session_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Session $session, EntityManagerInterface $entityManager): Response
    {
        // Vérifier que la session appartient bien à cet entraîneur
        if ($session->getEntraineur() !== $this->getUser()) {
            $this->addFlash('danger', 'Vous ne pouvez pas modifier cette session.');
            return $this->redirectToRoute('app_entraineur_planning');
        }

        $form = $this->createForm(SessionType::class, $session);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Session modifiée avec succès.');
            return $this->redirectToRoute('app_entraineur_planning');
        }

        return $this->render('entraineur/edit.html.twig', [
            'session' => $session,
            'form' => $form,
        ]);
    }

    #[Route('/session/{id}/annuler', name: 'app_entraineur_session_annuler', methods: ['POST'])]
    public function annuler(Request $request, Session $session, EntityManagerInterface $entityManager): Response
    {
        // Vérifier que la session appartient bien à cet entraîneur
        if ($session->getEntraineur() !== $this->getUser()) {
            $this->addFlash('danger', 'Vous ne pouvez pas annuler cette session.');
            return $this->redirectToRoute('app_entraineur_planning');
        }

        if ($this->isCsrfTokenValid('annuler'.$session->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($session);
            $entityManager->flush();
            $this->addFlash('success', 'Session annulée avec succès.');
        }

        return $this->redirectToRoute('app_entraineur_planning');
    }
}