<?php

namespace App\Controller;

use App\Repository\InscrireRepository;
use App\Repository\SessionRepository;
use App\Repository\MembreRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/stats')]
#[IsGranted('ROLE_ADMIN')]
class StatController extends AbstractController
{
    #[Route('', name: 'app_stats', methods: ['GET'])]
    public function index(InscrireRepository $inscrireRepository, SessionRepository $sessionRepository, MembreRepository $membreRepository): Response
    {
        return $this->render('admin/stats.html.twig', [
            'parActivite'   => $inscrireRepository->countByActivite(),
            'parMois'       => $inscrireRepository->countByMonth(),
            'tauxOccupation'=> $inscrireRepository->tauxOccupation(),
            'totalMembres'  => $membreRepository->count([]),
            'totalSessions' => $sessionRepository->count([]),
            'totalInscrits' => $inscrireRepository->count([]),
        ]);
    }
}