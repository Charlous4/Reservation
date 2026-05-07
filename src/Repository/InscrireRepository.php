<?php

namespace App\Repository;

use App\Entity\Inscrire;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class InscrireRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Inscrire::class);
    }

    // Nombre de réservations par activité
    public function countByActivite(): array
    {
        return $this->createQueryBuilder('i')
            ->select('a.nom as activite, COUNT(i.id) as total')
            ->join('i.session', 's')
            ->join('s.activite', 'a')
            ->groupBy('a.id')
            ->orderBy('total', 'DESC')
            ->getQuery()
            ->getResult();
    }

    // Nombre de réservations par mois
    public function countByMonth(): array
{
    $conn = $this->getEntityManager()->getConnection();
    $sql = "
        SELECT TO_CHAR(s.date_deb, 'YYYY-MM') as mois, COUNT(i.id) as total
        FROM inscrire i
        JOIN session s ON i.session_id = s.id
        GROUP BY mois
        ORDER BY mois ASC
    ";
    return $conn->executeQuery($sql)->fetchAllAssociative();
}

    // Taux d'occupation par session
    public function tauxOccupation(): array
    {
        return $this->createQueryBuilder('i')
            ->select('s.id, a.nom as activite, s.dateDeb, s.nbPlace, COUNT(i.id) as inscrits')
            ->join('i.session', 's')
            ->join('s.activite', 'a')
            ->groupBy('s.id, a.nom, s.dateDeb, s.nbPlace')
            ->orderBy('s.dateDeb', 'DESC')
            ->getQuery()
            ->getResult();
    }
}