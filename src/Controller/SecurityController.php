<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * SecurityController — Gère l'affichage du formulaire de connexion et la déconnexion.
 *
 * IMPORTANT : Ce controller n'effectue PAS réellement la connexion.
 * La vraie logique d'authentification (vérification login/mot de passe)
 * est gérée par LoginFormAuthenticator.php, déclaré dans security.yaml.
 *
 * Ce controller sert uniquement à :
 *   - Afficher la page de connexion (GET /login)
 *   - Déclarer la route de déconnexion (GET /logout)
 */
class SecurityController extends AbstractController
{
    /**
     * Affiche le formulaire de connexion.
     * Route : GET /login
     *
     * AuthenticationUtils est injecté automatiquement par Symfony.
     * Il permet de récupérer les informations sur la tentative de connexion précédente.
     */
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Récupère l'erreur de connexion si elle existe
        // (ex: "Identifiants invalides" après un mauvais mot de passe)
        $error = $authenticationUtils->getLastAuthenticationError();

        // Récupère le dernier login saisi pour le réafficher dans le champ
        // (évite à l'utilisateur de retaper son login en cas d'erreur)
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error'         => $error,
        ]);
    }

    /**
     * Route de déconnexion.
     * Route : GET /logout
     *
     * Cette méthode ne sera JAMAIS exécutée.
     * Symfony intercepte la requête vers /logout avant d'arriver ici
     * (géré par le firewall dans security.yaml).
     * La méthode doit juste exister pour que la route soit déclarée.
     */
    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('Cette méthode est interceptée par le firewall Symfony — elle ne s\'exécute jamais.');
    }
}