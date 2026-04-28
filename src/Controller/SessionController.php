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
use App\Entity\Inscrire;
use App\Repository\InscrireRepository;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\MailerService;



#[Route('/session')]
final class SessionController extends AbstractController
{
    #[Route(name: 'app_session_index', methods: ['GET'])]
    public function index(SessionRepository $sessionRepository): Response
    {
        return $this->render('session/index.html.twig', [
            'sessions' => $sessionRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_session_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $session = new Session();
        $form = $this->createForm(SessionType::class, $session);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($session);
            $entityManager->flush();

            return $this->redirectToRoute('app_session_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('session/new.html.twig', [
            'session' => $session,
            'form' => $form,
        ]);
    }
                    #[Route('/calendar', name: 'app_session_calendar', methods: ['GET'])]
        public function calendar(SessionRepository $sessionRepository, InscrireRepository $inscrireRepository): Response
        {
            $sessions = $sessionRepository->findAll();
            $user = $this->getUser();

            $events = [];
            foreach ($sessions as $session) {
                $start = \DateTime::createFromFormat(
                    'Y-m-d H:i:s',
                    $session->getDateDeb()->format('Y-m-d') . ' ' . $session->getHeureDeb()->format('H:i:s')
                );
                $end = \DateTime::createFromFormat(
                    'Y-m-d H:i:s',
                    $session->getDateFin()->format('Y-m-d') . ' ' . $session->getHeureFin()->format('H:i:s')
                );

                $placesRestantes = $session->getNbPlace() - count($session->getInscrires());

                // Liste des participants
                $participants = [];
                foreach ($session->getInscrires() as $inscrire) {
                    $m = $inscrire->getMembre();
                    $participants[] = $m->getPrenom() . ' ' . $m->getNom();
                }

                // Est-ce que l'user courant est inscrit ?
                $dejainscrit = false;
                if ($user) {
                    $dejainscrit = $inscrireRepository->findOneBy([
                        'membre' => $user,
                        'session' => $session,
                    ]) !== null;
                }

                $events[] = [
                    'id'              => $session->getId(),
                    'title'           => $session->getActivite()?->getNom() ?? 'Sans activité',
                    'start'           => $start->format('Y-m-d\TH:i:s'),
                    'end'             => $end->format('Y-m-d\TH:i:s'),
                    'placesTotal'     => $session->getNbPlace(),
                    'placesRestantes' => $placesRestantes,
                    'participants'    => $participants,
                    'dejaInscrit'     => $dejainscrit,
                    'color'           => $placesRestantes <= 0 ? '#dc3545' : ($placesRestantes <= 3 ? '#fd7e14' : '#198754'),
                ];
            }

            return $this->render('session/calendar.html.twig', [
                'events' => json_encode($events),
            ]);
        }
    #[Route('/{id}', name: 'app_session_show', methods: ['GET'])]
    public function show(Session $session): Response
    {
        return $this->render('session/show.html.twig', [
            'session' => $session,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_session_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Session $session, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SessionType::class, $session);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_session_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('session/edit.html.twig', [
            'session' => $session,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_session_delete', methods: ['POST'])]
    public function delete(Request $request, Session $session, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$session->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($session);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_session_index', [], Response::HTTP_SEE_OTHER);
    }


    #[Route('/{id}/reserver', name: 'app_session_reserver', methods: ['POST'])]
#[IsGranted('ROLE_USER')]
public function reserver(Session $session, EntityManagerInterface $entityManager, InscrireRepository $inscrireRepository, MailerService $mailerService): Response
{
    $membre = $this->getUser();

    $dejaInscrit = $inscrireRepository->findOneBy(['membre' => $membre, 'session' => $session]);
    if ($dejaInscrit) {
        $this->addFlash('warning', 'Vous êtes déjà inscrit à cette session.');
        return $this->redirectToRoute('app_session_calendar');
    }

    $nbInscrits = count($session->getInscrires());
    if ($nbInscrits >= $session->getNbPlace()) {
        $this->addFlash('danger', 'Plus de places disponibles.');
        return $this->redirectToRoute('app_session_calendar');
    }

    $inscrire = new Inscrire();
    $inscrire->setMembre($membre);
    $inscrire->setSession($session);
    $entityManager->persist($inscrire);
    $entityManager->flush();

    // 👇 Envoi du mail de confirmation
    $mailerService->sendConfirmation($membre, $session);

    $this->addFlash('success', 'Réservation confirmée ! Un email vous a été envoyé.');
    return $this->redirectToRoute('app_session_calendar');
}


#[Route('/{id}/annuler', name: 'app_session_annuler', methods: ['POST'])]
#[IsGranted('ROLE_USER')]
public function annuler(Session $session, EntityManagerInterface $entityManager, InscrireRepository $inscrireRepository, MailerService $mailerService): Response
{
    $membre = $this->getUser();

    $inscrire = $inscrireRepository->findOneBy(['membre' => $membre, 'session' => $session]);
    if ($inscrire) {
        $entityManager->remove($inscrire);
        $entityManager->flush();

        // 👇 Envoi du mail d'annulation
        $mailerService->sendAnnulation($membre, $session);

        $this->addFlash('success', 'Réservation annulée. Un email vous a été envoyé.');
    }

    return $this->redirectToRoute('app_session_calendar');
}

}
