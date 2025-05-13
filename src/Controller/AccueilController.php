<?php

namespace App\Controller;

use DateTime;
use App\Entity\ConstantsClass;
use App\Repository\CommandeRepository;
use App\Repository\FactureRepository;
use App\Repository\LicenceRepository;
use App\Repository\LotRepository;
use App\Repository\ProduitRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\RouterInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @IsGranted("ROLE_USER and !user.etat", message="Accès refusé. Espace reservé uniquement aux abonnés")
 *
 */
class AccueilController extends AbstractController
{
    public function __construct(
        protected RouterInterface $router,
        protected EntityManagerInterface $em,
        protected LotRepository $lotRepository,
        protected TranslatorInterface $translator,
        protected UserRepository $userRepository,
        protected LicenceRepository $licenceRepository,
        protected ProduitRepository $produitRepository,
        protected FactureRepository $factureRepository,
        protected CommandeRepository $commandeRepository,
    )
    {}

    #[Route('/accueil', name: 'accueil')]
    public function accueil(Request $request): Response
    {
        $collection = $this->router->getRouteCollection();
        $allRoutes = $collection->all();

        # je récupère ma session
        $maSession = $request->getSession();

        #mes variables témoin pour afficher les sweetAlert
        $maSession->set('ajout', null);
        $maSession->set('miseAjour', null);
        $maSession->set('suppression', null);
        
        /**
         *@var User
         */
        $user = $this->getUser();
        
        #si l'utilisateur n'est pas connecté déconnecte le
        if (!$user) 
        {   
            return $this->redirectToRoute('app_logout');
        }

        $factures = [];
        $facturesDuJour = [];
        $facturesDuJourCaissiere = [];
        #date du jour
        $aujourdhui = new DateTime('now');

        #je formate 'maintenant' pour calculer le nombre de jours restants de la licence
        $maintenant1 = date_format($aujourdhui, 'Y-m-d');
        $maintenant = new DateTime($maintenant1);

        #je récupère la date d'expiration et je la formate
        $dateExpiration1 = date_format($this->licenceRepository->findAll()[0]->getDateExpirationAt(), 'Y-m-d');
        $dateExpiration = new DateTime($dateExpiration1);

        #je calcul la différence
        $dateDiffExpiration = $maintenant->diff($dateExpiration);
        
        #je récupère tous les utilisateurs
        $utilisateurs = $this->userRepository->findAll();
        $nombreJoursRestant = (int)$dateDiffExpiration->format('%R%a');

        #si le nombre de jour restant est supérieur à zéro(0)
        if ((int)$dateDiffExpiration->format('%R%a') >= 0 ) 
        {
            
            $licence = $this->licenceRepository->findAll()[0];

            $licence->setNombreJours($nombreJoursRestant);
            $this->em->persist($licence);
            $this->em->flush();

            //2. Toutes Ses factures
           

            if (in_array(ConstantsClass::ROLE_REGIES_DES_RECETTES, $user->getRoles()) || 
            in_array(ConstantsClass::ROLE_ADMINISTRATEUR, $user->getRoles()) || 
            in_array(ConstantsClass::ROLE_SECRETAIRE, $user->getRoles())) 
            {
                #ses factures du jours
                $facturesDuJour = $this->factureRepository->findBy([
                    'dateFactureAt' => $aujourdhui,
                    'annulee' => 0,
                ], ['dateFactureAt' => 'DESC']);
                
                $factures = $this->factureRepository->findBy([
                    'annulee' => 0,
                ], ['id' => 'DESC']);
            } 
            elseif(in_array(ConstantsClass::ROLE_CAISSIERE, $user->getRoles()))
            {
                #ses factures du jours
                $facturesDuJour = $this->factureRepository->findBy([
                    'dateFactureAt' => $aujourdhui,
                    'annulee' => 0,
                    'caissiere' => $this->userRepository->find($user->getId())
                ], ['dateFactureAt' => 'DESC']);

                $factures = $this->factureRepository->findBy([
                    'annulee' => 0,
                    'caissiere' => $user
                ], ['id' => 'DESC']);
            }
            
            
            #je recupère tous les produits
            $tousLesProduits = $this->produitRepository->findBy([
                'ensemble' => 0,
                'supprime' => 0,
            ]);

            #je récupère les produits dont la date de premption est non nulle
            $produits = $this->produitRepository->produits();

            #je calcul les produits périmés dans moins de 90 jours
            $produitsBientotPerimes = [];

            foreach ($produits as $produit) 
            {
                $aujourdhui = date_format(new DateTime('now'), 'Y-m-d');
                $aujourdhui = new DateTime($aujourdhui);
            
            }

            #je calcul les produits périmés
            $produitsPerimes = [];

            foreach ($produits as $produit) 
            {
                #je récupère le nombre de jour entre la date du jour et la date de peremption du produit
                $aujourdhui = date_format(new DateTime('now'), 'Y-m-d');
                $aujourdhui = new DateTime($aujourdhui);

            }

            #les commandes
            #je recupère toutes les commandes pour compter
            $commandes = $this->commandeRepository->findBy([], ['id' => 'DESC' ]);

            ###############################
            #mes variables
            $nombreCash = 0;
            $montantCash = 0;

            $nombreCheque = 0;
            $montantCheque = 0;

            $nombreMomo = 0;
            $montantMomo = 0;

            $nombreOm = 0;
            $montantOm = 0;

            $nombreVirement = 0;
            $montantVirement = 0;

            foreach ($factures as $facture) 
            {
                switch ($facture->getModePaiement()->getModePaiement()) 
                {
                    case ConstantsClass::CASH:
                        $nombreCash = $nombreCash + 1;
                        $montantCash += $facture->getAvance();
                        break;

                    case ConstantsClass::CHEQUE:
                        $nombreCheque = $nombreCheque + 1;
                        $montantCheque += $facture->getAvance();
                        break;

                    case ConstantsClass::MOBILE_MONEY:
                        $nombreMomo = $nombreMomo + 1;
                        $montantMomo += $facture->getAvance();
                        break;

                    case ConstantsClass::ORANGE_MONEY:
                        $nombreOm = $nombreOm + 1;
                        $montantOm += $facture->getAvance();
                        break;


                    case ConstantsClass::VIREMENT:
                        $nombreVirement = $nombreVirement + 1;
                        $montantVirement += $facture->getAvance();
                        break;
                        
                        
                    
                }
            }

            ###################
            #mes variables
            $nombreCashDuJour = 0;
            $montantCashDuJour = 0;

            $nombreChequeDuJour = 0;
            $montantChequeDuJour = 0;

            $nombreMomoDuJour = 0;
            $montantMomoDuJour = 0;

            $nombreOmDuJour = 0;
            $montantOmDuJour = 0;

            $nombreVirementDuJour = 0;
            $montantVirementDuJour = 0;

            foreach ($facturesDuJour as $factureDuJour) 
            {
                switch ($factureDuJour->getModePaiement()->getModePaiement()) {
                    case ConstantsClass::CASH:
                        $nombreCashDuJour = $nombreCashDuJour + 1;
                        $montantCashDuJour += $factureDuJour->getAvance();
                        break;

                    case ConstantsClass::CHEQUE:
                        $nombreChequeDuJour = $nombreChequeDuJour + 1;
                        $montantChequeDuJour += $factureDuJour->getAvance();
                        break;

                    case ConstantsClass::MOBILE_MONEY:
                        $nombreMomoDuJour = $nombreMomoDuJour + 1;
                        $montantMomoDuJour += $factureDuJour->getAvance();
                        break;

                    case ConstantsClass::ORANGE_MONEY:
                        $nombreOmDuJour = $nombreOmDuJour + 1;
                        $montantOmDuJour += $factureDuJour->getAvance();
                        break;

                    case ConstantsClass::VIREMENT:
                        $nombreVirementDuJour = $nombreVirementDuJour + 1;
                        $montantVirementDuJour += $factureDuJour->getAvance();
                        break;

                    
                    
                }
            }

            #je recupère les lots
            $lots = $this->lotRepository->findAll();

            if (in_array(ConstantsClass::ROLE_REGIES_DES_RECETTES, $user->getRoles()) || 
            in_array(ConstantsClass::ROLE_ADMINISTRATEUR, $user->getRoles()) || 
            in_array(ConstantsClass::ROLE_SECRETAIRE, $user->getRoles())) 
            {
                #ses factures du jours de la caisiere
                $facturesDuJourCaissiere = $this->factureRepository->findBy([
                    'dateFactureAt' => $aujourdhui,
                    'annulee' => 0
                ], ['dateFactureAt' => 'DESC']);
            }
            elseif(in_array(ConstantsClass::ROLE_CAISSIERE, $user->getRoles()))
            {
                #ses factures du jours de la caisiere
                $facturesDuJourCaissiere = $this->factureRepository->findBy([
                    'caissiere' => $this->userRepository->find($user->getId()),
                    'dateFactureAt' => $aujourdhui,
                    'annulee' => 0
                ], ['dateFactureAt' => 'DESC']);
            }

            return $this->render('accueil/index.html.twig', [
                'licence' => 1,
                'nombreDeJoursRestants' => $nombreJoursRestant, 
                'tousLesProduits' => $tousLesProduits,
                'commandes' => $commandes,
                'facturesDuJourCaissiere' => $facturesDuJourCaissiere,

                'nombreCash' => $nombreCash,
                'montantCash' => $montantCash,
                'nombreCheque' => $nombreCheque,
                'montantCheque' => $montantCheque,
                'nombreMomo' => $nombreMomo,
                'montantMomo' => $montantMomo,
                'nombreOm' => $nombreOm,
                'montantOm' => $montantOm,
                'nombreVirement' => $nombreVirement,
                'montantVirement' => $montantVirement,

                'nombreCashDuJour' => $nombreCashDuJour,
                'montantCashDuJour' => $montantCashDuJour,
                'nombreChequeDuJour' => $nombreChequeDuJour,
                'montantChequeDuJour' => $montantChequeDuJour,
                'nombreMomoDuJour' => $nombreMomoDuJour,
                'montantMomoDuJour' => $montantMomoDuJour,
                'nombreOmDuJour' => $nombreOmDuJour,
                'montantOmDuJour' => $montantOmDuJour,
                'nombreVirementDuJour' => $nombreOmDuJour,
                'montantVirementDuJour' => $montantOmDuJour,

                'aujourdhui' => $aujourdhui,
                'factures' => compact("factures"),
                
                'produitsPerimes' => $produitsPerimes,
                'produitsBientotPerimes' => $produitsBientotPerimes,

                ##########################
                'lots' => $lots,
                'produits' => $produits,
                'factureAnnulee' => 0,

            ]);
        }
        else
        {
            return $this->render('accueil/index.html.twig', [
                'licence' => 0,
            ]);
        }

        

    }
}
