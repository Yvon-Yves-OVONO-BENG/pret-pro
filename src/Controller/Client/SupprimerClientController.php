<?php

namespace App\Controller\Client;

use App\Repository\FactureRepository;
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
#[Route('/client')]
class SupprimerClientController extends AbstractController
{
    public function __construct(
        protected EntityManagerInterface $em,
        protected TranslatorInterface $translator,
        protected FactureRepository $factureRepository
    )
    {}

    #[Route('/supprimer-client/{code}', name: 'supprimer_client')]
    public function supprimerClient(Request $request, string $code): Response
    {
        # je récupère ma session
        $maSession = $request->getSession();
        
        #mes variables témoin pour afficher les sweetAlert
        $maSession->set('ajout', null);
        $maSession->set('suppression', null);
        
        

        #je récupère la catégorie à supprimer
        $client = $this->factureRepository->findOneByCode([
            'code' => $code
        ]);
        
        #je prépare ma requête à la suppression
        $this->em->remove($client);

        #j'exécute ma requête
        $this->em->flush();

        #j'affiche le message de confirmation d'ajout
        $this->addFlash('info', $this->translator->trans('Client supprimée avec succès !'));

        #j'affecte 1 à ma variable pour afficher le message
        $maSession->set('suppression', 1);

        #je retourne à la liste des catégories
        return $this->redirectToRoute('afficher_client', [ 's' => 1 ]);
    }
}
