<?php

namespace App\Controller;

use App\Entity\Type;
use App\Form\TypeType;
use App\Repository\TypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * TypeController — Gère les types d'activités.
 *
 * Un "type" = une catégorie d'activité sportive (ex: Aquatique, Cardio, Relaxation...).
 * Les activités sont ensuite filtrables par type dans ActiviteController.
 *
 * C'est un CRUD classique généré par Symfony Make,
 * identique dans sa structure à RolesController.
 */
#[Route('/type')]
final class TypeController extends AbstractController
{
    /**
     * Affiche la liste de tous les types d'activités.
     * Route : GET /type
     */
    #[Route(name: 'app_type_index', methods: ['GET'])]
    public function index(TypeRepository $typeRepository): Response
    {
        return $this->render('type/index.html.twig', [
            'types' => $typeRepository->findAll(),
        ]);
    }

    /**
     * Crée un nouveau type d'activité.
     * Route : GET /type/new (affiche le formulaire)
     *         POST /type/new (traite le formulaire)
     */
    #[Route('/new', name: 'app_type_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $type = new Type();
        $form = $this->createForm(TypeType::class, $type);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($type);
            $entityManager->flush();

            return $this->redirectToRoute('app_type_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('type/new.html.twig', [
            'type' => $type,
            'form' => $form,
        ]);
    }

    /**
     * Affiche le détail d'un type.
     * Route : GET /type/{id}
     */
    #[Route('/{id}', name: 'app_type_show', methods: ['GET'])]
    public function show(Type $type): Response
    {
        return $this->render('type/show.html.twig', [
            'type' => $type,
        ]);
    }

    /**
     * Modifie un type existant.
     * Route : GET /type/{id}/edit (formulaire pré-rempli)
     *         POST /type/{id}/edit (sauvegarde)
     */
    #[Route('/{id}/edit', name: 'app_type_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Type $type, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TypeType::class, $type);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_type_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('type/edit.html.twig', [
            'type' => $type,
            'form' => $form,
        ]);
    }

    /**
     * Supprime un type.
     * Route : POST /type/{id}
     *
     * ATTENTION : Si des activités sont liées à ce type,
     * leur champ "type" sera mis à null (relation nullable).
     */
    #[Route('/{id}', name: 'app_type_delete', methods: ['POST'])]
    public function delete(Request $request, Type $type, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $type->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($type);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_type_index', [], Response::HTTP_SEE_OTHER);
    }
}