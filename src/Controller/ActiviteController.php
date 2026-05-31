<?php
// ============================================================
// ActiviteController.php
// ============================================================
namespace App\Controller;

use App\Entity\Activite;
use App\Form\ActiviteType;
use App\Repository\ActiviteRepository;
use App\Repository\TypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * ActiviteController — Gère les activités sportives proposées par le centre.
 *
 * Une "activité" = Tennis, Yoga, Natation...
 * Elle a un nom, une description, une capacité max, un prix et un niveau de difficulté.
 *
 * Ce controller gère :
 *   - L'affichage de la liste avec filtres (type et difficulté)
 *   - Le CRUD complet (réservé aux admins pour création/modification/suppression)
 */
#[Route('/activite')]
final class ActiviteController extends AbstractController
{
    /**
     * Affiche la liste des activités avec filtres optionnels.
     * Route : GET /activite
     *
     * Les filtres passent par l'URL (paramètres GET) :
     * ex: /activite?type=2&difficulte=1
     */
    #[Route(name: 'app_activite_index', methods: ['GET'])]
    public function index(ActiviteRepository $activiteRepository, TypeRepository $typeRepository, Request $request): Response
    {
        // On récupère les valeurs des filtres depuis l'URL (null si absents)
        $selectedType = $request->query->get('type');
        $selectedDifficulte = $request->query->get('difficulte');

        // On construit dynamiquement les critères de recherche
        $criteria = [];
        if ($selectedType) {
            $criteria['type'] = $selectedType;
        }
        if ($selectedDifficulte) {
            $criteria['nvDifficulte'] = $selectedDifficulte;
        }

        // findBy() permet de filtrer selon des critères exacts
        // Si aucun critère, on récupère tout avec findAll()
        $activites = $criteria ? $activiteRepository->findBy($criteria) : $activiteRepository->findAll();

        return $this->render('activite/index.html.twig', [
            'activites' => $activites,
            'types' => $typeRepository->findAll(),       // Pour le menu déroulant des types
            'currentType' => $selectedType,              // Pour garder le filtre sélectionné visuellement
            'currentDifficulte' => $selectedDifficulte,
        ]);
    }

    /**
     * Crée une nouvelle activité.
     * Accessible uniquement aux administrateurs.
     * Route : GET/POST /activite/new
     */
    #[Route('/new', name: 'app_activite_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $activite = new Activite();
        $form = $this->createForm(ActiviteType::class, $activite);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($activite);
            $entityManager->flush();
            return $this->redirectToRoute('app_activite_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('activite/new.html.twig', ['activite' => $activite, 'form' => $form]);
    }

    /** Affiche le détail d'une activité. Route : GET /activite/{id} */
    #[Route('/{id}', name: 'app_activite_show', methods: ['GET'])]
    public function show(Activite $activite): Response
    {
        return $this->render('activite/show.html.twig', ['activite' => $activite]);
    }

    /** Modifie une activité. Route : GET/POST /activite/{id}/edit */
    #[Route('/{id}/edit', name: 'app_activite_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Activite $activite, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ActiviteType::class, $activite);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('app_activite_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('activite/edit.html.twig', ['activite' => $activite, 'form' => $form]);
    }

    /** Supprime une activité. Route : POST /activite/{id} */
    #[Route('/{id}', name: 'app_activite_delete', methods: ['POST'])]
    public function delete(Request $request, Activite $activite, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $activite->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($activite);
            $entityManager->flush();
        }
        return $this->redirectToRoute('app_activite_index', [], Response::HTTP_SEE_OTHER);
    }
}