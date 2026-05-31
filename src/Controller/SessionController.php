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

/**
 * SessionController — Gère tout ce qui concerne les sessions sportives.
 *
 * Une "session" = un créneau planifié d'une activité sportive
 * (ex: cours de yoga le lundi à 10h avec 15 places).
 *
 * Ce controller gère :
 *   - L'affichage de la liste et du calendrier des sessions
 *   - La création / modification / suppression (réservé aux admins)
 *   - La réservation et l'annulation par les membres
 */
#[Route('/session')]
final class SessionController extends AbstractController
{
    /**
     * Affiche la liste de toutes les sessions sous forme de tableau.
     * Route : GET /session
     */
    #[Route(name: 'app_session_index', methods: ['GET'])]
    public function index(SessionRepository $sessionRepository): Response
    {
        // On récupère toutes les sessions depuis la base de données
        return $this->render('session/index.html.twig', [
            'sessions' => $sessionRepository->findAll(),
        ]);
    }

    /**
     * Crée une nouvelle session.
     * Route : GET /session/new (affiche le formulaire)
     *         POST /session/new (traite le formulaire soumis)
     */
    #[Route('/new', name: 'app_session_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        // On crée un objet Session vide qui sera rempli par le formulaire
        $session = new Session();

        // On génère le formulaire Symfony lié à cet objet
        $form = $this->createForm(SessionType::class, $session);

        // On analyse la requête HTTP : si c'est un POST, le formulaire est soumis
        $form->handleRequest($request);

        // Si le formulaire est soumis ET valide (toutes les contraintes respectées)
        if ($form->isSubmitted() && $form->isValid()) {
            // On dit à Doctrine de préparer l'insertion en base
            $entityManager->persist($session);
            // On exécute vraiment l'INSERT en base de données
            $entityManager->flush();

            // On redirige vers la liste des sessions
            return $this->redirectToRoute('app_session_index', [], Response::HTTP_SEE_OTHER);
        }

