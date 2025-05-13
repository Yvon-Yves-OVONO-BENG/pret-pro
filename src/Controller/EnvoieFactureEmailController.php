<?php

namespace App\Controller;

use DateTime;
use DateTimeImmutable;
use App\Entity\Verification;
use App\Service\EmailService;
use App\Service\ImpressionFactureService;
use App\Repository\UserRepository;
use Symfony\Component\Mime\Address;
use App\Repository\FactureRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\LigneDeFactureRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @IsGranted("ROLE_USER", message="Accès refusé. Espace reservé uniquement aux abonnés")
 *
 */
class EnvoieFactureEmailController extends AbstractController
{
    public function __construct(
        protected UserRepository $userRepository,
        protected TranslatorInterface $translator,
        protected FactureRepository $factureRepository,
        protected UserPasswordHasherInterface $hasher,
        protected EntityManagerInterface $entityManagerInterface,
        protected LigneDeFactureRepository $ligneDeFactureRepository
    ) {
    }

    #[Route('/envoi-facture/{slug}', name: "envoi_facture")]
    public function envoiEmail(
        Request $request, 
        MailerInterface $mailer, 
        ImpressionFactureService $impressionFactureService, TransportInterface $transport, 
        string $slug
    ): Response 
    {
        /**
         * @var User
         */
        $user = $this->getUser();
        $session = $request->getSession();
        $session->set('user', $user);

        // Récupération de la facture
        $facture = $this->factureRepository->findOneBy(['slug' => $slug]);

        $detailsFacture = $this->ligneDeFactureRepository->findBy([
            'facture' => $facture
        ]);

        if (!$facture) {
            $this->addFlash('danger', $this->translator->trans("Facture introuvable !"));
            return $this->redirectToRoute("liste_facture");
        }

        $session->set('facture', $facture);

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
            $impressionFactureService->impressionFacture($facture, $detailsFacture, $envoie, $filePath);
        } 
        catch (\Exception $e) 
        {
            $this->addFlash('danger', $this->translator->trans("Erreur lors de la génération de la facture PDF !"));
            return $this->redirectToRoute("liste_facture");
        }

        // Envoi de l'email avec pièce jointe
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

        try {
            $transport->send($email);
            $mailer->send($email);
            $this->addFlash('info', $this->translator->trans("Facture envoyée avec succès !"));
        } 
        catch (TransportExceptionInterface $e)
        {
            $this->addFlash('danger', $this->translator->trans("Erreur lors de l'envoi de l'email !"));
            return $this->redirectToRoute("liste_facture");
        }

        // Retourner à la liste des factures
        return $this->redirectToRoute("liste_facture");
    }
}
