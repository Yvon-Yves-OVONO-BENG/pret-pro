<?php

namespace App\Controller\Ensemble;

use App\Repository\ProduitRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @IsGranted("ROLE_USER", message="Accès refusé. Espace reservé uniquement aux abonnés")
 *
 */
#[Route('/ensemble')]
class DetailsEnsembleController extends AbstractController
{
    public function __construct(
        protected ProduitRepository $produitRepository,
    )
    {}

    #[Route('/details-ensemble/{slug}', name: 'details_ensemble')]
    public function detailsEnsemble(Request $request, string $slug): Response
    {
        # je récupère ma session
        $maSession = $request->getSession();
        
        #mes variables témoin pour afficher les sweetAlert
        $maSession->set('ajout', null);
        $maSession->set('suppression', null);
        
        

        #je récupère la ensemble dont je veux modifier
        $ensemble = $this->produitRepository->findOneBySlug([
            'slug' => $slug
        ]);
        
        
        return $this->render('ensemble/detailsEnsemble.html.twig', [
            'licence' => 1,
            'slug' => $slug,
            'ensemble' => $ensemble,
        ]);
    }
}
