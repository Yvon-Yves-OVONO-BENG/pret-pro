<?php

namespace App\Repository;

use App\Entity\Lot;
use App\Entity\Produit;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Produit>
 *
 * @method Produit|null find($id, $lockMode = null, $lockVersion = null)
 * @method Produit|null findOneBy(array $criteria, array $orderBy = null)
 * @method Produit[]    findAll()
 * @method Produit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProduitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Produit::class);
    }

    /**
     * function qui affiche les produit dont la date de peremption est definie
     *
     * @return array
     */
    public function produits(): array
    {
        $qb = $this->createQueryBuilder('p')
            ->innerJoin(Lot::class, 'l')
            ->andWhere('p.lot = l.id')
            ->andWhere('p.ensemble = 0')
            ->andWhere('p.supprime = 0')
            ;

        return $qb->getQuery()->getResult();
    }

    
    public function produitsSeuil(): array
    {
        $qb = $this->createQueryBuilder('p')
            ->innerJoin(Lot::class, 'l')
            ->andWhere('p.lot = l.id')
            ->andWhere('(l.quantite - l.vendu) = p.quantiteSeuil')
            ->andWhere('p.ensemble = 0')
            ->andWhere('p.supprime = 0')
            ->orderBy('p.libelle', 'ASC')
            ;

        return $qb->getQuery()->getResult();
    }

    //    /**
    //     * @return Produit[] Returns an array of Produit objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Produit
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
