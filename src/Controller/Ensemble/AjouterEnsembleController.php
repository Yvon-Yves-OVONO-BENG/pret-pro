<?php

namespace App\Controller\Ensemble;

use App\Entity\ConstantsClass;
use App\Entity\Produit;
use App\Form\EnsembleType;
use App\Service\StrService;
use App\Repository\ProduitRepository;
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
class AjouterEnsembleController extends AbstractController
{
    public function __construct(
        protected StrService $strService,
        protected EntityManagerInterface $em,
        protected TranslatorInterface $translator,
        protected ProduitRepository $produitRepository
    )
    {}

    #[Route('/ajouter-ensemble', name: 'ajouter_ensemble')]
    public function ajouterEnsemble(Request $request): Response
    {
        # je récupère ma session
        $maSession = $request->getSession();
        
        #mes variables témoin pour afficher les sweetAlert
        $maSession->set('ajout', null);
        $maSession->set('suppression', null);
        
        #j'initialise le slug
        $slug = 0;

        #je céclare une nouvelle instance ensemble
        $ensemble = new Produit;

        #je lie mon formulaire à ma nouvelle instance
        $form = $this->createForm(EnsembleType::class, $ensemble);

        #je demande à mon formulaire de gueter tout ce qui est dans le POST
        $form->handleRequest($request);

        #je construis le code pour la reference de la ensemble
        $characts    = 'abcdefghijklmnopqrstuvwxyz';
        $characts   .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $characts   .= '1234567890';
        $slug      = '';

        for ($i = 0; $i < 11; $i++) {
            $slug .= substr($characts, rand() % (strlen($characts)), 1);
        }

        //////j'extrait la dernière ensemble de la table
        $derniereProduit = $this->produitRepository->findBy([], ['id' => 'DESC'], 1, 0);

        if ($derniereProduit) 
        {
            /////je récupère l'id du dernier abonnement
            $id = $derniereProduit[0]->getId();
        } 
        else 
        {
            $id = 1;
        }

        if ($form->isSubmitted() && $form->isValid()) 
        {
            $prix = 0;
            $ligneDeEnsembles = $ensemble->getProduitLigneDeEnsembles();

            foreach ($ligneDeEnsembles as $ligneDeEnsemble) 
            {
                $ligneDeEnsemble->setPrix($ligneDeEnsemble->getProduit()->getPrixVente());
                $ligneDeEnsemble->setTotal($ligneDeEnsemble->getProduit()->getPrixVente() * $ligneDeEnsemble->getQuantite());
                $prix += ($ligneDeEnsemble->getProduit()->getPrixVente() * $ligneDeEnsemble->getQuantite());

                $this->em->persist($ligneDeEnsemble);
            }

            $ensemble->setEnsemble($this->strService->strToUpper($ensemble->getLibelle()))
            ->setPrixVente($prix)
            ->setEnsemble(1)
            ->setRetire(0)
            ->setSupprime(0)
            ->setSlug($slug.$id)
            ->setPhoto(ConstantsClass::NOM_PRODUIT)
            ;

            
            #je prepare ma requete
            $this->em->persist($ensemble);

            #j'exécute ma requête
            $this->em->flush();

            #j'affiche le message de confirmation
            $this->addFlash('info', $this->translator->trans('Ensemble ajouté avec succès !'));
            
            #j'affecte 1 à ma variable pour afficher le message
            $maSession->set('ajout', 1);
            
            

            #je declare une nouvelle instance
            $ensemble = new Produit;

            #je lie mon formulaire à la nouvelle instance
            $form = $this->createForm(EnsembleType::class, $ensemble);
        }

        #je rénitialise mon slug
        $slug = 0;

        return $this->render('ensemble/ajouterEnsemble.html.twig', [
            'licence' => 1,
            'slug' => $slug,
            'formProduit' => $form->createView(),
        ]);
    }
}