        // Si formulaire pas encore soumis (GET) ou invalide : on affiche la page
        return $this->render('session/new.html.twig', [
            'session' => $session,
            'form' => $form,
        ]);
    }

    /**
     * Affiche le calendrier interactif des sessions (FullCalendar).
     * Route : GET /session/calendar
     *
     * IMPORTANT : Cette route doit être AVANT /{id} sinon Symfony
     * confondrait "calendar" avec un ID numérique.
     */
    #[Route('/calendar', name: 'app_session_calendar', methods: ['GET'])]
    public function calendar(SessionRepository $sessionRepository, InscrireRepository $inscrireRepository): Response
    {
        $sessions = $sessionRepository->findAll();

        // On récupère l'utilisateur connecté (peut être null si non connecté)
        $user = $this->getUser();

        // On va construire un tableau JSON compréhensible par FullCalendar
        $events = [];

        foreach ($sessions as $session) {
            // FullCalendar a besoin d'une date+heure combinée (ex: 2026-05-30T10:00:00)
            // Or notre entité sépare date et heure → on les fusionne ici
            $start = \DateTime::createFromFormat(
                'Y-m-d H:i:s',
                $session->getDateDeb()->format('Y-m-d') . ' ' . $session->getHeureDeb()->format('H:i:s')
            );
            $end = \DateTime::createFromFormat(
                'Y-m-d H:i:s',
                $session->getDateFin()->format('Y-m-d') . ' ' . $session->getHeureFin()->format('H:i:s')
            );

            // Calcul des places restantes : total - nombre d'inscrits
            $placesRestantes = $session->getNbPlace() - count($session->getInscrires());

            // On récupère la liste des noms des participants
            $participants = [];
            foreach ($session->getInscrires() as $inscrire) {
                $m = $inscrire->getMembre();
                $participants[] = $m->getPrenom() . ' ' . $m->getNom();
            }

            // On vérifie si l'utilisateur connecté est déjà inscrit à cette session
            $dejainscrit = false;
            if ($user) {
                $dejainscrit = $inscrireRepository->findOneBy([
                    'membre' => $user,
                    'session' => $session,
                ]) !== null;
            }

            // On ajoute l'événement au tableau avec toutes les infos nécessaires
            $events[] = [
                'id'               => $session->getId(),
                'title'            => $session->getActivite()?->getNom() ?? 'Sans activité',
                'start'            => $start->format('Y-m-d\TH:i:s'),
                'end'              => $end->format('Y-m-d\TH:i:s'),
                'placesTotal'      => $session->getNbPlace(),
                'placesRestantes'  => $placesRestantes,
                'participants'     => $participants,
                'dejaInscrit'      => $dejainscrit,
                // Couleur dynamique : rouge si complet, orange si peu de places, vert sinon
                'color'            => $placesRestantes <= 0 ? '#dc3545' : ($placesRestantes <= 3 ? '#fd7e14' : '#198754'),
            ];
        }

        // On envoie le tableau en JSON au template Twig
        return $this->render('session/calendar.html.twig', [
            'events' => json_encode($events),
        ]);
    }

    /**
     * Affiche le détail d'une session.
     * Route : GET /session/{id}
     * Symfony injecte automatiquement l'objet Session correspondant à l'ID.
     */
    #[Route('/{id}', name: 'app_session_show', methods: ['GET'])]
    public function show(Session $session): Response
    {
        return $this->render('session/show.html.twig', [
            'session' => $session,
        ]);
    }

    /**
     * Modifie une session existante.
     * Route : GET /session/{id}/edit (affiche le formulaire pré-rempli)
     *         POST /session/{id}/edit (traite les modifications)
     */
    #[Route('/{id}/edit', name: 'app_session_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Session $session, EntityManagerInterface $entityManager): Response
    {
        // On crée le formulaire pré-rempli avec les données de la session existante
        $form = $this->createForm(SessionType::class, $session);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Pas besoin de persist() ici : Doctrine suit déjà cet objet (il vient de la BDD)
            // flush() suffit pour enregistrer les modifications
            $entityManager->flush();

            return $this->redirectToRoute('app_session_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('session/edit.html.twig', [
            'session' => $session,
            'form' => $form,
        ]);
    }

    /**
     * Supprime une session.
     * Route : POST /session/{id} (POST et non DELETE pour compatibilité HTML)
     * Le token CSRF protège contre les suppressions frauduleuses.
     */
    #[Route('/{id}', name: 'app_session_delete', methods: ['POST'])]
    public function delete(Request $request, Session $session, EntityManagerInterface $entityManager): Response
    {
        // On vérifie le token CSRF pour s'assurer que la demande vient bien de notre site
        if ($this->isCsrfTokenValid('delete' . $session->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($session);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_session_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * Permet à un membre de réserver une session.
     * Route : POST /session/{id}/reserver
     * Accessible uniquement aux utilisateurs connectés (ROLE_USER).
     */
    #[Route('/{id}/reserver', name: 'app_session_reserver', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function reserver(Session $session, EntityManagerInterface $entityManager, InscrireRepository $inscrireRepository, MailerService $mailerService): Response
    {
        // On récupère le membre connecté
        $membre = $this->getUser();

        // Vérification 1 : le membre est-il déjà inscrit à cette session ?
        $dejaInscrit = $inscrireRepository->findOneBy(['membre' => $membre, 'session' => $session]);
        if ($dejaInscrit) {
            $this->addFlash('warning', 'Vous êtes déjà inscrit à cette session.');
            return $this->redirectToRoute('app_session_calendar');
        }

        // Vérification 2 : reste-t-il des places disponibles ?
        $nbInscrits = count($session->getInscrires());
        if ($nbInscrits >= $session->getNbPlace()) {
            $this->addFlash('danger', 'Plus de places disponibles.');
            return $this->redirectToRoute('app_session_calendar');
        }

        // Tout est OK : on crée l'inscription (table inscrire = lien membre ↔ session)
        $inscrire = new Inscrire();
        $inscrire->setMembre($membre);
        $inscrire->setSession($session);
        $entityManager->persist($inscrire);
        $entityManager->flush();

        // On envoie un email de confirmation via le MailerService
        $mailerService->sendConfirmation($membre, $session);

        $this->addFlash('success', 'Réservation confirmée ! Un email vous a été envoyé.');
        return $this->redirectToRoute('app_session_calendar');
    }

    /**
     * Permet à un membre d'annuler sa réservation.
     * Route : POST /session/{id}/annuler
     * Accessible uniquement aux utilisateurs connectés (ROLE_USER).
     */
    #[Route('/{id}/annuler', name: 'app_session_annuler', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function annuler(Session $session, EntityManagerInterface $entityManager, InscrireRepository $inscrireRepository, MailerService $mailerService): Response
    {
        $membre = $this->getUser();

        // On cherche l'inscription du membre pour cette session
        $inscrire = $inscrireRepository->findOneBy(['membre' => $membre, 'session' => $session]);

        if ($inscrire) {
            // On supprime l'inscription → la place se libère automatiquement
            $entityManager->remove($inscrire);
            $entityManager->flush();

            // On envoie un email d'annulation
            $mailerService->sendAnnulation($membre, $session);

            $this->addFlash('success', 'Réservation annulée. Un email vous a été envoyé.');
        }

        return $this->redirectToRoute('app_session_calendar');
    }
}