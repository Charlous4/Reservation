<?php

namespace App\Controller;

use App\Repository\InscrireRepository;
use App\Repository\SessionRepository;
use App\Repository\MembreRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * StatController — Tableau de bord statistique réservé aux administrateurs.
 *
 * Toutes les routes de ce controller sont automatiquement protégées
 * par #[IsGranted('ROLE_ADMIN')] placé sur la classe entière.
 * Un non-admin qui tente d'accéder sera redirigé vers la page de connexion.
 *
 * Les données viennent de requêtes personnalisées dans InscrireRepository.
 * Elles sont ensuite passées au template Twig qui les affiche avec Chart.js.
 */
#[Route('/admin/stats')]
#[IsGranted('ROLE_ADMIN')]
class StatController extends AbstractController
{
    /**
     * Affiche le tableau de bord avec tous les graphiques et chiffres clés.
     * Route : GET /admin/stats
     *
     * Données envoyées au template Twig :
     *   - parActivite    : nombre de réservations par activité → graphique barres
     *   - parMois        : nombre de réservations par mois → graphique courbe
     *   - tauxOccupation : inscrits / places par session → tableau avec barres de progression
     *   - totalMembres   : chiffre clé affiché en haut de page
     *   - totalSessions  : chiffre clé affiché en haut de page
     *   - totalInscrits  : chiffre clé affiché en haut de page
     */
    #[Route('', name: 'app_stats', methods: ['GET'])]
    public function index(
        InscrireRepository $inscrireRepository,
        SessionRepository $sessionRepository,
        MembreRepository $membreRepository
    ): Response {
        return $this->render('admin/stats.html.twig', [
            // Requêtes personnalisées définies dans InscrireRepository
            'parActivite'    => $inscrireRepository->countByActivite(),
            'parMois'        => $inscrireRepository->countByMonth(),
            'tauxOccupation' => $inscrireRepository->tauxOccupation(),

            // count([]) = SELECT COUNT(*) sans aucun filtre
            'totalMembres'  => $membreRepository->count([]),
            'totalSessions' => $sessionRepository->count([]),
            'totalInscrits' => $inscrireRepository->count([]),
        ]);
    }
}