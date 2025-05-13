<?php

namespace App\Service;

use App\Entity\ConstantsClass;
use Fpdf\Fpdf;
use App\Entity\User;
use App\Entity\ElementsPiedDePage\PDF;
use App\Entity\ElementsPiedDePage\PaginationPaysage;
use App\Entity\Facture;
use DateTime;

class ImpressionFicheDeVenteService extends FPDF
{
    public function __construct(
        protected EntetePaysagePaginantion $entetePaysagePagination
        )
    {}

    /**
     * Fonction qui permet d'imprimer la fiche de vente d'une caissière
     *
     * @param integer $nombreCashDuJour
     * @param integer $montantCashDuJour
     * @param integer $nombrePrisEnChargeDuJour
     * @param integer $montantPrisEnChargeDuJour
     * @param integer $nombreCreditDuJour
     * @param integer $montantCreditDuJour
     * @param array $cashs
     * @param array $cheques
     * @param array $mobileMoneys
     * @param User $user
     * @return PDF
     */
    public function impressionFiheDeVente(array $historiquesPaiement,  User $caissiere = null, DateTime $dateDebut = null, DateTime $dateFin = null, bool $periode = null): PaginationPaysage
    {
        $pdf = new PaginationPaysage();
        $pdf->addPage('L');

        $pdf = $this->entetePaysagePagination->entetePaysagePagination($pdf);

        $pdf->SetLeftMargin(10);

        $positionY = 50;
        // $pdf->Ln(5);
        $pdf->SetXY(15, $positionY);
        
        $pdf->SetFont('Arial', 'B', 14);

        if ($caissiere) 
        {
            // $pdf->Ln(15);
            $pdf->SetX(15);
            // $pdf->Cell(100, 5, 'FICHE DE VENTE DE : '.$caissiere->getNom(), 0, 1, 'L', 0);
            $pdf->Cell(0, 5, 'FICHE DE VENTE DE : '.$caissiere->getNom(), 0, 1, 'C', 0);

            $pdf->SetX(15);
            $pdf->SetFont('Arial', 'B', 10);
            // $pdf->Cell(80.5, 5, 'Contact : '.$caissiere->getContact(), 0, 1, 'L', 0);
            // $pdf->Cell(80.5, 5, 'Contact : ', 0, 1, 'L', 0);
    
        } 
        else 
        {
            // $pdf->Ln(15);
            $pdf->SetX(15);
            $pdf->Cell(0, 5, 'FICHE DE VENTE', 0, 1, 'C', 0);

        }
        
        $pdf->SetX(15);
        $pdf->SetFont('Arial', 'B', 8);

        if ($periode) 
        {
            $pdf->Cell(0, 5, utf8_decode('Période allat du : ').date_format($dateDebut, 'd-m-Y').utf8_decode(' au ').date_format($dateFin, 'd-m-Y'), 0, 1, 'C', 0);
        } 
        else 
        {
            $pdf->Cell(0, 5, 'Date : '.date_format(new DateTime('now'), 'd-m-Y H:i:s'), 0, 1, 'C', 0);
        }
        
        ###################
        $pdf->Ln(5);

        $pdf->SetX(15);
        $pdf->SetFillColor(240,240,240);
        $pdf->SetFont('Arial', 'BI', 12);
        $pdf->Cell(10, 5, utf8_decode('NB : Les montants sont en FCFA'), 0, 1, 'l', false);

        $pdf->SetFont('Arial', 'B', 12);

        $pdf->SetX(15);
        $pdf->Cell(10, 5, utf8_decode('N°'), 1, 0, 'C', true);
        $pdf->Cell(40, 5, utf8_decode('FACTURES'), 1, 0, 'C', true);
        $pdf->Cell(70, 5, utf8_decode('CLIENT'), 1, 0, 'C', true);
        $pdf->Cell(40, 5, utf8_decode('NET A PAYER'), 1, 0, 'C', true);
        $pdf->Cell(30, 5, utf8_decode('RECU'), 1, 0, 'C', true);
        $pdf->Cell(30, 5, utf8_decode('RESTE'), 1, 0, 'C', true);
        $pdf->Cell(50, 5, utf8_decode('MODE DE PAIEMENT'), 1, 1, 'C', true);

        $pdf->SetX(15);
        $pdf->SetFont('Arial', '', 12);
        $pdf->SetFillColor(240,240,240);
        $i = 0;
        foreach ($historiquesPaiement as $historiquesPaiement) 
        {

            $i++;
            if ($i % 2 != 0) 
            {
                $pdf->SetFillColor(219,238,243);
            }
            else 
            {
                $pdf->SetFillColor(255,255,255);
            }
            $pdf->SetX(15);
            $pdf->Cell(10, 5, utf8_decode($i), 1, 0, 'C', true);
            $pdf->Cell(40, 5, utf8_decode($historiquesPaiement->getFacture()->getReference()), 1, 0, 'C', true);
            $pdf->Cell(70, 5, utf8_decode($historiquesPaiement->getFacture()->getNomClient()), 1, 0, 'C', true);
            $pdf->Cell(40, 5, utf8_decode(number_format($historiquesPaiement->getFacture()->getNetApayer(), 0, '', ' ')), 1, 0, 'C', true);
            $pdf->Cell(30, 5, utf8_decode(number_format($historiquesPaiement->getMontantAvance(), 0, '', ' ')), 1, 0, 'C', true);
            $pdf->Cell(30, 5, utf8_decode(number_format($historiquesPaiement->getFacture()->getNetApayer() - $historiquesPaiement->getFacture()->getAvance(), 0, '', ' ')), 1, 0, 'C', true);
            $pdf->Cell(50, 5, utf8_decode($historiquesPaiement->getFacture()->getModePaiement()->getModePaiement()), 1, 1, 'C', true);


        }

        
        

        $pdf->Ln(10);
        $pdf->SetX(15);
        
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(100, 5, utf8_decode('La Caissière'), 0, 0, 'C');
        $pdf->Cell(180, 5, utf8_decode("L'Administration"), 0, 0, 'C');

        return $pdf;
    }


