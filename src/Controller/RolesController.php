<?php

namespace App\Controller;

use App\Entity\Roles;
use App\Form\RolesType;
use App\Repository\RolesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * RolesController — Gère les rôles disponibles dans l'application.
 *
 * Les rôles sont stockés en base de données dans la table "roles"
 * (ex: Administrateur, Entraîneur, Utilisateur).
 *
 * Ce controller est un CRUD classique généré par Symfony Make.
 * Il permet à l'admin de créer, lire, modifier et supprimer des rôles.
 *
 * ATTENTION : Supprimer un rôle utilisé par des membres peut causer des problèmes.
 * Dans un projet en production, il faudrait ajouter une vérification.
 */
#[Route('/roles')]
final class RolesController extends AbstractController
{
    /**
     * Affiche la liste de tous les rôles.
     * Route : GET /roles
     */
    #[Route(name: 'app_roles_index', methods: ['GET'])]
    public function index(RolesRepository $rolesRepository): Response
    {
        return $this->render('roles/index.html.twig', [
            'roles' => $rolesRepository->findAll(),
        ]);
    }

    /**
     * Crée un nouveau rôle.
     * Route : GET /roles/new (affiche le formulaire)
     *         POST /roles/new (traite le formulaire)
     */
    #[Route('/new', name: 'app_roles_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $role = new Roles();
        $form = $this->createForm(RolesType::class, $role);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // On prépare l'insertion en base
            $entityManager->persist($role);
            // On exécute le INSERT en base
            $entityManager->flush();

            return $this->redirectToRoute('app_roles_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('roles/new.html.twig', [
            'role' => $role,
            'form' => $form,
        ]);
    }

    /**
     * Affiche le détail d'un rôle.
     * Route : GET /roles/{id}
     * Symfony récupère automatiquement l'objet Roles correspondant à l'ID.
     */
    #[Route('/{id}', name: 'app_roles_show', methods: ['GET'])]
    public function show(Roles $role): Response
    {
        return $this->render('roles/show.html.twig', [
            'role' => $role,
        ]);
    }

    /**
     * Modifie un rôle existant.
     * Route : GET /roles/{id}/edit (formulaire pré-rempli)
     *         POST /roles/{id}/edit (sauvegarde)
     */
    #[Route('/{id}/edit', name: 'app_roles_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Roles $role, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(RolesType::class, $role);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Pas besoin de persist() : Doctrine suit déjà cet objet (il vient de la BDD)
            $entityManager->flush();

            return $this->redirectToRoute('app_roles_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('roles/edit.html.twig', [
            'role' => $role,
            'form' => $form,
        ]);
    }

    /**
     * Supprime un rôle.
     * Route : POST /roles/{id}
     *
     * Le token CSRF protège contre les suppressions frauduleuses
     * (quelqu'un qui enverrait un formulaire depuis un autre site).
     */
    #[Route('/{id}', name: 'app_roles_delete', methods: ['POST'])]
    public function delete(Request $request, Roles $role, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $role->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($role);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_roles_index', [], Response::HTTP_SEE_OTHER);
    }
}