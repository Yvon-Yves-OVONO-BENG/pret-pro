<?php

namespace App\Repository;

use DateTime;
use App\Entity\User;
use App\Entity\Facture;
use App\Entity\EtatFacture;
use App\Entity\HistoriquePaiement;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<HistoriquePaiement>
 *
 * @method HistoriquePaiement|null find($id, $lockMode = null, $lockVersion = null)
 * @method HistoriquePaiement|null findOneBy(array $criteria, array $orderBy = null)
 * @method HistoriquePaiement[]    findAll()
 * @method HistoriquePaiement[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HistoriquePaiementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HistoriquePaiement::class);
    }

    public function avancesDuJour(DateTime $dateJour): array
    {
        $qb = $this->createQueryBuilder('h')
            ->andWhere('h.dateAvanceAt =:dateJour')
            ->setParameter('dateJour', $dateJour)
            ;
            
        return $qb->getQuery()->getResult();
    }

    public function historiquePaiementPeriode(User $caissiere = null, EtatFacture $etatFacture = null, DateTime $dateDebut = null, DateTime $dateFin = null): array
    {
        $qb = $this->createQueryBuilder('h')
            ;
            if($caissiere)
            {
                $qb->innerjoin(Facture::class, 'f')
                ->andWhere('f.caissiere = :caissiere')
                ->setParameter('caissiere', $caissiere);
            }
            if($dateDebut && $dateFin)
            {
                $qb->andWhere('h.dateAvanceAt BETWEEN :dateDebut AND :dateFin')
                ->setParameter('dateDebut', $dateDebut)
                ->setParameter('dateFin', $dateFin);
            }

            if($etatFacture)
            {
                $qb->andWhere('f.etatFacture = :etatFacture')
                ->setParameter('etatFacture', $etatFacture);
            }

        return $qb->getQuery()->getResult();
    }


    //    /**
    //     * @return HistoriquePaiement[] Returns an array of HistoriquePaiement objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('h')
    //            ->andWhere('h.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('h.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?HistoriquePaiement
    //    {
    //        return $this->createQueryBuilder('h')
    //            ->andWhere('h.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
