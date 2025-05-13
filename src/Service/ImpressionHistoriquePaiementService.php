<?php

namespace App\Service;

use Fpdf\Fpdf;
use App\Service\EntetePaysage;
use App\Service\EntetePortrait;
use App\Entity\ElementsPiedDePage\PDF;



class ImpressionHistoriquePaiementService extends FPDF
{
    public function __construct(
        protected EntetePaysage $entetePaysage, 
        protected EntetePortraitFacture $entetePortraitFacture,
        )
    {
    }

    public function impressionHistoriquePaiement($facture, $historiqueFacture): PDF
    {
        $pdf = new PDF();
        $pdf->addPage('P');

        $pdf = $this->entetePortraitFacture->entetePortraitFacture($pdf);

        $pdf->SetLeftMargin(10);

        $positionY = 50;
        $pdf->SetXY(15, $positionY);

        // $pdf->Image('../public/images/qrcode/'.$facture->getQrCode(), 10, 40, 500);
        // $pdf->Image('../public/images/qrcode/'.$facture->getQrCode(), 165, 67, 34, 34);
        
        $pdf->Ln(-8);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetX(15);
        $pdf->Cell(100, 5, 'HISTORIQUE DE PAIEMENT DE LA FACTURE : '.$facture->getReference(), 0, 1, 'L', 0);

        
        $pdf->SetFont('Arial', 'I', 10);
        $pdf->SetX(15);
        $pdf->Cell(50, 5, 'INVOICE PAYMENT HISTORY', 0, 0, 'L', 0);

        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(30, 5, 'PAR : ', 0, 0, 'R', 0);
        $pdf->Cell(50, 5, $facture->getCaissiere() ? utf8_decode($facture->getCaissiere()->getNom()) : "CAISSIERE", 0, 1, 'L', 0);

        
        $pdf->Ln(5);
        $pdf->SetX(15);
        $pdf->SetFont('Arial', '', 10);

        if ($facture->getClient()) 
        {
            $pdf->SetX(15);
            $pdf->Cell(0, 5, utf8_decode("Nom du client :".$facture->getClient()->getNom()), 0, 1, 'L', 0);
            
            $pdf->SetX(15);
            $pdf->Cell(0, 5, utf8_decode("Date de naissance : ".date_format($facture->getClient()->getDateNaissanceAt(), 'd-m-Y')), 0, 1, 'L', 0);

            $pdf->SetX(15);
            $pdf->Cell(0, 5, utf8_decode("Ville : ".$facture->getClient()->getVilleResidence()), 0, 1, 'L', 0);

            $pdf->SetX(15);
            $pdf->Cell(0, 5, utf8_decode("Pays : ".$facture->getClient()->getPays()->getPays()), 0, 1, 'L', 0);

            $pdf->SetX(15);
            $pdf->Cell(0, 5, utf8_decode("Téléphone : ".$facture->getClient()->getTelephone()), 0, 1, 'L', 0);
        } 
        else 
        {
            $pdf->SetX(15);
            $pdf->Cell(0, 5, utf8_decode("Nom du client :  ".$facture->getNomClient()), 0, 1, 'L', 0);

            $pdf->SetX(15);
            $pdf->SetFont('Arial', '', 10);
            $pdf->Cell(20, 5, utf8_decode("Téléphone : "), 0, 0, 'L', 0);
            $pdf->Cell(100, 5, utf8_decode($facture->getContactClient() ? $facture->getContactClient() : ""), 0, 1, 'L', 0);

        }
        
        
        
        $pdf->SetFont('Arial', '', 10);
        $pdf->SetX(15);
        $pdf->Cell(40, 5, utf8_decode("Etat de la facture : "), 0, 0, 'L', 0);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(0, 5, utf8_decode($facture->getEtatFacture() ? $facture->getEtatFacture()->getEtatFacture() : ""), 0, 1, 'L', 0);

        $pdf->SetX(15);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(45, 5, utf8_decode("Mode de paiement choisi : "), 0, 0, 'L', 0);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(0, 5, utf8_decode($facture->getModePaiement() ? $facture->getModePaiement()->getModePaiement() : ""), 0, 1, 'L', 0);

        $pdf->SetX(15);
        $pdf->Cell(75, 5, utf8_decode("Cette facture s'élève à un montant de : "), 0, 0, 'L', 0);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(0, 5, utf8_decode(number_format($facture->getNetApayer(), 0, '', ' ')." FCFA"), 0, 1, 'L', 0);

        $positionY = 80;
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetX(15);
        
        $pdf->Cell(0, 10, utf8_decode('Historique'), 0, 1, 'L', 0);

        $pdf->SetX(15);
        $pdf->SetFillColor(240,240,240);
        $pdf->Cell(7, 5, utf8_decode('N°'), 1, 0, 'C', true);
        $pdf->Cell(40, 5, utf8_decode('Montant'), 1, 0, 'C', true);
        $pdf->Cell(95, 5, utf8_decode('Reçu par'), 1, 0, 'C', true);
        $pdf->Cell(40, 5, utf8_decode('Date'), 1, 1, 'C', true);

        
        $i = 1;
        foreach ($historiqueFacture as $historiqueFactur) 
        {
            if ($i % 2 == 0) 
            {
                $pdf->SetFillColor(202,219,255);
            }else {
                $pdf->SetFillColor(255,255,255);
            }
            $pdf->SetX(15);
            $pdf->SetFont('Arial', '', 8);
                
            $pdf->SetFont('Arial', '', 8);
            $pdf->Cell(7, 5, utf8_decode($i), 1, 0, 'C', true);
            $pdf->Cell(40, 5, utf8_decode(number_format($historiqueFactur->getMontantAvance(), 0, '', ' ')), 1, 0, 'C', true);
            $pdf->Cell(95, 5, utf8_decode($historiqueFactur->getRecuPar()->getNom()), 1, 0, 'C', true);
            $pdf->Cell(40, 5, utf8_decode(date_format($historiqueFactur->getDateAvanceAt(), 'd-m-Y H:i:s')), 1, 1, 'C', true);
        
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

        $pdf->Ln(-5);
        $pdf->SetX($pdf->GetX() + 15);
        $pdf->SetY($pdf->GetY() + 15);
        $pdf->SetFont('Arial', 'BU', 12);
        $pdf->Cell(142, 5, utf8_decode('LA CAISSIERE'), 0, 0, 'R');

        
        
        $pdf->AliasNbPages();
        return $pdf;
    }
}

