<?php

namespace App\Repository;

use App\Entity\LigneDeEnsemble;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<LigneDeEnsemble>
 *
 * @method LigneDeEnsemble|null find($id, $lockMode = null, $lockVersion = null)
 * @method LigneDeEnsemble|null findOneBy(array $criteria, array $orderBy = null)
 * @method LigneDeEnsemble[]    findAll()
 * @method LigneDeEnsemble[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LigneDeEnsembleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LigneDeEnsemble::class);
    }

    //    /**
    //     * @return LigneDeEnsemble[] Returns an array of LigneDeEnsemble objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('l')
    //            ->andWhere('l.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('l.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?LigneDeEnsemble
    //    {
    //        return $this->createQueryBuilder('l')
    //            ->andWhere('l.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
