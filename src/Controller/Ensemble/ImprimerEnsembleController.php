<?php

namespace App\Controller\Ensemble;

use App\Repository\ProduitRepository;
use App\Service\ImpressionDesEnsemblesService;
use App\Service\ImpressionEnsembleService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @IsGranted("ROLE_USER", message="Accès refusé. Espace reservé uniquement aux abonnés")
 *
 */
#[Route('/ensemble')]
class ImprimerEnsembleController extends AbstractController
{
    public function __construct(
        protected ImpressionEnsembleService $impressionEnsembleService,
        protected ImpressionDesEnsemblesService $impressionDesEnsemblesService,
        protected ProduitRepository $produitRepository)
    {}

    #[Route('/imprimer-ensemble/{slug}', name: 'imprimer_ensemble')]
    public function imprimerEnsemble(string $slug = null): Response
    {
        if ($slug != null) 
        {
            $ensemble = $this->produitRepository->findOneBySlug([
                'slug' => $slug
                ]);
            
            $pdf = $this->impressionEnsembleService->impressionEnsemble($ensemble);
            return new Response($pdf->Output(utf8_decode($ensemble->getLibelle()), "I"), 200, ['content-type' => 'application/pdf']);

        } 
        else 
        {
            $ensembles = $this->produitRepository->findBy([
                'ensemble' => 1
                ]);
            
            $pdf = $this->impressionDesEnsemblesService->impressionDesEnsembles($ensembles);
            return new Response($pdf->Output(utf8_decode("Liste des ensembles "), "I"), 200, ['content-type' => 'application/pdf']);

        }
        
        

        
    }
}
