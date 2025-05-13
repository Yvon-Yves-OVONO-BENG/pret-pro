<?php

namespace App\Controller\Facture;

use App\Entity\ConstantsClass;
use App\Repository\EtatFactureRepository;
use App\Repository\FactureRepository;
use App\Repository\ModePaiementRepository;
use App\Service\ImpressionDesFactureService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @IsGranted("ROLE_USER", message="Accès refusé. Espace reservé uniquement aux abonnés")
 *
 */
#[Route('/facture')]
class ImprimerLesFacturesPatientController extends AbstractController
{
    public function __construct(
        protected FactureRepository $factureRepository,
        protected EtatFactureRepository $etatFactureRepository,
        protected ModePaiementRepository $modePaiementRepository,
        protected ImpressionDesFactureService $impressionDesFactureService, 
        )
    {}
    
    #[Route('/imprimer-les-factures-client/{codePatient}', name: 'imprimer_les_factures_client')]
    public function imprimerFacture($codePatient): Response
    {
        #je récupère le client dont je veux imprimer les prise en charges
        $client = $this->factureRepository->findOneBy([
            'code' => $codePatient
        ]);
        
        $etatFacture = $this->etatFactureRepository->findOneByEtatFacture([
            'etatFacture' => ConstantsClass::NON_SOLDE
        ]);


        $factures = $this->factureRepository->findBy([
            'client' => $client,
            'etatFacture' => $etatFacture,
            'annulee' => 0
            ]);

        $pdf = $this->impressionDesFactureService->impressionDesFactures($factures);
    
        return new Response($pdf->Output(utf8_decode("Les factures de ".$client->getNomClient()), "I"), 200, ['content-type' => 'application/pdf']);

    }
}
