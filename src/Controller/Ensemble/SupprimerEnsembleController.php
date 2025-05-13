<?php

namespace App\Controller\Ensemble;

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
class SupprimerEnsembleController extends AbstractController
{
    public function __construct(
        protected EntityManagerInterface $em,
        protected TranslatorInterface $translator,
        protected ProduitRepository $produitRepository,
    )
    {}
    
    #[Route('/supprimer-ensemble/{slug}', name: 'supprimer_ensemble')]
    public function supprimerEnsemble(Request $request, string $slug): Response
    {

        # je récupère ma session
        $maSession = $request->getSession();
        
        #mes variables témoin pour afficher les sweetAlert
        $maSession->set('ajout', null);
        $maSession->set('suppression', null);
        
        

        # je récupère la ensemble dont je veux modifier l'état
        $ensemble = $this->produitRepository->findOneBySlug([
            'slug' => $slug
        ]);

        #je prépare ma requete à la suppression
        $ensemble->setSupprime(1);

        #j'exécute ma requete
        $this->em->flush();

        #j'affiche le message de confirmation
        $this->addFlash('info', $this->translator->trans('Ensemble supprimé avec succès !'));
            
        #j'affecte 1 à ma variable pour afficher le message
        $maSession->set('suppression', 1);
        
        

        #je redirige vers la liste des ensmebles
        return $this->redirectToRoute('liste_ensemble', ['s' => 1 ]);
    }
}
