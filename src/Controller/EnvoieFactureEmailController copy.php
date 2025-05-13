<?php

namespace App\Controller;

use DateTime;
use DateTimeImmutable;
use App\Entity\Verification;
use App\Repository\UserRepository;
use Symfony\Component\Mime\Address;
use App\Repository\FactureRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Service\EmailService;

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
    ) {
    }

    #[Route('/envoi-facture/{slug}', "envoi_facture")]
    public function envoiEmail(Request $request, MailerInterface $mailer, TransportInterface $transport, EmailService $emailService, string $slug): Response
    {
        /**
         *@var User
         */
        $user = $this->getUser();
        
        $session = $request->getSession();
            
        $session->set('user',$user);

        #je récupère la facture
        $facture = $this->factureRepository->findOneBySlug(['slug' => $slug]);
        
        $session->set('facture',$facture);
        //envoi du mail
        $email = new TemplatedEmail;
        $email->from(new Address('pretpro@freedomsoftwarepro.com ', "Prêt-Pro Service"))
            ->sender(new Address('pretpro@freedomsoftwarepro.com  ', "Prêt-Pro Service"))
            ->to($facture->getEmailClient())
            ->subject("Votre facture")
            ->htmlTemplate("emails/envoieFacture.html.twig", [
            ])
            ->date(new DateTime)
            ;
            
            
        try 
        {
            //j'envoie le mail
            $transport->send($email);
            $mailer->send($email);
          
            //je retourne à la liste des factures
            $this->addFlash('info',  $this->translator->trans("Facture envoyée avec succès !"));
            return $this->redirectToRoute('liste_facture');
        } 
        catch (\Throwable) 
        {
           $this->addFlash('info',  $this->translator->trans("Erreur lors de l'envoi de la facture !"));
           return $this->redirectToRoute('accueil');
        }
        
        return $this->redirectToRoute("liste_facture");


    }
}
