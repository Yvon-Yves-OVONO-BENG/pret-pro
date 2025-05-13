<?php

namespace App\Controller\Client;

use App\Form\ConfirmerPanierType;
use App\Service\StrService;
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
class ModifierClientController extends AbstractController
{
    public function __construct(
        protected StrService $strService,
        protected EntityManagerInterface $em,
        protected TranslatorInterface $translator,
        protected FactureRepository $factureRepository,
    )
    {}

    #[Route('/modifier-client/{code}', name: 'modifier_client')]
    public function modifierClient(Request $request, string $code): Response
    {
        # je récupère ma session
        $maSession = $request->getSession();
        
        #mes variables témoin pour afficher les sweetAlert
        $maSession->set('ajout', null);
        $maSession->set('suppression', null);
        
        

        #je récupère la client à modifier
        $client = $this->factureRepository->findOneByCode([
            'code' => $code
        ]);

        #je crée mon formulaire et je le lie à mon instance
        $form = $this->createForm(ConfirmerPanierType::class, $client);

        #je demande à mon formulaire de récupérer les donnéesqui sont dans le POST avec la $request
        $form->handleRequest($request);

        #je teste si mon formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) 
        {
            #je met le nom de la client en CAPITAL LETTER
            $client->setNom($this->strService->strToUpper($client->getNom()));

            # je prépare ma requête avec entityManager
            $this->em->persist($client);

            #j'exécutebma requête
            $this->em->flush();

            #j'affiche le message de confirmation d'ajout
            $this->addFlash('info', $this->translator->trans('Client mise à jour avec succès !'));

            #j'affecte 1 à ma variable pour afficher le message
            $maSession->set('ajout', 1);
            
            

            #je retourne à la liste des categories
            return $this->redirectToRoute('afficher_client', [ 'm' => 1 ]);
        }

        # j'affiche mon formulaire avec twig
        return $this->render('client/ajouterClient.html.twig', [
            'licence' => 1,
            'code' => $code,
            'client' => $client,
            'formClient' => $form->createView(),
        ]);
    }
}
