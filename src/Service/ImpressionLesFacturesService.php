<?php

namespace App\Service;

use Fpdf\Fpdf;
use App\Service\EntetePaysage;
use App\Service\EntetePortrait;
use App\Entity\ElementsPiedDePage\PDF;


class ImpressionLesFacturesService extends FPDF
{
    public function __construct(
        protected EntetePaysage $entetePaysage, 
        protected EntetePortrait $entetePortrait,
        )
    {}

    public function impressionLesFactures($factures): PDF
    {
        $pdf = new PDF();

        if (count($factures) != 0) 
        {
            foreach ($factures as $facture) 
            {
                $pdf->addPage('P');

                $pdf = $this->entetePortrait->entetePortrait($pdf);

                $pdf->SetLeftMargin(10);

                $positionY = 50;
                $pdf->SetXY(15, $positionY);

                $pdf->Image('images/qrCode/'.$facture->getQrCode(), 150, 70, 25, 25);
        
        $pdf->Ln(-8);
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->SetX(15);
        $pdf->Cell(65, 5, 'PRET-PRO', 0, 0, 'C', 0);
        $pdf->Cell(50, 5, '', 0, 0, 'C', 0);
        $pdf->Cell(65, 5, 'CLIENT', 0, 1, 'C', 0);
        
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell(65, 5, utf8_decode('Yaoundé, Mfoundi, Centre, Cameroun'), 0, 0, 'C', 0);
        $pdf->Cell(50, 5, '', 0, 0, 'C', 0);
        $pdf->Cell(65, 5, utf8_decode($facture->getNomClient()), 0, 1, 'C', 0);

        $pdf->Cell(65, 5, utf8_decode('Tel : (+237) 697 993 386 / 673 788 308'), 0, 0, 'C', 0);
        $pdf->Cell(50, 5, '', 0, 0, 'C', 0);
        $pdf->Cell(65, 5, utf8_decode($facture->getContactClient()), 0, 1, 'C', 0);

        $pdf->Cell(65, 5, utf8_decode('Email : pretpro@gmail.com'), 0, 0, 'C', 0);
        $pdf->Cell(50, 5, '', 0, 0, 'C', 0);
        $pdf->Cell(65, 5, utf8_decode($facture->getEmailClient()), 0, 1, 'C', 0);

        $pdf->Cell(65, 5, utf8_decode(''), 0, 0, 'C', 0);
        $pdf->Cell(50, 5, '', 0, 0, 'C', 0);
        $pdf->Cell(65, 5, utf8_decode("Adresse : ".$facture->getAdresseClient()), 0, 1, 'C', 0);


        $pdf->SetFont('Arial', '', 10);
        $pdf->SetX(15);
        $pdf->Cell(100, 5, utf8_decode('Référence : '.$facture->getReference()), 0, 1, 'L', 1);
        
        $pdf->SetX(15);
        $pdf->Cell(100, 5, 'Date de la facture : '.date_format($facture->getDateFactureAt(), 'd/m/Y').utf8_decode(' à ').date_format($facture->getHeure(), 'H:i:s'), 0, 1, 'L', 1);
        
        $pdf->SetX(15);
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(10, 5, 'PAR : ', 0, 0, 'L', 1);
        $pdf->Cell(90, 5, $facture->getCaissiere() ? utf8_decode($facture->getCaissiere()->getNom()) : "CAISSIERE", 0, 1, 'L', 1);

        $pdf->SetFont('Arial', '', 10);
        $pdf->SetX(15);
        $pdf->Cell(30, 5, utf8_decode("Etat de la facture : "), 0, 0, 'L', 1);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(70, 5, utf8_decode($facture->getEtatFacture() ? $facture->getEtatFacture()->getEtatFacture() : ""), 0, 1, 'L', 1);

        $pdf->SetX(15);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(45, 5, utf8_decode("Mode de paiement choisi : "), 0, 0, 'L', 1);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(55, 5, utf8_decode($facture->getModePaiement() ? $facture->getModePaiement()->getModePaiement() : ""), 0, 1, 'L', 1);

        $pdf->SetX(15);
        $pdf->Cell(75, 5, utf8_decode("Cette facture s'élève à un montant de : "), 0, 0, 'L', 1);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(25, 5, utf8_decode(number_format($facture->getNetApayer(), 0, '', ' ')." FCFA"), 0, 1, 'L', 1);
        $pdf->SetX(15);
        $pdf->Cell(100, 8, utf8_decode((new ChiffreEnLettreService($facture->getNetApayer(), 'Francs CFA'))->convert('fr-FR')), 0, 1, 'C',1);

        $positionY = 80;
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetX(15);
                
                $pdf->Cell(0, 10, utf8_decode('Eléménts de la facture'), 0, 1, 'L', 0);

                $pdf->SetX(15);
                $pdf->SetFillColor(240,240,240);
                $pdf->Cell(7, 5, utf8_decode('N°'), 1, 0, 'C', true);
                $pdf->Cell(90, 5, utf8_decode('Produits'), 1, 0, 'C', true);
                $pdf->Cell(25, 5, utf8_decode('Pu (FCFA)'), 1, 0, 'C', true);
                $pdf->Cell(20, 5, utf8_decode('Qté'), 1, 0, 'C', true);
                $pdf->Cell(40, 5, utf8_decode('Total (FCFA)'), 1, 1, 'C', true);


                $i = 1;
                foreach ($facture->getLigneDeFactures() as $detailsFactur) 
                {
                    if ($i % 2 == 0) 
                    {
                        $pdf->SetFillColor(202,219,255);
                    }else {
                        $pdf->SetFillColor(255,255,255);
                    }
                    $pdf->SetX(15);
                    $pdf->SetFont('Arial', '', 8);
                    $pdf->Cell(7, 5, utf8_decode($i), 1, 0, 'C', true);
                    $pdf->Cell(90, 5, utf8_decode($detailsFactur->getProduit()->getLibelle() ? $detailsFactur->getProduit()->getLibelle() : $detailsFactur->getProduit()->getLibelle()), 1, 0, 'L', true);
                    $pdf->Cell(25, 5, utf8_decode(number_format($detailsFactur->getPrix(), 0, '', ' ')), 1, 0, 'C', true);
                    $pdf->Cell(20, 5, utf8_decode(number_format($detailsFactur->getQuantite(), 0, '', ' ')), 1, 0, 'C', true);
                    $pdf->Cell(40, 5, utf8_decode(number_format($detailsFactur->getPrixQuantite(), 0, '', ' ')), 1, 1, 'C', true);

                    $i++;
                    
                }

                $pdf->SetX(15);
                $pdf->SetFont('Arial', 'B', 10);
                $pdf->Cell(142, 5, utf8_decode('Montant HT'), 0, 0, 'R');
                $pdf->Cell(40, 5, utf8_decode(number_format($facture->getNetApayer(), 0, '', ' ')), 1, 1, 'C');

                $pdf->SetX(15);
                $pdf->Cell(142, 5, utf8_decode('TVA'), 0, 0, 'R');
                $pdf->Cell(40, 5, utf8_decode("0%"), 1, 1, 'C');

                $pdf->SetX(15);
                $pdf->Cell(142, 5, utf8_decode('Montant TTC'), 0, 0, 'R');
                $pdf->Cell(40, 5, utf8_decode(number_format($facture->getNetApayer(), 0, '', ' ')), 1, 1, 'C');

                $pdf->SetFont('Arial', 'B', 12);
                $pdf->SetX(15);
                $pdf->SetFillColor(202, 219, 255);
                $pdf->Cell(142, 5, utf8_decode('NET A PAYER'), 0, 0, 'R');
                $pdf->Cell(40, 5, utf8_decode(number_format($facture->getNetApayer(), 0, '', ' ')." FCFA"), 1, 1, 'C', true);

                
                $pdf->SetX(15);
                $pdf->SetFillColor(202, 219, 255);
                $pdf->Cell(142, 5, utf8_decode('AVANCE'), 0, 0, 'R');
                $pdf->Cell(40, 5, utf8_decode(number_format($facture->getAvance(), 0, '', ' ')." FCFA"), 1, 1, 'C', true);

                $pdf->SetX(15);
                $pdf->SetFillColor(202, 219, 255);
                $pdf->Cell(142, 5, utf8_decode('RESTE'), 0, 0, 'R');
                $pdf->Cell(40, 5, utf8_decode(number_format(($facture->getNetApayer() - $facture->getAvance()), 0, '', ' ')." FCFA"), 1, 1, 'C', true);

                

                $pdf->SetX($pdf->GetX() + 15);
                $pdf->SetY($pdf->GetY() + 15);
                $pdf->SetFont('Arial', 'BU', 12);
                $pdf->Cell(142, 5, utf8_decode('LA CAISSIERE'), 0, 0, 'R');
            }
            
        } 
        else 
        {
            $pdf->addPage('P');

            $pdf = $this->entetePortrait->entetePortrait($pdf);
            $pdf->Ln(50);
            $pdf->SetX(15);
            $pdf->SetFont('Arial', 'B', 15);
            $pdf->Cell(0, 5, "PAS DE FACTURES", 0, 0, 'C', 0);
           
        }
        
        
        return $pdf;
    }
}