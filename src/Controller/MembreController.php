<?php

namespace App\Controller;

use App\Entity\Membre;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Form\MembreType;
use App\Repository\MembreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\MailerService;
use App\Repository\InscrireRepository;

/**
 * MembreController — Gère tout ce qui concerne les membres (utilisateurs).
 *
 * Ce controller gère :
 *   - La liste, création, modification, suppression des membres (admin)
 *   - Le profil personnel de l'utilisateur connecté
 *   - L'envoi d'emails groupés à tous les membres (admin uniquement)
 *
 * ATTENTION : Les routes sans {id} (profil, email-groupe) doivent être
 * déclarées AVANT les routes avec {id}, sinon Symfony confondrait
 * "profil" ou "admin" avec un identifiant numérique.
 */
#[Route('/membre')]
final class MembreController extends AbstractController
{
    /**
     * Affiche la liste de tous les membres.
     * Route : GET /membre
     */
    #[Route(name: 'app_membre_index', methods: ['GET'])]
    public function index(MembreRepository $membreRepository): Response
    {
        return $this->render('membre/index.html.twig', [
            'membres' => $membreRepository->findAll(),
        ]);
    }

    /**
     * Crée un nouveau membre (depuis l'interface admin).
     * Route : GET /membre/new (affiche le formulaire)
     *         POST /membre/new (traite le formulaire)
     *
     * Le mot de passe est haché (bcrypt) avant d'être sauvegardé en base.
     */
    #[Route('/new', name: 'app_membre_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $membre = new Membre();
        $form = $this->createForm(MembreType::class, $membre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Le champ plainPassword n'est pas lié à l'entité (mapped: false)
            // Il faut donc le récupérer manuellement et le hacher
            $plainPassword = $form->get('plainPassword')->getData();
            if ($plainPassword) {
                $hashedPassword = $passwordHasher->hashPassword($membre, $plainPassword);
                $membre->setPassword($hashedPassword);
            }

            $entityManager->persist($membre);
            $entityManager->flush();

            return $this->redirectToRoute('app_membre_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('membre/new.html.twig', [
            'membre' => $membre,
            'form' => $form,
        ]);
    }

    /**
     * Page de profil de l'utilisateur connecté.
     * Route : GET /membre/profil (affiche le profil + historique réservations)
     *         POST /membre/profil (sauvegarde les modifications)
     *
     * Accessible uniquement aux utilisateurs connectés (ROLE_USER).
     */
    #[Route('/profil', name: 'app_profil', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function profil(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, InscrireRepository $inscrireRepository): Response
    {
        // getUser() retourne l'objet Membre de l'utilisateur actuellement connecté
        $membre = $this->getUser();

        $form = $this->createForm(MembreType::class, $membre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            // On ne change le mot de passe que si l'utilisateur en a saisi un nouveau
            if ($plainPassword) {
                $hashedPassword = $passwordHasher->hashPassword($membre, $plainPassword);
                $membre->setPassword($hashedPassword);
            }
            $entityManager->flush();
            $this->addFlash('success', 'Profil mis à jour !');
            return $this->redirectToRoute('app_profil');
        }

        // On récupère toutes les réservations de ce membre pour l'historique
        $reservations = $inscrireRepository->findBy(['membre' => $membre]);

        return $this->render('membre/profil.html.twig', [
            'form' => $form,
            'reservations' => $reservations,
        ]);
    }

    /**
     * Envoie un email groupé à tous les membres et entraîneurs.
     * Route : GET /membre/admin/email-groupe (affiche le formulaire)
     *         POST /membre/admin/email-groupe (envoie les emails)
     *
     * Accessible uniquement aux administrateurs (ROLE_ADMIN).
     */
    #[Route('/admin/email-groupe', name: 'app_admin_email_groupe', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function emailGroupe(Request $request, MembreRepository $membreRepository, MailerService $mailerService): Response
    {
        if ($request->isMethod('POST')) {
            $sujet = $request->request->get('sujet');
            $contenu = $request->request->get('contenu');

            // On sélectionne uniquement les membres avec le rôle Utilisateur ou Entraîneur
            // (on exclut les admins qui n'ont pas besoin de recevoir ces emails)
            $membres = $membreRepository->createQueryBuilder('m')
                ->leftJoin('m.role', 'r')
                ->where('r.lib IN (:roles)')
                ->setParameter('roles', ['Utilisateur', 'Entraîneur'])
                ->getQuery()
                ->getResult();

            // On envoie l'email à chaque membre via le MailerService
            $mailerService->sendGroupEmail($membres, $sujet, $contenu);

            $this->addFlash('success', count($membres) . ' emails envoyés !');
            return $this->redirectToRoute('app_admin_email_groupe');
        }

        return $this->render('admin/email_groupe.html.twig');
    }

    /**
     * Affiche le détail d'un membre.
     * Route : GET /membre/{id}
     */
    #[Route('/{id}', name: 'app_membre_show', methods: ['GET'])]
    public function show(Membre $membre): Response
    {
        return $this->render('membre/show.html.twig', [
            'membre' => $membre,
        ]);
    }

    /**
     * Modifie un membre existant.
     * Route : GET /membre/{id}/edit (formulaire pré-rempli)
     *         POST /membre/{id}/edit (sauvegarde)
     */
    #[Route('/{id}/edit', name: 'app_membre_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Membre $membre, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $form = $this->createForm(MembreType::class, $membre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            // Si le champ mot de passe est laissé vide, on garde l'ancien
            if ($plainPassword) {
                $hashedPassword = $passwordHasher->hashPassword($membre, $plainPassword);
                $membre->setPassword($hashedPassword);
            }
            $entityManager->flush();
            return $this->redirectToRoute('app_membre_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('membre/edit.html.twig', [
            'membre' => $membre,
            'form' => $form,
        ]);
    }

    /**
     * Supprime un membre.
     * Route : POST /membre/{id}
     * Le token CSRF protège contre les suppressions frauduleuses.
     */
    #[Route('/{id}', name: 'app_membre_delete', methods: ['POST'])]
    public function delete(Request $request, Membre $membre, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $membre->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($membre);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_membre_index', [], Response::HTTP_SEE_OTHER);
    }
}