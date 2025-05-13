<?php

namespace App\Controller\Facture;

use DateTime;
use App\Entity\Facture;
use App\Form\AjoutAvanceType;
use App\Entity\ConstantsClass;
use App\Entity\LigneDeFacture;
use App\Service\PanierService;
use App\Service\QrcodeService;
use App\Entity\HistoriquePaiement;
use App\Repository\FactureRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\EtatFactureRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @IsGranted("ROLE_USER", message="Accès refusé. Espace reservé uniquement aux abonnés")
 *
 */
class AjoutAvanceController extends AbstractController
{
    public function __construct(
        protected EntityManagerInterface $em,
        protected PanierService $panierService,
        protected QrcodeService $qrcodeService,
        protected FactureRepository $factureRepository,
        protected EtatFactureRepository $etatFactureRepository,
    )
    {}

    #[Route('/ajout-avance/{slug}', name: 'ajout_avance')]
    public function ajoutAvance(Request $request, $slug): Response
    {
        # je récupère ma session
        $maSession = $request->getSession();
        
        #mes variables témoin pour afficher les sweetAlert
        $maSession->set('ajout', null);
        $maSession->set('suppression', null);

        #je récupère la facture dont je veux ajouter l'avance
        $facture = $this->factureRepository->findOneBySlug([
            'slug' => $slug
        ]);

        $ligneDeFactures = $facture->getLigneDeFactures();

        #je récupère le reste
        $reste = $facture->getNetAPayer() - $facture->getAvance();
            
        // 1. Nous voulons lire les données du formulaire
        //FormFactoryInterface / Request
        $form = $this->createForm(AjoutAvanceType::class, null, ['reste' => $reste]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) 
        {
            $facture = $this->factureRepository->findOneBySlug([
                'slug' => $request->request->get('slugFacture')
            ]);

            $avanceActuelle = $facture->getAvance();
            $avance = $form->getData()->getAvance();
            
            $nouvelleAvance = $avanceActuelle + $avance;
            
            /**
             *@var User
            */
            $user = $this->getUser();

            $now = new DateTime('now');

            #je fabrique mon slug
            $characts    = 'abcdefghijklmnopqrstuvwxyz#{};()';
            $characts   .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ#{};()';	
            $characts   .= '1234567890'; 
            $slug      = ''; 
    
            for($i=0;$i < 11;$i++) 
            { 
                $slug .= substr($characts,rand()%(strlen($characts)),1); 
            }

            //////j'extrait la dernière facture de la table
            $derniereFacture = $this->factureRepository->findBy([],['id' => 'DESC'],1,0);

            if(!$derniereFacture)
            {
                $id = 1;
            }
            else
            {
                /////je récupère l'id de la dernière facture
                $id = $derniereFacture[0]->getId();

            }
           
            ///j'extrais le jour de la date du jour en numérique
            $jour = $now->format('d');

            ///j'exrais le mois de la date du jour en numérique
            $mois = $now->format('m');

            ///j'extrais l'annéé de la dat du jour en numérique
            $annee = $now->format('Y');

            $annee = substr($annee, 2, 2);

            /////je construis la référence
            $reference = 'PP-'.$id.$jour.$mois.$annee;

            #je set l'état de facture en fonction du mode de paiement
            switch ($form->getData()->getModePaiement()->getModePaiement()) 
            {
                case ConstantsClass::CASH:
                    ///je récupère l'état EN COURS pour setter la cammande qui vient d'être passé
                    $solde = $this->etatFactureRepository->findOneByEtatFacture([
                        'etatFacture' => ConstantsClass::SOLDE
                    ]);

                    $nonSolde = $this->etatFactureRepository->findOneByEtatFacture([
                        'etatFacture' => ConstantsClass::NON_SOLDE
                    ]);
                    

                    $qrCode = $this->qrcodeService->generateQrCode("Cette facture appartient à : ".$facture->getNomClient()." Contact : ".$facture->getContactClient().", adresse : ".$facture->getAdresseClient().", email : ".$facture->getEmailClient().", NET A PAYER : ".number_format($facture->getNetAPayer(), 0, '', ' ')."FCFA, Avance : ".number_format($nouvelleAvance, 0, '', ' ')."FCFA, Reste : ".number_format(($facture->getNetAPayer() - $nouvelleAvance), 0, '', ' ')."FCFA, Mode paiment : CASH, Par : ".$user->getNom());


                    /**
                     *@var User
                    */
                    $user = $this->getUser();

                    
                    $factur = new Facture;

                    
                    if ($nouvelleAvance == $facture->getNetAPayer()) 
                    {
                        $factur->setEtatFacture($solde);
                    }
                    else
                    {
                        $factur->setEtatFacture($nonSolde);
                    }

                    $factur
                    ->setCaissiere($user)
                    ->setReference($reference)
                    ->setDateFactureAt($now)
                    ->setHeure($now)
                    ->setAvance($nouvelleAvance)
                    ->setNetAPayer($facture->getNetAPayer())
                    ->setNomClient($facture->getNomClient())
                    ->setContactClient($facture->getContactClient())
                    ->setEmailClient($facture->getEmailClient())
                    ->setAdresseClient($facture->getAdresseClient())
                    ->setQrCode($qrCode)
                    ->setSlug($slug.$id)
                    ->setModePaiement($form->getData()->getModePaiement())
                    ->setAnnulee(0)
                    ;

                    
                    foreach ($ligneDeFactures as $ligneDeFacture) 
                    {
                        $ligneDeFactur = new LigneDeFacture;
                        
                        $ligneDeFactur->setFacture($factur)
                                    ->setProduit($ligneDeFacture->getProduit())
                                    ->setQuantite($ligneDeFacture->getQuantite())
                                    ->setPrix($ligneDeFacture->getPrix())
                                    ->setPrixQuantite($ligneDeFacture->getPrixQuantite())
                                    ;
                        $this->em->persist($ligneDeFactur);
                    }

                    break;


                case ConstantsClass::MOBILE_MONEY:
                    $solde = $this->etatFactureRepository->findOneByEtatFacture([
                        'etatFacture' => ConstantsClass::SOLDE
                    ]);
                    ///je récupère l'état EN COURS pour setter la cammande qui vient d'être passé
                    $nonSolde = $this->etatFactureRepository->findOneByEtatFacture([
                        'etatFacture' => ConstantsClass::NON_SOLDE
                    ]);
                    
                    $qrCode = $this->qrcodeService->generateQrCode("Cette facture appartient à : ".$facture->getNomClient()." Contact : ".$facture->getContactClient().", adresse : ".$facture->getAdresseClient().", email : ".$facture->getEmailClient().", NET A PAYER : ".number_format($facture->getNetAPayer(), 0, '', ' ')."FCFA, Avance : ".number_format($nouvelleAvance, 0, '', ' ')."FCFA, Reste : ".number_format(($facture->getNetAPayer() - $nouvelleAvance), 0, '', ' ')."FCFA, Mode paiment : MOMO, Par : ".$user->getNom());


                    

                    $factur = new Facture;
                    if ($nouvelleAvance == $facture->getNetAPayer()) 
                    {
                        $factur->setEtatFacture($solde);
                    }
                    else
                    {
                        $factur->setEtatFacture($nonSolde);
                    }

                    $factur
                    ->setCaissiere($user)
                    ->setReference($reference)
                    ->setDateFactureAt($now)
                    ->setHeure($now)
                    ->setAvance($nouvelleAvance)
                    ->setNetAPayer($facture->getNetAPayer())
                    ->setNomClient($facture->getNomClient())
                    ->setContactClient($facture->getContactClient())
                    ->setEmailClient($facture->getEmailClient())
                    ->setAdresseClient($facture->getAdresseClient())
                    ->setQrCode($qrCode)
                    ->setSlug($slug.$id)
                    ->setModePaiement($form->getData()->getModePaiement())
                    ->setAnnulee(0)
                    ;

                    foreach ($ligneDeFactures as $ligneDeFacture) 
                    {
                        $ligneDeFactur = new LigneDeFacture;

                        $ligneDeFactur->setFacture($facture)
                                    ->setProduit($ligneDeFacture->getProduit())
                                    ->setQuantite($ligneDeFacture->getQuantite())
                                    ->setPrix($ligneDeFacture->getPrix())
                                    ->setPrixQuantite($ligneDeFacture->getPrixQuantite())
                                    ;
                        $this->em->persist($ligneDeFactur);
                    }
                    break;

                case ConstantsClass::ORANGE_MONEY:
                    $solde = $this->etatFactureRepository->findOneByEtatFacture([
                        'etatFacture' => ConstantsClass::SOLDE
                    ]);
                    ///je récupère l'état EN COURS pour setter la cammande qui vient d'être passé
                    $nonSolde = $this->etatFactureRepository->findOneByEtatFacture([
                        'etatFacture' => ConstantsClass::NON_SOLDE
                    ]);
                    
                    $qrCode = $this->qrcodeService->generateQrCode("Cette facture appartient à : ".$facture->getNomClient()." Contact : ".$facture->getContactClient().", adresse : ".$facture->getAdresseClient().", email : ".$facture->getEmailClient().", NET A PAYER : ".number_format($facture->getNetAPayer(), 0, '', ' ')."FCFA, Avance : ".number_format($nouvelleAvance, 0, '', ' ')."FCFA, Reste : ".number_format(($facture->getNetAPayer() - $nouvelleAvance), 0, '', ' ')."FCFA, Mode paiment : OM, Par : ".$user->getNom());


                    if ($nouvelleAvance == $facture->getNetAPayer()) 
                    {
                        $facture->setEtatFacture($solde);
                    }
                    else
                    {
                        $facture->setEtatFacture($nonSolde);
                    }

                    $factur = new Facture;
                    if ($nouvelleAvance == $this->panierService->getTotal()) 
                    {
                        $factur->setEtatFacture($solde);
                    }
                    else
                    {
                        $factur->setEtatFacture($nonSolde);
                    }

                    $factur
                    ->setCaissiere($user)
                    ->setReference($reference)
                    ->setDateFactureAt($now)
                    ->setHeure($now)
                    ->setAvance($nouvelleAvance)
                    ->setNetAPayer($facture->getNetAPayer())
                    ->setNomClient($facture->getNomClient())
                    ->setContactClient($facture->getContactClient())
                    ->setEmailClient($facture->getEmailClient())
                    ->setAdresseClient($facture->getAdresseClient())
                    ->setQrCode($qrCode)
                    ->setSlug($slug.$id)
                    ->setModePaiement($form->getData()->getModePaiement())
                    ->setAnnulee(0)
                    ;

                    foreach ($ligneDeFactures as $ligneDeFacture) 
                    {
                        $ligneDeFactur = new LigneDeFacture;

                        $ligneDeFactur->setFacture($facture)
                                    ->setProduit($ligneDeFacture->getProduit())
                                    ->setQuantite($ligneDeFacture->getQuantite())
                                    ->setPrix($ligneDeFacture->getPrix())
                                    ->setPrixQuantite($ligneDeFacture->getPrixQuantite())
                                    ;
                        $this->em->persist($ligneDeFactur);
                    }
                    break;
                
                case ConstantsClass::CHEQUE:
                    $solde = $this->etatFactureRepository->findOneByEtatFacture([
                        'etatFacture' => ConstantsClass::SOLDE
                    ]);
                    ///je récupère l'état EN COURS pour setter la cammande qui vient d'être passé
                    $nonSolde = $this->etatFactureRepository->findOneByEtatFacture([
                        'etatFacture' => ConstantsClass::NON_SOLDE
                    ]);
                    
                    $qrCode = $this->qrcodeService->generateQrCode("Cette facture appartient à : ".$facture->getNomClient()." Contact : ".$facture->getContactClient().", adresse : ".$facture->getAdresseClient().", email : ".$facture->getEmailClient().", NET A PAYER : ".number_format($facture->getNetAPayer(), 0, '', ' ')."FCFA, Avance : ".number_format($nouvelleAvance, 0, '', ' ')."FCFA, Reste : ".number_format(($facture->getNetAPayer() - $nouvelleAvance), 0, '', ' ')."FCFA, Mode paiment : CHEQUE, Par : ".$user->getNom());


                    if ($nouvelleAvance == $facture->getNetAPayer()) 
                    {
                        $facture->setEtatFacture($solde);
                    }
                    else
                    {
                        $facture->setEtatFacture($nonSolde);
                    }

                    $factur = new Facture;
                    if ($nouvelleAvance == $this->panierService->getTotal()) 
                    {
                        $factur->setEtatFacture($solde);
                    }
                    else
                    {
                        $factur->setEtatFacture($nonSolde);
                    }

                    $factur
                    ->setCaissiere($user)
                    ->setReference($reference)
                    ->setDateFactureAt($now)
                    ->setHeure($now)
                    ->setAvance($nouvelleAvance)
                    ->setNetAPayer($facture->getNetAPayer())
                    ->setNomClient($facture->getNomClient())
                    ->setContactClient($facture->getContactClient())
                    ->setEmailClient($facture->getEmailClient())
                    ->setAdresseClient($facture->getAdresseClient())
                    ->setQrCode($qrCode)
                    ->setSlug($slug.$id)
                    ->setModePaiement($form->getData()->getModePaiement())
                    ->setAnnulee(0)
                    ;

                    foreach ($ligneDeFactures as $ligneDeFacture) 
                    {
                        $ligneDeFactur = new LigneDeFacture;

                        $ligneDeFactur->setFacture($facture)
                                    ->setProduit($ligneDeFacture->getProduit())
                                    ->setQuantite($ligneDeFacture->getQuantite())
                                    ->setPrix($ligneDeFacture->getPrix())
                                    ->setPrixQuantite($ligneDeFacture->getPrixQuantite())
                                    ;
                        $this->em->persist($ligneDeFactur);
                    }
                    break;

                case ConstantsClass::VIREMENT:
                    $solde = $this->etatFactureRepository->findOneByEtatFacture([
                        'etatFacture' => ConstantsClass::SOLDE
                    ]);
                    ///je récupère l'état EN COURS pour setter la cammande qui vient d'être passé
                    $nonSolde = $this->etatFactureRepository->findOneByEtatFacture([
                        'etatFacture' => ConstantsClass::NON_SOLDE
                    ]);
                    
                    $qrCode = $this->qrcodeService->generateQrCode("Cette facture appartient à : ".$facture->getNomClient()." Contact : ".$facture->getContactClient().", adresse : ".$facture->getAdresseClient().", email : ".$facture->getEmailClient().", NET A PAYER : ".number_format($facture->getNetAPayer(), 0, '', ' ')."FCFA, Avance : ".number_format($nouvelleAvance, 0, '', ' ')."FCFA, Reste : ".number_format(($facture->getNetAPayer() - $nouvelleAvance), 0, '', ' ')."FCFA, Mode paiment : VIREMENT, Par : ".$user->getNom());


                    if ($nouvelleAvance == $facture->getNetAPayer()) 
                    {
                        $facture->setEtatFacture($solde);
                    }
                    else
                    {
                        $facture->setEtatFacture($nonSolde);
                    }

                    $factur = new Facture;

                    if ($nouvelleAvance == $this->panierService->getTotal()) 
                    {
                        $factur->setEtatFacture($solde);
                    }
                    else
                    {
                        $factur->setEtatFacture($nonSolde);
                    }

                    $factur
                    ->setCaissiere($user)
                    ->setReference($reference)
                    ->setDateFactureAt($now)
                    ->setHeure($now)
                    ->setAvance($nouvelleAvance)
                    ->setNetAPayer($facture->getNetAPayer())
                    ->setNomClient($facture->getNomClient())
                    ->setContactClient($facture->getContactClient())
                    ->setEmailClient($facture->getEmailClient())
                    ->setAdresseClient($facture->getAdresseClient())
                    ->setQrCode($qrCode)
                    ->setSlug($slug.$id)
                    ->setModePaiement($form->getData()->getModePaiement())
                    ->setAnnulee(0)
                    ;

                    foreach ($ligneDeFactures as $ligneDeFacture) 
                    {
                        $ligneDeFactur = new LigneDeFacture;

                        $ligneDeFactur->setFacture($factur)
                                    ->setProduit($ligneDeFacture->getProduit())
                                    ->setQuantite($ligneDeFacture->getQuantite())
                                    ->setPrix($ligneDeFacture->getPrix())
                                    ->setPrixQuantite($ligneDeFacture->getPrixQuantite())
                                    ;
                        $this->em->persist($ligneDeFactur);
                    }
                    break;
            
            }
        

            ######j'insère une nouvelle ligne dans la table historique paiement
            $historiquePaiement = new HistoriquePaiement;

            $historiquePaiement->setFacture($factur)
            ->setMontantAvance($avance)
            ->setDateAvanceAt(new DateTime('now'))
            ->setHeure(new DateTime('now'))
            ->setRecuPar($this->getUser());

            ####SI LE NET A PAYER EST EGAL A LA NOUVELLE AVANCE, ON MET LA FACTURE A SOLDE
            if ($facture->getNetApayer() == $nouvelleAvance) 
            {
                $facture->setEtatFacture($this->etatFactureRepository->find(1));
            }
            #je persiste mes entités
            $this->em->persist($factur);
            $this->em->persist($historiquePaiement);

            #je demande à entity manager d'exécuter la requête
            $this->em->flush();

            $this->addFlash('info', 'Avance ajoutée avec succès !');

            return $this->redirectToRoute('liste_facture', ['m' => 1]);

        }

        return $this->render('facture/ajoutAvance.html.twig', [
            'licence' => 1,
            'facture' => $facture,
            'ajoutAvanceForm' => $form->createView(),
        ]);
    }
}
