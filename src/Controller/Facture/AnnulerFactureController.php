<?php

namespace App\Controller\Facture;

use App\Entity\ConstantsClass;
use App\Repository\FactureRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\EtatFactureRepository;
use App\Repository\ProduitRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

/**
 * @IsGranted("ROLE_USER", message="Accès refusé. Espace reservé uniquement aux abonnés")
 *
 */
#[Route('/facture')]
class AnnulerFactureController extends AbstractController
{
    public function __construct(
        protected FactureRepository $factureRepository, 
        protected ProduitRepository $produitRepository,
        protected EtatFactureRepository $etatFactureRepository, 
        protected EntityManagerInterface $em)
    {}

    #[Route('/annuler-facture/{slug}', name: 'annuler_facture')]
    public function annulerFacture(Request $request, $slug): Response
    {
        $maSession = $request->getSession();
        $facture = $this->factureRepository->findOneBySlug([
            'slug' => $slug
        ]);
        
        $facture->setAnnulee(1);

        #je récupère les lignes de facture de la facture à annuler
        $ligneDeFactures = $facture->getLigneDeFactures();

        #je parcours mon tableau de produit pour aller restituer leurs quantités en stock
        foreach ($ligneDeFactures as $ligneDeFacture) 
        {
            #je récupère sa quantité dans la facture
            $quantiteFacture = $ligneDeFacture->getQuantite();

            if (!$ligneDeFacture->getProduit()->isEnsemble()) 
            {
                #je récupère le lot
                $lot = $ligneDeFacture->getProduit()->getLot();

                #je récupère la quantite dans le lot du produit
                $quantiteLot = $ligneDeFacture->getProduit()->getLot()->getQuantite();
                
                #je récupère la quantité déjà vendu dand le lot
                $quantiteVenduLot = $ligneDeFacture->getProduit()->getLot()->getVendu();

                #je restitue la quentité du produit en stock
                $lot->setQuantite($quantiteLot + $quantiteFacture)
                    ->setVendu($quantiteVenduLot - $quantiteFacture) ;

                $this->em->persist($lot);
            }
            
        }
        
        $this->em->persist($facture);
        $this->em->flush();

        $this->addFlash('info', 'Facture annulée avec succès !');

        #j'affecte 1 à ma variable pour afficher le message
        $maSession->set('ajout', 1);
            
        

        return $this->redirectToRoute('liste_facture', ['m' => 1 ]);
    }
}
