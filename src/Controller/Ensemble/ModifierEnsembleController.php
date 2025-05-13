<?php

namespace App\Controller\Ensemble;

use App\Form\EnsembleType;
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
class ModifierEnsembleController extends AbstractController
{
    public function __construct(
        protected EntityManagerInterface $em,
        protected TranslatorInterface $translator,
        protected ProduitRepository $produitRepository,
    )
    {}
    
    #[Route('/modifier-ensemble/{slug}', name: 'modifier_ensemble')]
    public function modifierEnsemble(Request $request, string $slug): Response
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

        //je récupère la date de maintenant
        $now = new \DateTime('now');

        //////j'extrait la dernière ensemble de la table
        $derniereEnsemble = $this->produitRepository->findBy([], ['id' => 'DESC'], 1, 0);

        if ($derniereEnsemble) 
        {
            /////je récupère l'id du dernier abonnement
            $id = $derniereEnsemble[0]->getId();
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
            
            $ensemble->setSlug($slug.$id)
            ->setPrixVente($prix);
            
            #je prepare ma requete
            $this->em->persist($ensemble);

            #j'exécute ma requête
            $this->em->flush();

            #j'affiche le message de confirmation
            $this->addFlash('info', $this->translator->trans('Ensemble modifiée avec succès !'));
            
            #j'affecte 1 à ma variable pour afficher le message
            $maSession->set('ajout', 1);
            
            #je redirige vers la liste des ensembles
            return $this->redirectToRoute('liste_ensemble', ['m' => 1 ]);
        }

        return $this->render('ensemble/ajouterEnsemble.html.twig', [
            'slug' => $slug,
            'ensemble' => $ensemble,
            'licence' => 1,
            'formProduit' => $form->createView(),
        ]);
    }
}
