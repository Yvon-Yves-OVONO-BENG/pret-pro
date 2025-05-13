<?php

namespace App\Controller\Panier;

use DateTime;
use App\Entity\ConstantsClass;
use App\Entity\LigneDeFacture;
use App\Service\PanierService;
use App\Service\QrcodeService;
use App\Service\ImpressionFactureService;
use App\Form\PanierAfficherType;
use App\Entity\HistoriquePaiement;
use Symfony\Component\Mime\Address;
use App\Repository\FactureRepository;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\EtatFactureRepository;
use App\Repository\LigneDeFactureRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @IsGranted("ROLE_USER", message="Accès refusé. Espace reservé uniquement aux abonnés")
 *
 */
class PanierAfficherController extends AbstractController
{
    public function __construct(
        protected EntityManagerInterface $em,
        protected PanierService $panierService,
        protected TranslatorInterface $translator,
        protected EtatFactureRepository $etatFactureRepository, 
        protected ProduitRepository $produitRepository,
        protected FactureRepository $factureRepository,
        protected QrcodeService $qrcodeService,
        protected ImpressionFactureService $impressionFactureService,
        protected LigneDeFactureRepository $ligneDeFactureRepository
        
        )
    {}

    #[Route('/panier/{s<[0-1]{1}>}', name: 'panier_afficher')]
    public function afficher(Request $request, PanierService $panierService, string $slug = null, int $s = 0, MailerInterface $mailer = null, TransportInterface $transport = null)
    {
        # je récupère ma session
        $maSession = $request->getSession();
        
        #mes variables témoin pour afficher les sweetAlert
        if ($s == 1) 
        {
            #mes variables témoin pour afficher les sweetAlert
            $maSession->set('ajout', null);
            $maSession->set('misAjour', null);
            $maSession->set('suppression', 1);
            
        }
        else 
        {
            $maSession->set('ajout', null);
            $maSession->set('misAjour', null);
            $maSession->set('suppression', null);
        }
        
        $detailsPanier = $panierService->getDetailsPanierProduits($request);
        
        $total = $panierService->getTotal($request);

        $nombreProduits = count($this->panierService->getDetailsPanierProduits($request));
        $totalApayer = $this->panierService->getTotal($request);

        $maSession->set('nombreProduits', $nombreProduits);
        $maSession->set('totalApayer', $totalApayer);

        // if (count($detailsPanier) == 0) 
        // {
        //     return $this->redirectToRoute('panier_afficher');
        // }

        if ($slug) 
        {
            #je récupère la facture dont je veux valider le solde
            $facture = $this->factureRepository->findOneBySlug([
                'slug' => $slug
            ]); 

            $form = $this->createForm(PanierAfficherType::class, $facture);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) 
            {
                /**
                 * @var Facture
                 */
                $facture = $form->getData();
                
                /**
                 *@var User 
                 */
                $user = $this->getUser();
                #je set l'état de facture en fonction du mode de paiement
                switch ($facture->getModePaiement()->getModePaiement()) 
                {
                    case ConstantsClass::CASH:
                        ///je récupère l'état EN COURS pour setter la cammande qui vient d'être passé
                        $solde = $this->etatFactureRepository->findOneByEtatFacture([
                            'etatFacture' => ConstantsClass::SOLDE
                        ]);

                        $nonSolde = $this->etatFactureRepository->findOneByEtatFacture([
                            'etatFacture' => ConstantsClass::NON_SOLDE
                        ]);
                        
                        
                        $qrCode = $this->qrcodeService->generateQrCode("Cette facture appartient à : ".$facture->getNomClient()." Contact : ".$facture->getContactClient().", adresse : ".$facture->getAdresseClient().", email : ".$facture->getEmailClient().", NET A PAYER : ".$this->panierService->getTotal()."FCFA, Avance : ".$facture->getAvance()."FCFA, Reste : ".$this->panierService->getTotal() - $facture->getAvance()."FCFA, Mode paiment : CASH, Par : ".$user->getNom());


                        if ($facture->getAvance() == $this->panierService->getTotal()) 
                        {
                            $facture->setEtatFacture($solde);
                        }
                        else
                        {
                            $facture->setEtatFacture($nonSolde);
                        }
                        
                        $facture->setNetAPayer($this->panierService->getTotal())
                        ->setQrCode($qrCode);
                        break;


                    case ConstantsClass::COMPOSE:
                        ///je récupère l'état EN COURS pour setter la cammande qui vient d'être passé
                        $solde = $this->etatFactureRepository->findOneByEtatFacture([
                            'etatFacture' => ConstantsClass::SOLDE
                        ]);

                        $nonSolde = $this->etatFactureRepository->findOneByEtatFacture([
                            'etatFacture' => ConstantsClass::NON_SOLDE
                        ]);
                        
                        $qrCode = $this->qrcodeService->generateQrCode("Cette facture appartient à : ".$facture->getNomClient()." Contact : ".$facture->getContactClient().", adresse : ".$facture->getAdresseClient().", email : ".$facture->getEmailClient().", NET A PAYER : ".number_format($this->panierService->getTotal(), 0, '', ' ')."FCFA, Avance : ".number_format($facture->getAvance(), 0, '', ' ')."FCFA, Reste : ".number_format(($this->panierService->getTotal() - $facture->getAvance()), 0, '', ' ')."FCFA, Mode paiment : COMPOSE, Par : ".$user->getNom());


                        if ($facture->getAvance() == $this->panierService->getTotal()) 
                        {
                            $facture->setEtatFacture($solde);
                        }
                        else
                        {
                            $facture->setEtatFacture($nonSolde);
                        }
                        
                        $facture->setNetAPayer($this->panierService->getTotal())
                        ->setQrCode($qrCode);
                        break;


                    case ConstantsClass::MOBILE_MONEY:
                        $solde = $this->etatFactureRepository->findOneByEtatFacture([
                            'etatFacture' => ConstantsClass::SOLDE
                        ]);
                        ///je récupère l'état EN COURS pour setter la cammande qui vient d'être passé
                        $nonSolde = $this->etatFactureRepository->findOneByEtatFacture([
                            'etatFacture' => ConstantsClass::NON_SOLDE
                        ]);
                        
                        $qrCode = $this->qrcodeService->generateQrCode("Cette facture appartient à : ".$facture->getNomClient()." Contact : ".$facture->getContactClient().", adresse : ".$facture->getAdresseClient().", email : ".$facture->getEmailClient().", NET A PAYER : ".$this->panierService->getTotal()."FCFA, Avance : ".$facture->getAvance()."FCFA, Reste : ".$this->panierService->getTotal() - $facture->getAvance()."FCFA, Mode paiment : MOMO, Par : ".$user->getNom());


                        if ($facture->getAvance() == $this->panierService->getTotal()) 
                        {
                            $facture->setEtatFacture($solde);
                        }
                        else
                        {
                            $facture->setEtatFacture($nonSolde);
                        }

                        $facture->setNetAPayer($this->panierService->getTotal())
                        ->setQrCode($qrCode);
                        break;

                    case ConstantsClass::ORANGE_MONEY:
                        $solde = $this->etatFactureRepository->findOneByEtatFacture([
                            'etatFacture' => ConstantsClass::SOLDE
                        ]);
                        ///je récupère l'état EN COURS pour setter la cammande qui vient d'être passé
                        $nonSolde = $this->etatFactureRepository->findOneByEtatFacture([
                            'etatFacture' => ConstantsClass::NON_SOLDE
                        ]);
                        
                        $qrCode = $this->qrcodeService->generateQrCode("Cette facture appartient à : ".$facture->getNomClient()." Contact : ".$facture->getContactClient().", adresse : ".$facture->getAdresseClient().", email : ".$facture->getEmailClient().", NET A PAYER : ".$this->panierService->getTotal()."FCFA, Avance : ".$facture->getAvance()."FCFA, Reste : ".$this->panierService->getTotal() - $facture->getAvance()."FCFA, Mode paiment : OM, Par : ".$user->getNom());


                        if ($facture->getAvance() == $this->panierService->getTotal()) 
                        {
                            $facture->setEtatFacture($solde);
                        }
                        else
                        {
                            $facture->setEtatFacture($nonSolde);
                        }

                        $facture->setNetAPayer($this->panierService->getTotal())
                        ->setQrCode($qrCode);
                        break;
                    
                    case ConstantsClass::CHEQUE:
                        $solde = $this->etatFactureRepository->findOneByEtatFacture([
                            'etatFacture' => ConstantsClass::SOLDE
                        ]);
                        ///je récupère l'état EN COURS pour setter la cammande qui vient d'être passé
                        $nonSolde = $this->etatFactureRepository->findOneByEtatFacture([
                            'etatFacture' => ConstantsClass::NON_SOLDE
                        ]);
                        
                        $qrCode = $this->qrcodeService->generateQrCode("Cette facture appartient à : ".$facture->getNomClient()." Contact : ".$facture->getContactClient().", adresse : ".$facture->getAdresseClient().", email : ".$facture->getEmailClient().", NET A PAYER : ".$this->panierService->getTotal()."FCFA, Avance : ".$facture->getAvance()."FCFA, Reste : ".$this->panierService->getTotal() - $facture->getAvance()."FCFA, Mode paiment : CHEQUE, Par : ".$user->getNom());


                        if ($facture->getAvance() == $this->panierService->getTotal()) 
                        {
                            $facture->setEtatFacture($solde);
                        }
                        else
                        {
                            $facture->setEtatFacture($nonSolde);
                        }

                        $facture->setNetAPayer($this->panierService->getTotal())
                        ->setQrCode($qrCode);
                        break;

                    case ConstantsClass::VIREMENT:
                        $solde = $this->etatFactureRepository->findOneByEtatFacture([
                            'etatFacture' => ConstantsClass::SOLDE
                        ]);
                        ///je récupère l'état EN COURS pour setter la cammande qui vient d'être passé
                        $nonSolde = $this->etatFactureRepository->findOneByEtatFacture([
                            'etatFacture' => ConstantsClass::NON_SOLDE
                        ]);
                        
                        $qrCode = $this->qrcodeService->generateQrCode("Cette facture appartient à : ".$facture->getNomClient()." Contact : ".$facture->getContactClient().", adresse : ".$facture->getAdresseClient().", email : ".$facture->getEmailClient().", NET A PAYER : ".$this->panierService->getTotal()."FCFA, Avance : ".$facture->getAvance()."FCFA, Reste : ".$this->panierService->getTotal() - $facture->getAvance()."FCFA, Mode paiment : VIREMENT, Par : ".$user->getNom());


                        if ($facture->getAvance() == $this->panierService->getTotal()) 
                        {
                            $facture->setEtatFacture($solde);
                        }
                        else
                        {
                            $facture->setEtatFacture($nonSolde);
                        }

                        $facture->setNetAPayer($this->panierService->getTotal())
                        ->setQrCode($qrCode);
                        break;
                
                }


                // 4. Nous allons la lier avec l'utilisateur actuellement connecté (Security)
                
                #je fabrique mon slug
                $characts    = 'abcdefghijklmnopqrstuvwxyz#{};()';
                $characts   .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ#{};()';	
                $characts   .= '1234567890'; 
                $slug      = ''; 
        
                for($i=0;$i < 11;$i++) 
                { 
                    $slug .= substr($characts,rand()%(strlen($characts)),1); 
                }

                //je récupère la date de maintenant
                $now = new \DateTime('now');

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

                if ($facture->getClient()) 
                {
                    $facture->setClient($facture->getClient());
                } 
                else 
                {
                    $facture->setNomClient(ConstantsClass::NOM_PATIENT)
                        ->setContactClient(ConstantsClass::CONTACT_PATIENT)
                    ;
                }
                
                $facture->setDateFactureAt($now)
                        ->setHeure($now)
                        ->setSlug($slug.$id)
                        ->setAnnulee(0)
                        ;
                
                $this->em->persist($facture);

                // 6. Nous allons enregistrer la facture (EntityManagerInterface)
                $this->em->flush(); 


                #j'affiche le message de confirmation d'ajout
                $this->addFlash('info', $this->translator->trans('Facture enregistrée avec succès !'));

                #j'affecte 1 à ma variable pour afficher le message
                $maSession->set('ajout', 1);

                /**
                 *@var User
                */
                $user = $this->getUser();
                
                $session = $request->getSession();
                    
                $session->set('user',$user);
                
                $session->set('facture',$facture);

                $detailsFacture = $this->ligneDeFactureRepository->findBy([
                    'facture' => $facture
                ]);

                // Génération du PDF
                $filePath = $this->getParameter('kernel.project_dir') . '/public/factures/facture-' . $facture->getReference() . ' de '.$facture->getNomClient() .'.pdf';

                // $htmlContent = $this->renderView('factures/facture_pdf.html.twig', [
                //     'facture' => $facture,
                //     'user' => $user,
                // ]);

                try 
                {
                    // Générer et enregistrer le fichier PDF
                    $envoie = 1;
                    $this->impressionFactureService->impressionFacture($facture, $detailsFacture, $envoie, $filePath);
                } 
                catch (\Exception $e) 
                {
                    $this->addFlash('danger', $this->translator->trans("Erreur lors de la génération de la facture PDF !"));
                    return $this->redirectToRoute("liste_facture");
                }

                //envoi du mail
                $email = (new TemplatedEmail())
                ->from(new Address('pretpro@freedomsoftwarepro.com', "Prêt-Pro Service"))
                ->to($facture->getEmailClient())
                ->subject("Votre facture Prêt-Pro")
                ->htmlTemplate('emails/envoieFacture.html.twig')
                ->context([
                    'facture' => $facture,
                    'user' => $user,
                ])
                ->attachFromPath($filePath, 'facture-' . $facture->getReference() . ' de '.$facture->getNomClient() .'.pdf', 'application/pdf');
                    
                    
                try 
                {
                    //j'envoie le mail
                    $transport->send($email);
                    $mailer->send($email);
                
                    //je retourne à la liste des factures
                    $this->addFlash('info',  $this->translator->trans("Facture envoyée avec succès !"));
                    // return $this->redirectToRoute('liste_facture');
                } 
                catch (\Throwable) 
                {
                $this->addFlash('info',  $this->translator->trans("Erreur lors de l'envoi de la facture !"));
                return $this->redirectToRoute('accueil');
                }

                return  $this->redirectToRoute('liste_facture', [ 'm' => 1 ]);
                
            }

        } 
        else 
        {
            #je récupère le netApayer
            $netApayer = $this->panierService->getTotal();
            
            // 1. Nous voulons lire les données du formulaire
            //FormFactoryInterface / Request
            $form = $this->createForm(PanierAfficherType::class, null, ['netApayer' => $netApayer]);
            
            $form->handleRequest($request);

            ////je récupère l'utilisateur connecté
            $user = $this->getUser();

            // 2. Si il y'a pas de produits dans mon panier : dégager(PanierService)
            $produits = $this->panierService->getDetailsPanierProduits();
           
            if (count($produits) === 0) 
            {
                return $this->render("panier/afficherPanier.html.twig",[
                    'licence' => 1,
                    'panierVide' => true,
                ]);
                
            }
            
            if ($form->isSubmitted() && $form->isValid()) 
            {
                // 3. Nous allons créer une facture
                /**
                 *@var Facture
                 */
                $facture = $form->getData();
                
                /**
                 *@var User
                */
                $user = $this->getUser();

                #je set l'état de facture en fonction du mode de paiement
                switch ($facture->getModePaiement()->getModePaiement()) 
                {
                    case ConstantsClass::CASH:
                        ///je récupère l'état EN COURS pour setter la cammande qui vient d'être passé
                        $solde = $this->etatFactureRepository->findOneByEtatFacture([
                            'etatFacture' => ConstantsClass::SOLDE
                        ]);

                        $nonSolde = $this->etatFactureRepository->findOneByEtatFacture([
                            'etatFacture' => ConstantsClass::NON_SOLDE
                        ]);
                        
                        $qrCode = $this->qrcodeService->generateQrCode("Cette facture appartient à : ".$facture->getNomClient()." Contact : ".$facture->getContactClient().", adresse : ".$facture->getAdresseClient().", email : ".$facture->getEmailClient().", NET A PAYER : ".number_format($this->panierService->getTotal(), 0, '', ' ')."FCFA, Avance : ".number_format($facture->getAvance(), 0, '', ' ')."FCFA, Reste : ".number_format(($this->panierService->getTotal() - $facture->getAvance()), 0, '', ' ')."FCFA, Mode paiment : CASH, Par : ".$user->getNom());


                        if ($facture->getAvance() == $this->panierService->getTotal()) 
                        {
                            $facture->setEtatFacture($solde);
                        }
                        else
                        {
                            $facture->setEtatFacture($nonSolde);
                        }
                        
                        $facture->setNetAPayer($this->panierService->getTotal())
                        ->setQrCode($qrCode);
                        break;


                    case ConstantsClass::COMPOSE:
                        ///je récupère l'état EN COURS pour setter la cammande qui vient d'être passé
                        $solde = $this->etatFactureRepository->findOneByEtatFacture([
                            'etatFacture' => ConstantsClass::SOLDE
                        ]);

                        $nonSolde = $this->etatFactureRepository->findOneByEtatFacture([
                            'etatFacture' => ConstantsClass::NON_SOLDE
                        ]);
                        
                        $qrCode = $this->qrcodeService->generateQrCode("Cette facture appartient à : ".$facture->getNomClient()." Contact : ".$facture->getContactClient().", adresse : ".$facture->getAdresseClient().", email : ".$facture->getEmailClient().", NET A PAYER : ".number_format($this->panierService->getTotal(), 0, '', ' ')."FCFA, Avance : ".number_format($facture->getAvance(), 0, '', ' ')."FCFA, Reste : ".number_format(($this->panierService->getTotal() - $facture->getAvance()), 0, '', ' ')."FCFA, Mode paiment : COMPOSE, Par : ".$user->getNom());


                        if ($facture->getAvance() == $this->panierService->getTotal()) 
                        {
                            $facture->setEtatFacture($solde);
                        }
                        else
                        {
                            $facture->setEtatFacture($nonSolde);
                        }
                        
                        $facture->setNetAPayer($this->panierService->getTotal())
                        ->setQrCode($qrCode);
                        break;


                    case ConstantsClass::MOBILE_MONEY:
                        $solde = $this->etatFactureRepository->findOneByEtatFacture([
                            'etatFacture' => ConstantsClass::SOLDE
                        ]);
                        ///je récupère l'état EN COURS pour setter la cammande qui vient d'être passé
                        $nonSolde = $this->etatFactureRepository->findOneByEtatFacture([
                            'etatFacture' => ConstantsClass::NON_SOLDE
                        ]);
                        
                        $qrCode = $this->qrcodeService->generateQrCode("Cette facture appartient à : ".$facture->getNomClient()." Contact : ".$facture->getContactClient().", adresse : ".$facture->getAdresseClient().", email : ".$facture->getEmailClient().", NET A PAYER : ".number_format($this->panierService->getTotal(), 0, '', ' ')."FCFA, Avance : ".number_format($facture->getAvance(), 0, '', ' ')."FCFA, Reste : ".number_format(($this->panierService->getTotal() - $facture->getAvance()), 0, '', ' ')."FCFA, Mode paiment : MOMO, Par : ".$user->getNom());


                        if ($facture->getAvance() == $this->panierService->getTotal()) 
                        {
                            $facture->setEtatFacture($solde);
                        }
                        else
                        {
                            $facture->setEtatFacture($nonSolde);
                        }

                        $facture->setNetAPayer($this->panierService->getTotal())
                        ->setQrCode($qrCode);
                        break;

                    case ConstantsClass::ORANGE_MONEY:
                        $solde = $this->etatFactureRepository->findOneByEtatFacture([
                            'etatFacture' => ConstantsClass::SOLDE
                        ]);
                        ///je récupère l'état EN COURS pour setter la cammande qui vient d'être passé
                        $nonSolde = $this->etatFactureRepository->findOneByEtatFacture([
                            'etatFacture' => ConstantsClass::NON_SOLDE
                        ]);
                        
                        $qrCode = $this->qrcodeService->generateQrCode("Cette facture appartient à : ".$facture->getNomClient()." Contact : ".$facture->getContactClient().", adresse : ".$facture->getAdresseClient().", email : ".$facture->getEmailClient().", NET A PAYER : ".number_format($this->panierService->getTotal(), 0, '', ' ')."FCFA, Avance : ".number_format($facture->getAvance(), 0, '', ' ')."FCFA, Reste : ".number_format(($this->panierService->getTotal() - $facture->getAvance()), 0, '', ' ')."FCFA, Mode paiment : OM, Par : ".$user->getNom());


                        if ($facture->getAvance() == $this->panierService->getTotal()) 
                        {
                            $facture->setEtatFacture($solde);
                        }
                        else
                        {
                            $facture->setEtatFacture($nonSolde);
                        }

                        $facture->setNetAPayer($this->panierService->getTotal())
                        ->setQrCode($qrCode);
                        break;
                    
                    case ConstantsClass::CHEQUE:
                        $solde = $this->etatFactureRepository->findOneByEtatFacture([
                            'etatFacture' => ConstantsClass::SOLDE
                        ]);
                        ///je récupère l'état EN COURS pour setter la cammande qui vient d'être passé
                        $nonSolde = $this->etatFactureRepository->findOneByEtatFacture([
                            'etatFacture' => ConstantsClass::NON_SOLDE
                        ]);
                        
                        $qrCode = $this->qrcodeService->generateQrCode("Cette facture appartient à : ".$facture->getNomClient()." Contact : ".$facture->getContactClient().", adresse : ".$facture->getAdresseClient().", email : ".$facture->getEmailClient().", NET A PAYER : ".number_format($this->panierService->getTotal(), 0, '', ' ')."FCFA, Avance : ".number_format($facture->getAvance(), 0, '', ' ')."FCFA, Reste : ".number_format(($this->panierService->getTotal() - $facture->getAvance()), 0, '', ' ')."FCFA, Mode paiment : CHEQUE, Par : ".$user->getNom());


                        if ($facture->getAvance() == $this->panierService->getTotal()) 
                        {
                            $facture->setEtatFacture($solde);
                        }
                        else
                        {
                            $facture->setEtatFacture($nonSolde);
                        }

                        $facture->setNetAPayer($this->panierService->getTotal())
                        ->setQrCode($qrCode);
                        break;

                    case ConstantsClass::VIREMENT:
                        $solde = $this->etatFactureRepository->findOneByEtatFacture([
                            'etatFacture' => ConstantsClass::SOLDE
                        ]);
                        ///je récupère l'état EN COURS pour setter la cammande qui vient d'être passé
                        $nonSolde = $this->etatFactureRepository->findOneByEtatFacture([
                            'etatFacture' => ConstantsClass::NON_SOLDE
                        ]);
                        
                        $qrCode = $this->qrcodeService->generateQrCode("Cette facture appartient à : ".$facture->getNomClient()." Contact : ".$facture->getContactClient().", adresse : ".$facture->getAdresseClient().", email : ".$facture->getEmailClient().", NET A PAYER : ".number_format($this->panierService->getTotal(), 0, '', ' ')."FCFA, Avance : ".number_format($facture->getAvance(), 0, '', ' ')."FCFA, Reste : ".number_format(($this->panierService->getTotal() - $facture->getAvance()), 0, '', ' ')."FCFA, Mode paiment : VIREMENT, Par : ".$user->getNom());


                        if ($facture->getAvance() == $this->panierService->getTotal()) 
                        {
                            $facture->setEtatFacture($solde);
                        }
                        else
                        {
                            $facture->setEtatFacture($nonSolde);
                        }

                        $facture->setNetAPayer($this->panierService->getTotal())
                        ->setQrCode($qrCode);
                        break;
                
                }


                // 4. Nous allons la lier avec l'utilisateur actuellement connecté (Security)
                
                #je fabrique mon slug
                $characts    = 'abcdefghijklmnopqrstuvwxyz#{};()';
                $characts   .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ#{};()';	
                $characts   .= '1234567890'; 
                $slug      = ''; 
        
                for($i=0;$i < 11;$i++) 
                { 
                    $slug .= substr($characts,rand()%(strlen($characts)),1); 
                }

                //je récupère la date de maintenant
                $now = new \DateTime('now');

                ///j'extrais le jour de la date du jour en numérique
                $jour = $now->format('d');

                ///j'exrais le mois de la date du jour en numérique
                $mois = $now->format('m');

                ///j'extrais l'annéé de la dat du jour en numérique
                $annee = $now->format('Y');

                $annee = substr($annee, 2, 2);

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

                /////je construis la référence
                $reference = 'PP-'.$id.$jour.$mois.$annee;

                // if ($facture->getClient()) 
                // {
                //     $facture->setClient($facture->getClient());
                // } 

                $facture->setCaissiere($user)
                        ->setDateFactureAt($now)
                        ->setHeure($now)
                        ->setReference($reference)
                        ->setSlug($slug.$id)
                        ->setAnnulee(0)
                        ;
                
                $this->em->persist($facture);
                
                /////NOUVELLE HISTORIQUE FACTURE
                $historiquePaiement = new HistoriquePaiement;
                
                $historiquePaiement->setFacture($facture)
                ->setDateAvanceAt($now)
                ->setHeure($now)
                ->setMontantAvance($facture->getAvance())
                ->setRecuPar($this->getUser());
                
                $this->em->persist($historiquePaiement);

                // 5. Nous allons parcourir les produits qui sont dans le panier (PanierService)
                foreach ($this->panierService->getDetailsPanierProduits() as $panierProduit) 
                {
                    #je déclare une ligne de facture pour chaque produit de la facture
                    $ligneDeFacture = new LigneDeFacture;

                    #pour chaque ligne de facture
                    $ligneDeFacture->setFacture($facture)
                                    ->setProduit($panierProduit->produit)
                                    ->setQuantite($panierProduit->qte);
                                   
                    switch ($facture->getModePaiement()->getModePaiement()) 
                    {
                        case ConstantsClass::CASH:
                            $ligneDeFacture->setPrix($panierProduit->produit->getPrixVente())
                            ->setPrixQuantite($panierProduit->getTotal());
                            break;

                        case ConstantsClass::COMPOSE:
                            $ligneDeFacture->setPrix($panierProduit->produit->getPrixVente())
                            ->setPrixQuantite($panierProduit->getTotal());
                            break;
    
                        case ConstantsClass::ORANGE_MONEY:
                            $ligneDeFacture->setPrix($panierProduit->produit->getPrixVente())
                            ->setPrixQuantite($panierProduit->getTotal());
                            break;

                        case ConstantsClass::MOBILE_MONEY:
                            $ligneDeFacture->setPrix($panierProduit->produit->getPrixVente())
                            ->setPrixQuantite($panierProduit->getTotal());
                            break;

                        case ConstantsClass::CHEQUE:
                            $ligneDeFacture->setPrix($panierProduit->produit->getPrixVente())
                            ->setPrixQuantite($panierProduit->getTotal());
                            break;

                        case ConstantsClass::VIREMENT:
                            $ligneDeFacture->setPrix($panierProduit->produit->getPrixVente())
                            ->setPrixQuantite($panierProduit->getTotal());
                            break;
                        
                    }
                    
                    if ($panierProduit->produit->isEnsemble()) 
                    {   
                        foreach ($panierProduit->produit->getProduitLigneDeEnsembles() as $ligneDeEnsemble) 
                        {   
                            #quantite de la facture
                            $quantiteFacture = $ligneDeEnsemble->getQuantite() * $panierProduit->qte;
                                    
                            // dd($lot);
                            if ($ligneDeEnsemble->getProduit()->getLot()) 
                            {
                                #nombreVendu dans un lot
                                $ancienneQuantiteVenduLot = $ligneDeEnsemble->getProduit()->getLot()->getVendu();

                                #nouvelle quantité vendu
                                $nouvelleQuaniteVenduLot = $ancienneQuantiteVenduLot + $quantiteFacture;
                                
                                $ligneDeEnsemble->getProduit()->getLot()->setVendu($nouvelleQuaniteVenduLot);
                                $this->em->persist($ligneDeEnsemble->getProduit()->getLot());

                            }

                            $this->em->persist($ligneDeFacture);
                            $this->em->persist($ligneDeEnsemble);
                            
                        }
                    } 
                    else 
                    {
                        #je récupère chaque produit pour diminuer le nombre en stock
                        $produi = $this->produitRepository->findOneBySlug([
                            'slug' => $panierProduit->produit->getSlug()
                        ]);

                        #quantite de la facture
                        $quantiteFacture = $panierProduit->qte;

                        #je récupère le lot
                        $lot = $produi->getLot();

                        if ($lot) 
                        {
                            #nombreVendu dans un lot
                            $ancienneQuantiteVenduLot = $lot->getVendu();

                            #nouvelle quantité vendu
                            $nouvelleQuaniteVenduLot = $ancienneQuantiteVenduLot + $quantiteFacture;
                            
                            $lot->setVendu($nouvelleQuaniteVenduLot);
                            $this->em->persist($lot);

                        }

                        $this->em->persist($ligneDeFacture);
                        $this->em->persist($produi);
                    }
                    
                }
               
                // 6. Nous allons enregistrer la facture (EntityManagerInterface)
                $this->em->flush(); 
                
                #je vide le panier
                $this->panierService->viderPanier();

                #j'affiche le message de confirmation d'ajout
                $this->addFlash('info', $this->translator->trans('Facture payée avec succès !'));

                $nombreProduits = count($this->panierService->getDetailsPanierProduits($request));
                $totalApayer = $this->panierService->getTotal($request);

                $maSession->set('nombreProduits', $nombreProduits);
                $maSession->set('totalApayer', $totalApayer);
                
                #j'affecte 1 à ma variable pour afficher le message
                $maSession->set('ajout', 1);

                /**
                 *@var User
                */
                $user = $this->getUser();
                
                $session = $request->getSession();
                    
                $session->set('user',$user);
                
                $session->set('facture',$facture);

                $detailsFacture = $this->ligneDeFactureRepository->findBy([
                    'facture' => $facture
                ]);

                // Génération du PDF
                $filePath = $this->getParameter('kernel.project_dir') . '/public/factures/facture-' . $facture->getReference() . ' de '.$facture->getNomClient() .'.pdf';

                // $htmlContent = $this->renderView('factures/facture_pdf.html.twig', [
                //     'facture' => $facture,
                //     'user' => $user,
                // ]);

                try 
                {
                    // Générer et enregistrer le fichier PDF
                    $envoie = 1;
                    $this->impressionFactureService->impressionFacture($facture, $detailsFacture, $envoie, $filePath);
                } 
                catch (\Exception $e) 
                {
                    $this->addFlash('danger', $this->translator->trans("Erreur lors de la génération de la facture PDF !"));
                    return $this->redirectToRoute("liste_facture");
                }

                //envoi du mail
                $email = (new TemplatedEmail())
                ->from(new Address('pretpro@freedomsoftwarepro.com', "Prêt-Pro Service"))
                ->to($facture->getEmailClient())
                ->subject("Votre facture Prêt-Pro")
                ->htmlTemplate('emails/envoieFacture.html.twig')
                ->context([
                    'facture' => $facture,
                    'user' => $user,
                ])
                ->attachFromPath($filePath, 'facture-' . $facture->getReference() . ' de '.$facture->getNomClient() .'.pdf', 'application/pdf');
                    
                    
                try 
                {
                    //j'envoie le mail
                    $transport->send($email);
                    $mailer->send($email);
                
                    //je retourne à la liste des factures
                    $this->addFlash('info',  $this->translator->trans("Facture envoyée avec succès !"));
                    // return $this->redirectToRoute('liste_facture');
                } 
                catch (\Throwable) 
                {
                    $this->addFlash('info',  $this->translator->trans("Erreur lors de l'envoi de la facture !"));
                    return $this->redirectToRoute('accueil');
                }

                return  $this->redirectToRoute('details_facture', [ 'slug' => $slug.$id, 'm' => 1 ]);
                
            }
        }


        return $this->render("panier/afficherPanier.html.twig",[
            'licence' => 1,
            'total' => $total,
            'produits' => $detailsPanier,
            'panierVide' => false,
            'confirmerFactureForm' => $form->createView(),
        ]);
    }
}
