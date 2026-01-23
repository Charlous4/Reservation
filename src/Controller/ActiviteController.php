<?php

namespace App\Controller;

use App\Entity\Activite;
use App\Form\ActiviteType;
use App\Repository\ActiviteRepository;
use App\Repository\TypeRepository; // 👈 Ajout important pour le filtre
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/activite')]
final class ActiviteController extends AbstractController
{
    #[Route(name: 'app_activite_index', methods: ['GET'])]
    public function index(ActiviteRepository $activiteRepository, TypeRepository $typeRepository, Request $request): Response
    {
        // 1. On récupère les choix de l'utilisateur dans l'URL
        $selectedType = $request->query->get('type');
        $selectedDifficulte = $request->query->get('difficulte');

        // 2. On construit le tableau de critères
        $criteria = [];
        
        if ($selectedType) {
            $criteria['type'] = $selectedType;
        }
        
        if ($selectedDifficulte) {
            $criteria['nvDifficulte'] = $selectedDifficulte;
        }

        // 3. On cherche en base de données selon les critères
        if ($criteria) {
            $activites = $activiteRepository->findBy($criteria);
        } else {
            $activites = $activiteRepository->findAll();
        }

        return $this->render('activite/index.html.twig', [
            'activites' => $activites,
            // On passe la liste complète des types pour le menu déroulant
            'types' => $typeRepository->findAll(),
            // On renvoie les choix actuels pour garder le filtre sélectionné visuellement
            'currentType' => $selectedType,
            'currentDifficulte' => $selectedDifficulte
        ]);
    }

    #[Route('/new', name: 'app_activite_new', methods: ['GET', 'POST'])]
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

        return $this->render('activite/new.html.twig', [
            'activite' => $activite,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_activite_show', methods: ['GET'])]
    public function show(Activite $activite): Response
    {
        return $this->render('activite/show.html.twig', [
            'activite' => $activite,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_activite_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Activite $activite, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ActiviteType::class, $activite);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_activite_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('activite/edit.html.twig', [
            'activite' => $activite,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_activite_delete', methods: ['POST'])]
    public function delete(Request $request, Activite $activite, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$activite->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($activite);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_activite_index', [], Response::HTTP_SEE_OTHER);
    }
}