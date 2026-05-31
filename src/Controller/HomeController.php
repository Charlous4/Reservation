<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * HomeController — Gère la page d'accueil de l'application.
 *
 * C'est le controller le plus simple du projet.
 * Il n'a qu'une seule méthode qui affiche la page d'accueil
 * avec les tuiles de navigation (Membres, Activités, Sessions...).
 *
 * Aucune donnée n'est passée au template car la page d'accueil
 * affiche uniquement des liens de navigation.
 */
class HomeController extends AbstractController
{
    /**
     * Affiche la page d'accueil.
     * Route : GET /
     *
     * C'est la première page que voit l'utilisateur après connexion.
     */
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        // Aucune donnée à récupérer en base : on affiche juste le template
        return $this->render('home/index.html.twig');
    }
}