    public function enteteTableau(PaginationPaysage $pdf): PaginationPaysage
    {
        $pdf->SetX(15);
        $pdf->SetFillColor(240,240,240);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(20, 5, utf8_decode('N°'), 1, 0, 'C', true);
        $pdf->Cell(40, 5, utf8_decode('Ref. Facture'), 1, 0, 'C', true);
        $pdf->Cell(70, 5, utf8_decode('Client'), 1, 0, 'C', true);
        $pdf->Cell(50, 5, utf8_decode('Date'), 1, 0, 'C', true);
        $pdf->Cell(30, 5, utf8_decode('Nat à payer'), 1, 0, 'C', true);
        $pdf->Cell(30, 5, utf8_decode('Avance'), 1, 0, 'C', true);
        $pdf->Cell(30, 5, utf8_decode('Reste'), 1, 1, 'C', true);

        return $pdf;
    }


    public function contenuTableau(PaginationPaysage $pdf, int $i, Facture $facture): PaginationPaysage
    {
        if ($i % 2 == 0) 
        {
            $pdf->SetFillColor(184,204,228);
        } 
        else 
        {
            $pdf->SetFillColor(255,255,255);
        }
                
        $pdf->SetX(15);
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(20, 5, utf8_decode($i), 1, 0, 'C', true);
        $pdf->Cell(40, 5, utf8_decode($facture->getReference()), 1, 0, 'C', true);
        $pdf->Cell(70, 5, utf8_decode($facture->getNomClient() ), 1, 0, 'C', true);
        $pdf->Cell(50, 5, utf8_decode(date_format($facture->getDateFactureAt(), 'd-m-Y').utf8_decode(' à ').date_format($facture->getHeure(), 'H:i:s')), 1, 0, 'C', true);
        $pdf->Cell(30, 5, utf8_decode(number_format($facture->getNetApayer(), 0, '', ' ')), 1, 0, 'C', true);
        $pdf->Cell(30, 5, utf8_decode(number_format($facture->getAvance(), 0, '', ' ')), 1, 0, 'C', true);
        $pdf->Cell(30, 5, utf8_decode(number_format(($facture->getNetApayer() - $facture->getAvance()), 0, '', ' ')), 1, 1, 'C', true);

        return $pdf;
    }


    public function basTableau(PaginationPaysage $pdf, int $montant, int $avance, int $reste): PaginationPaysage
    {
        $pdf->SetX(15);
        $pdf->SetFillColor(240,240,240);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(180, 5, utf8_decode('MONTANT'), 1, 0, 'C', true);
        $pdf->Cell(30, 5, utf8_decode(number_format($montant, 0, '', ' ')), 1, 0, 'C', true);
        $pdf->Cell(30, 5, utf8_decode(number_format($avance, 0, '', ' ')), 1, 0, 'C', true);
        $pdf->Cell(30, 5, utf8_decode(number_format($reste, 0, '', ' ')), 1, 1, 'C', true);

        return $pdf;
    }
}

