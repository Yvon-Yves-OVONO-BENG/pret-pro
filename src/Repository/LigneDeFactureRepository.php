<?php

namespace App\Repository;

use App\Entity\EtatFacture;
use App\Entity\Facture;
use DateTime;
use App\Entity\LigneDeFacture;
use App\Entity\LigneDeEnsemble;
use App\Entity\Lot;
use App\Entity\ModePaiement;
use App\Entity\Produit;
use App\Entity\TypeProduit;
use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @extends ServiceEntityRepository<LigneDeFacture>
 *
 * @method LigneDeFacture|null find($id, $lockMode = null, $lockVersion = null)
 * @method LigneDeFacture|null findOneBy(array $criteria, array $orderBy = null)
 * @method LigneDeFacture[]    findAll()
 * @method LigneDeFacture[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LigneDeFactureRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        protected EntityManagerInterface $em)
    {
        parent::__construct($registry, LigneDeFacture::class);
    }

    /**
     * fnction qui retourne les facture vendues d'une période
     *
     * @param DateTime $dateDebut
     * @param DateTime $dateFin
     * @return array
     */
    public function facturesVenduesPeriode(DateTime $dateDebut, DateTime $dateFin): array
    {
        $qb = $this->createQueryBuilder('l')
            ->innerJoin(Facture::class, 'f')
            ->innerJoin(Produit::class, 'p')
            ->where('l.facture = f.id')
            ->andWhere('l.produit = p.id')
            ->andWhere('p.supprime = 0')
            ->andWhere('f.dateFactureAt BETWEEN :dateDebut AND :dateFin')
            ->setParameter('dateDebut', $dateDebut)
            ->setParameter('dateFin', $dateFin)
            ;

        return $qb->getQuery()->getResult();
    }

    /**
     * fonction qui retourne les ensembles vendus du jour
     *
     * @param DateTime $date
     * @return array
     */
    public function ensemblesVendusDuJour(DateTime $date): array
    {
        $qb = $this->createQueryBuilder('l')
            ->innerJoin(Facture::class, 'f')
            ->innerJoin(Produit::class, 'p')
            ->where('l.facture = f.id')
            ->andWhere('l.produit = p.id')
            ->andWhere('p.supprime = 0')
            ->andWhere('p.ensemble = 1')
            ->andWhere('f.dateFactureAt = :date')
            ->setParameter('date', date_format($date, 'Y-m-d'))
            ;

        return $qb->getQuery()->getResult();
    }


    /**
     * fonction qui retourne l'état d'u stock pendant une période donnée
     *
     * @param DateTime $dateDebut
     * @param DateTime $dateFin
     * @return void
     */
    public function etatStockPeriode(DateTime $dateDebut, DateTime $dateFin)
    {
        $queryBuilder = $this->em->createQueryBuilder();
        $queryBuilder
                ->select('
                            SUM(l.quantite) as quantiteProduit, 
                            p.libelle as produit, 
                            p.id as idProduit, 
                            lt.quantite,
                            lt.dateFabricationAt as dateFabrication,
                            lt.datePeremptionAt as datePeremption')
                ->from(LigneDeFacture::class, 'l')
                ->innerJoin(Facture::class, 'f')
                ->innerJoin(Produit::class, 'p')
                ->innerJoin(Lot::class, 'lt')
                ->andWhere('l.facture = f.id')
                ->andWhere('l.produit = p.id')
                ->andWhere('p.lot = lt.id')
                ->andWhere('f.dateFactureAt BETWEEN :dateDebut AND :dateFin')
                ->setParameter('dateDebut', $dateDebut)
                ->setParameter('dateFin', $dateFin)
                ->groupBy('produit')
                ;

        $query = $queryBuilder->getQuery();

        return $query->execute();
    }


    /**
     * fonction qui retourne les médicaments classiques vendus du jour
     *
     * @param DateTime $dateFacture
     * @return array
     */
    public function chercheLesMedicamentsVendusDujour(DateTime $dateFacture): array
    {
        $queryBuilder = $this->em->createQueryBuilder();
        $queryBuilder
                ->select('tp.typeProduit AS typeProduit, SUM(l.quantite) as quantiteFacture, m.modePaiement AS modePaiement, 
                SUM(f.avance) montant')
                ->from(LigneDeFacture::class, 'l')
                ->innerJoin(Facture::class, 'f')
                ->innerJoin(ModePaiement::class, 'm')
                ->innerJoin(Produit::class, 'p')
                ->innerJoin(Lot::class, 'lt')
                ->innerJoin(TypeProduit::class, 'tp')
                ->andWhere('l.facture = f.id')
                ->andWhere('f.modePaiement = m.id')
                ->andWhere('p.lot = lt.id')
                ->andWhere('p.ensemble = 0')
                ->andWhere('p.supprime = 0')
                ->andWhere('lt.typeProduit = tp.id')
                ->andWhere('l.produit = p.id')
                ->andWhere('f.annulee = 0')
                ->andWhere('f.dateFactureAt = :dateFacture')
                ->setParameter('dateFacture', date_format($dateFacture, 'Y-m-d'))
                ->groupBy('typeProduit')
                ->groupBy('modePaiement')
                ;

        $query = $queryBuilder->getQuery();

        return $query->execute();
    }

    /**
     * fonction qui retourne la recette du jour
     *
     * @param DateTime $aujourdhui
     * @return array
     */
    public function recetteDujour(DateTime $aujourdhui): array
    {
        $queryBuilder = $this->em->createQueryBuilder();
        $queryBuilder
                ->select('u.nom AS nom, tp.typeProduit AS typeProduit, 
                m.modePaiement AS modePaiement, etf.etatFacture AS etatFacture, 
                COUNT(tp.id) AS nombre, f.avance as montant, f.reference as reference, lt.prixVente as prixVente')
                ->from(LigneDeFacture::class, 'ldf')
                ->innerJoin(Produit::class, 'p')
                ->innerJoin(Lot::class, 'lt')
                ->innerJoin(TypeProduit::class, 'tp')
                ->innerJoin(Facture::class, 'f')
                ->innerJoin(ModePaiement::class, 'm')
                ->innerJoin(EtatFacture::class, 'etf')
                ->innerJoin(User::class, 'u')
                ->andWhere('p.id = ldf.produit')
                ->andWhere('lt.id = p.lot')
                ->andWhere('tp.id = lt.typeProduit')
                ->andWhere('f.id = ldf.facture')
                ->andWhere('m.id = f.modePaiement')
                ->andWhere('etf.id = f.etatFacture')
                ->andWhere('u.id = f.caissiere')
                ->andWhere('p.supprime = 0')
                ->andWhere('p.ensemble = 0')
                ->andWhere('f.annulee = 0')
                ->andWhere('f.dateFactureAt = :dateFacture')
                ->setParameter('dateFacture', date_format($aujourdhui, 'Y-m-d'))
                ->groupBy('nom','typeProduit', 'modePaiement', 'reference')
                ;

        $query = $queryBuilder->getQuery();

        return $query->execute();
    }

    /**
     * fonction qui retourne les recettes d'un jour particuler
     *
     * @param DateTime $dateDuJour
     * @return array
     */
    public function recetteDunjourParticulier(DateTime $dateDuJour): array
    {
        $queryBuilder = $this->em->createQueryBuilder();
        $queryBuilder
                ->select('u.nom AS nom, tp.typeProduit AS typeProduit, m.modePaiement AS modePaiement, etf.etatFacture AS etatFacture, COUNT(tp.id) AS nombre, SUM(ldf.prixQuantite) as montant')
                ->from(LigneDeFacture::class, 'ldf')
                ->innerJoin(Produit::class, 'p')
                ->innerJoin(Lot::class, 'lt')
                ->innerJoin(TypeProduit::class, 'tp')
                ->innerJoin(Facture::class, 'f')
                ->innerJoin(ModePaiement::class, 'm')
                ->innerJoin(EtatFacture::class, 'etf')
                ->innerJoin(User::class, 'u')
                ->andWhere('p.id = ldf.produit')
                ->andWhere('lt.id = p.lot')
                ->andWhere('tp.id = lt.typeProduit')
                ->andWhere('f.id = ldf.facture')
                ->andWhere('m.id = f.modePaiement')
                ->andWhere('etf.id = f.etatFacture')
                ->andWhere('u.id = f.caissiere')
                ->andWhere('p.supprime = 0')
                ->andWhere('p.ensemble = 0')
                ->andWhere('f.annulee = 0')
                ->andWhere('f.dateFactureAt = :dateFacture')
                ->setParameter('dateFacture', date_format($dateDuJour, 'Y-m-d'))
                ->groupBy('nom','typeProduit', 'modePaiement')
                ;

        $query = $queryBuilder->getQuery();

        return $query->execute();
    }


    /**
     * fonction qui retourne les recette d'une période donnéee
     *
     * @param DateTime $dateDebut
     * @param DateTime $dateFin
     * @return array
     */
    public function recetteDunePeriode(DateTime $dateDebut, DateTime $dateFin): array
    {
        $queryBuilder = $this->em->createQueryBuilder();
        $queryBuilder
                ->select('u.nom AS nom, tp.typeProduit AS typeProduit, m.modePaiement AS modePaiement, etf.etatFacture AS etatFacture, COUNT(tp.id) AS nombre, SUM(ldf.prixQuantite) as montant')
                ->from(LigneDeFacture::class, 'ldf')
                ->innerJoin(Produit::class, 'p')
                ->innerJoin(Lot::class, 'lt')
                ->innerJoin(TypeProduit::class, 'tp')
                ->innerJoin(Facture::class, 'f')
                ->innerJoin(ModePaiement::class, 'm')
                ->innerJoin(EtatFacture::class, 'etf')
                ->innerJoin(User::class, 'u')
                ->andWhere('p.id = ldf.produit')
                ->andWhere('lt.id = p.lot')
                ->andWhere('tp.id = lt.typeProduit')
                ->andWhere('f.id = ldf.facture')
                ->andWhere('m.id = f.modePaiement')
                ->andWhere('etf.id = f.etatFacture')
                ->andWhere('u.id = f.caissiere')
                ->andWhere('p.supprime = 0')
                ->andWhere('p.ensemble = 0')
                ->andWhere('f.annulee = 0')
                ->andWhere('f.dateFactureAt BETWEEN :dateDebut AND :dateFin')
                ->setParameter('dateDebut', date_format($dateDebut, 'Y-m-d'))
                ->setParameter('dateFin', date_format($dateFin, 'Y-m-d'))
                ->groupBy('nom','typeProduit', 'modePaiement')
                ;

        $query = $queryBuilder->getQuery();

        return $query->execute();
    }


    /**
     * fonction qui retourne les les ensembles vendus du jour
     *
     * @param DateTime $dateFacture
     * @return array
     */
    public function chercheLesEnsemblesVendusDujour(DateTime $dateFacture): array
    {
        $queryBuilder = $this->em->createQueryBuilder();
        $queryBuilder
                ->select('p.libelle AS ensemble, SUM(l.quantite) as quantiteFacture, m.modePaiement AS modePaiement, 
                SUM(l.prixQuantite) montant')
                ->from(LigneDeFacture::class, 'l')
                ->innerJoin(Facture::class, 'f')
                ->innerJoin(ModePaiement::class, 'm')
                ->innerJoin(Produit::class, 'p')
                // ->addSelect('p')
                ->andWhere('l.facture = f.id')
                ->andWhere('f.modePaiement = m.id')
                ->andWhere('p.ensemble = 1')
                ->andWhere('p.supprime = 0')
                ->andWhere('l.produit = p.id')
                ->andWhere('f.annulee = 0')
                ->andWhere('f.dateFactureAt = :dateFacture')
                ->setParameter('dateFacture', date_format($dateFacture, 'Y-m-d'))
                ->groupBy('modePaiement')
                ;

        $query = $queryBuilder->getQuery();

        return $query->execute();
    }
    //    /**
    //     * @return LigneDeFacture[] Returns an array of LigneDeFacture objects
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

    //    public function findOneBySomeField($value): ?LigneDeFacture
    //    {
    //        return $this->createQueryBuilder('l')
    //            ->andWhere('l.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
