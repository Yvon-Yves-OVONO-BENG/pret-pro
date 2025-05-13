<?php

namespace App\Controller\Impression;

use DateTime;
use App\Entity\ConstantsClass;
use App\Repository\FactureRepository;
use App\Repository\HistoriquePaiementRepository;
use App\Repository\UserRepository;
use App\Service\ImpressionFicheDeVenteService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

/**
 * @IsGranted("ROLE_USER", message="Accès refusé. Espace reservé uniquement aux abonnés")
 */
#[Route('impression/')]
class ImpressionFicheDeVenteController extends AbstractController
{
    public function __construct(
        protected UserRepository $userRepository,
        protected FactureRepository $factureRepository,
        protected HistoriquePaiementRepository $historiquePaiementRepository,
        protected ImpressionFicheDeVenteService $impressionFicheDeVenteService
    )
    {}

    #[Route('/impression-fiche-de-vente/{id}/{recette}/{recettePeriode}/{dateDebut}/{dateFin}/{periode}', name: 'impression_fiche_de_vente')]
    public function impressionFicheDeVente(Request $request, int $id = 0, int $recette = 0, int $recettePeriode = 0, $dateDebut = 0, $dateFin = 0, $periode = 0): Response
    {
        # je récupère ma session
        $maSession = $request->getSession();
        
        #mes variables témoin pour afficher les sweetAlert
        $maSession->set('ajout', null);
        $maSession->set('suppression', null);

        /**
         *@var User
         */
        $user = $this->getUser();

        if (in_array(ConstantsClass::ROLE_CAISSIERE, $user->getRoles())) 
        {
            $caissiere = $this->userRepository->find($user->getId());
        } 
        else 
        {
            $caissiere = null;
        }
        
        if ($id !=0) 
        {
            $caissiere = $this->userRepository->find($id);

            #date du jour
            $aujourdhui = date_create(date_format(new DateTime('now'), 'Y-m-d'), timezone_open('Pacific/Nauru'));

            $historiquesPaiement = $this->historiquePaiementRepository->findBy([
                'dateAvanceAt' => $aujourdhui, 'recuPar' => $caissiere]);
            
            $pdf = $this->impressionFicheDeVenteService->impressionFiheDeVente($historiquesPaiement,$caissiere);
            

        } 
        elseif($recette != 0)
        {
            $caissiere = $this->userRepository->find($recette);

            #date du jour
            $aujourdhui = date_create(date_format(new DateTime('now'), 'Y-m-d'), timezone_open('Pacific/Nauru'));

            $historiquesPaiement = $this->historiquePaiementRepository->findBy([
                'dateAvanceAt' => $aujourdhui, 'recuPar' => $caissiere]);
            
           
            $pdf = $this->impressionFicheDeVenteService->impressionFiheDeVente($historiquesPaiement,$caissiere);
            
        }
        elseif ($request->request->has('impressionFicheVente')) 
        {
            // $dateDebut = DateTime::createFromFormat('Y-m-d',$request->request->get('dateDebut'));
            $dateDebut = date_create($request->request->get('dateDebut'));
            $dateFin = date_create($request->request->get('dateFin'));

            $etatFacture = null;

            $caissiere = $this->userRepository->find($user->getId());
            #ses factures du jours
            if (in_array(ConstantsClass::ROLE_CAISSIERE, $user->getRoles())) 
            {
                $historiquesPaiement = $this->historiquePaiementRepository->historiquePaiementPeriode($caissiere, $etatFacture, $dateDebut, $dateFin);
            } 
            else 
            {
                $historiquesPaiement = $this->historiquePaiementRepository->historiquePaiementPeriode($caissiere, $etatFacture, $dateDebut, $dateFin);
            }

            #date du jour
            $aujourdhui = date_create(date_format(new DateTime('now'), 'Y-m-d'), timezone_open('Pacific/Nauru'));

            // $historiquesPaiement = $this->historiquePaiementRepository->findBy([
            //     'dateAvanceAt' => $aujourdhui, 'recuPar' => $caissiere]);
            
            $pdf = $this->impressionFicheDeVenteService->impressionFiheDeVente($historiquesPaiement, $caissiere, $dateDebut, $dateFin, 1);

        }
        elseif ($recettePeriode != 0) 
        {
            $caissiere = $this->userRepository->find($recettePeriode);

            $etatFacture = null;
            // $dateDebut = DateTime::createFromFormat('Y-m-d',$request->request->get('dateDebut'));
            $dateDebut = date_create($dateDebut);
            $dateFin = date_create($dateFin);

            $historiquesPaiement = $this->historiquePaiementRepository->historiquePaiementPeriode($caissiere, $etatFacture, $dateDebut, $dateFin);
            
            $caissiere = $this->userRepository->find($user->getId());

            #date du jour
            $aujourdhui = date_create(date_format(new DateTime('now'), 'Y-m-d'), timezone_open('Pacific/Nauru'));

            // $historiquesPaiement = $this->historiquePaiementRepository->findBy([
            //     'dateAvanceAt' => $aujourdhui, 'recuPar' => $caissiere]);

            $pdf = $this->impressionFicheDeVenteService->impressionFiheDeVente($historiquesPaiement, $caissiere, $dateDebut, $dateFin, 1);

        }
        else 
        {
            #date du jour
            $aujourdhui = date_create(date_format(new DateTime('now'), 'Y-m-d'), timezone_open('Pacific/Nauru'));
            
            #les historique paiement du jour du jours
            if (in_array(ConstantsClass::ROLE_CAISSIERE, $user->getRoles())) 
            {
                $historiquesPaiement = $this->historiquePaiementRepository->findBy([
                    'dateAvanceAt' => $aujourdhui, 'recuPar' => $caissiere]);
            } 
            else 
            {
                $historiquesPaiement = $this->historiquePaiementRepository->findBy([
                    'dateAvanceAt' => $aujourdhui]);
            }

            $pdf = $this->impressionFicheDeVenteService->impressionFiheDeVente($historiquesPaiement, $caissiere);

        }
        
        if ($dateDebut && $dateFin) 
        {
            return new Response($pdf->Output(utf8_decode("Fiche de vente période du ".date_format($dateDebut, "d-m-Y")." au ".date_format($dateFin, "d-m-Y")." de ".$caissiere->getNom()) , "I"), 200, ['content-type' => 'application/pdf']);
        } 
        else 
        {
            return new Response($pdf->Output(utf8_decode("Fiche de vente du ".date_format(new DateTime('now'), "d-m-Y")) , "I"), 200, ['content-type' => 'application/pdf']);
        }
        
    }
}
