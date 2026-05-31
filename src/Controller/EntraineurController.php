<?php
// ============================================================
// EntraineurController.php
// ============================================================
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

/**
 * EntraineurController — Espace dédié aux entraîneurs.
 *
 * Tout ce controller est protégé par #[IsGranted('ROLE_ENTRAINEUR')] :
 * seuls les entraîneurs connectés peuvent accéder à ces pages.
 *
 * Un entraîneur peut :
 *   - Voir son planning (ses sessions assignées)
 *   - Modifier une de ses sessions
 *   - Annuler une de ses sessions
 *
 * Sécurité supplémentaire : on vérifie que la session appartient
 * bien à CET entraîneur (pas un autre).
 */
#[Route('/entraineur')]
#[IsGranted('ROLE_ENTRAINEUR')]
class EntraineurController extends AbstractController
{
    /**
     * Affiche le planning de l'entraîneur connecté.
     * Route : GET /entraineur/planning
     */
    #[Route('/planning', name: 'app_entraineur_planning', methods: ['GET'])]
    public function planning(SessionRepository $sessionRepository): Response
    {
        // On récupère l'entraîneur connecté
        $entraineur = $this->getUser();

        // On cherche uniquement les sessions où il est assigné comme entraîneur
        // (méthode personnalisée dans SessionRepository)
        $sessions = $sessionRepository->findByEntraineur($entraineur);

        return $this->render('entraineur/planning.html.twig', [
            'sessions' => $sessions,
        ]);
    }

    /**
     * Modifie une session de l'entraîneur.
     * Route : GET/POST /entraineur/session/{id}/edit
     */
    #[Route('/session/{id}/edit', name: 'app_entraineur_session_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Session $session, EntityManagerInterface $entityManager): Response
    {
        // Sécurité : on vérifie que la session appartient bien à l'entraîneur connecté
        // Un entraîneur ne doit pas pouvoir modifier les sessions d'un autre !
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

    /**
     * Annule (supprime) une session de l'entraîneur.
     * Route : POST /entraineur/session/{id}/annuler
     *
     * Grâce au cascade: ['remove'] sur Session→Inscrire,
     * toutes les inscriptions liées sont automatiquement supprimées.
     */
    #[Route('/session/{id}/annuler', name: 'app_entraineur_session_annuler', methods: ['POST'])]
    public function annuler(Request $request, Session $session, EntityManagerInterface $entityManager): Response
    {
        // Même vérification de propriété que pour edit()
        if ($session->getEntraineur() !== $this->getUser()) {
            $this->addFlash('danger', 'Vous ne pouvez pas annuler cette session.');
            return $this->redirectToRoute('app_entraineur_planning');
        }

        if ($this->isCsrfTokenValid('annuler' . $session->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($session);
            $entityManager->flush();
            $this->addFlash('success', 'Session annulée avec succès.');
        }

        return $this->redirectToRoute('app_entraineur_planning');
    }
}