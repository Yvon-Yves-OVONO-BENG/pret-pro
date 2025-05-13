<?php

namespace App\Controller\Ensemble;

use App\Repository\ProduitRepository;
use App\Repository\LigneDeEnsembleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @IsGranted("ROLE_USER", message="Accès refusé. Espace reservé uniquement aux abonnés")
 *
 */
#[Route('/ensemble')]
class AfficherEnsembleController extends AbstractController
{
    public function __construct(
        protected EntityManagerInterface $em,
        protected TranslatorInterface $translator,
        protected ProduitRepository $produitRepository,
        protected LigneDeEnsembleRepository $ligneDeEnsemblesRepository,
    )
    {}
    
    #[Route('/afficher-ensemble/{slug}/{s}', name: 'afficher_ensemble')]
    public function affichererEnsemble(Request $request, string $slug, int $s = 0): Response
    {
        # je récupère ma session
        $maSession = $request->getSession();

        #je teste si le témoin n'est pas vide pour savoir s'il vient de la suppression
        if ($s == 1) 
        {
            # je récupère ma session
            $maSession = $request->getSession();
            
            #mes variables témoin pour afficher les sweetAlert
            $maSession->set('ajout', null);
            $maSession->set('suppression', 1);
            
        }
        

        #je récupère la ensemble dont je veux modifier
        $ensemble = $this->produitRepository->findOneBySlug([
            'slug' => $slug
        ]);

        #je récupère tous les ensembles
        $ensembles = $this->produitRepository->findBy([
            'ensemble' => 1,
            'supprime' => 0,
        ]);

        #je calcul le prix du ensemble
        $prix = 0;
        $ligneDeEnsembles = $ensemble->getProduitLigneDeEnsembles();

        foreach ($ligneDeEnsembles as $ligneDeEnsemble) 
        {
            $prix += ($ligneDeEnsemble->getProduit()->getPrixVente() * $ligneDeEnsemble->getQuantite());

        }



        return $this->render('ensemble/modifierEnsemble.html.twig', [
            'slug' => $slug,
            'ensemble' => $ensemble,
            'ensembles' => $ensembles,
            'licence' => 1,
        ]);
    }
}
