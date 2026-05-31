<?php

namespace App\Controller;

use App\Entity\Membre;
use App\Form\RegistrationFormType;
use App\Security\LoginFormAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

/**
 * RegistrationController — Gère l'inscription des nouveaux utilisateurs.
 *
 * Accessible à tous (même les non-connectés) car c'est la page d'inscription.
 *
 * Sécurité importante :
 *   Le rôle "Administrateur" est exclu du formulaire d'inscription
 *   (filtré directement dans RegistrationFormType via query_builder).
 *   Ainsi, un nouvel utilisateur ne peut s'inscrire qu'en tant que
 *   Membre ou Entraîneur. Seul un admin existant peut créer un autre admin.
 */
class RegistrationController extends AbstractController
{
    /**
     * Affiche et traite le formulaire d'inscription.
     * Route : GET /register (affiche le formulaire)
     *         POST /register (traite l'inscription)
     */
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        Security $security,
        EntityManagerInterface $entityManager
    ): Response {
        // On crée un objet Membre vide qui sera rempli par le formulaire
        $user = new Membre();

        // On génère le formulaire lié à cet objet Membre
        $form = $this->createForm(RegistrationFormType::class, $user);

        // On analyse la requête HTTP : si POST, le formulaire a été soumis
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Le champ plainPassword est "mapped: false" dans RegistrationFormType
            // → il n'est pas automatiquement lié à l'entité, on le récupère manuellement
            $plainPassword = $form->get('plainPassword')->getData();

            // On hache le mot de passe avant de le sauvegarder
            // JAMAIS de mot de passe en clair en base de données !
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            // On sauvegarde le nouveau membre en base de données
            $entityManager->persist($user);
            $entityManager->flush();

            // Après inscription réussie, on connecte automatiquement l'utilisateur
            // sans qu'il ait à ressaisir ses identifiants
            return $security->login($user, LoginFormAuthenticator::class, 'main');
        }

        // Si formulaire pas encore soumis (GET) ou invalide : on affiche la page
        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }
}