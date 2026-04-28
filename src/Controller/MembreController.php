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

#[Route('/membre')]
final class MembreController extends AbstractController
{
    #[Route(name: 'app_membre_index', methods: ['GET'])]
    public function index(MembreRepository $membreRepository): Response
    {
        return $this->render('membre/index.html.twig', [
            'membres' => $membreRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_membre_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $membre = new Membre();
        $form = $this->createForm(MembreType::class, $membre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // 👇 AJOUT : On crypte le mot de passe à la création
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

    #[Route('/{id}', name: 'app_membre_show', methods: ['GET'])]
    public function show(Membre $membre): Response
    {
        return $this->render('membre/show.html.twig', [
            'membre' => $membre,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_membre_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Membre $membre, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $form = $this->createForm(MembreType::class, $membre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // 👇 AJOUT : On vérifie si un nouveau mot de passe a été saisi
            $plainPassword = $form->get('plainPassword')->getData();

            // Si oui, on le hache et on le met à jour
            if ($plainPassword) {
                $hashedPassword = $passwordHasher->hashPassword($membre, $plainPassword);
                $membre->setPassword($hashedPassword);
            }
            // Si non, on ne touche pas au mot de passe actuel

            $entityManager->flush();

            return $this->redirectToRoute('app_membre_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('membre/edit.html.twig', [
            'membre' => $membre,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_membre_delete', methods: ['POST'])]
    public function delete(Request $request, Membre $membre, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$membre->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($membre);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_membre_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/admin/email-groupe', name: 'app_admin_email_groupe', methods: ['GET', 'POST'])]
#[IsGranted('ROLE_ADMIN')]
public function emailGroupe(Request $request, MembreRepository $membreRepository, MailerService $mailerService): Response
{
    if ($request->isMethod('POST')) {
    $sujet = $request->request->get('sujet');
    $contenu = $request->request->get('contenu');

    // 👇 Remplace uniquement ces lignes
    $membres = $membreRepository->createQueryBuilder('m')
        ->leftJoin('m.role', 'r')
        ->where('r.lib IN (:roles)')
        ->setParameter('roles', ['Utilisateur', 'Entraîneur'])
        ->getQuery()
        ->getResult();

    $mailerService->sendGroupEmail($membres, $sujet, $contenu);

    $this->addFlash('success', count($membres) . ' emails envoyés !');
    return $this->redirectToRoute('app_admin_email_groupe');
}

    return $this->render('admin/email_groupe.html.twig');
}
}