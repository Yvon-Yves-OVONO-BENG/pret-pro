<?php

namespace App\Service;

use Fpdf\Fpdf;
use App\Entity\ElementsPiedDePage\PaginationPaysage;
use DateTime;

class ImpressionFicheEntreeService extends FPDF
{
    public function __construct(
        protected EntetePaysagePaginantion $entetePaysagePagination, 
        )
    {
    }

    /**
     * Undocumented function
     *
     * @param array $produits
     * @return PaginationPaysage
     */
    public function impressionFicheEntree(array $produits, DateTime $dateDebut = null, DateTime $dateFin = null, bool $periode = null): PaginationPaysage
    {
        $pdf = new PaginationPaysage();
        $pdf->addPage('L');

        $pdf = $this->entetePaysagePagination->entetePaysagePagination($pdf);

        $pdf->SetLeftMargin(10);

        $positionY = 50;
        $pdf->SetXY(15, $positionY);

        // $pdf->Image('../public/images/qrcode/'.$facture->getQrCode(), 10, 40, 500);
        // $pdf->Image('../public/images/qrcode/'.$facture->getQrCode(), 165, 67, 34, 34);
        
        $pdf->Ln(8);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 5, "FICHE D'ENTREE DES PRODUITS", 0, 1, 'C', 0);
        $pdf->SetFont('Arial', 'BI', 7);
        $pdf->Cell(0, 5, 'Les prix sont en fcfa', 0, 1, 'C', 0);

        $pdf->SetFont('Arial', 'BI', 9);
        if ($periode) 
        {
            $pdf->Cell(0, 5, 'PERIODE : du '.date_format($dateDebut, 'd-m-Y').' au '.date_format($dateFin, 'd-m-Y'), 0, 1, 'C', 0);
        }

        $pdf->SetX(15);
        $pdf->SetFont('Arial', 'B', 8);

        $pdf->Ln(5);
        $pdf->SetX(15);

        // Entête du tableau
        $pdf = $this->enteteTableau($pdf);

        $pdf->SetFont('Arial', '', 7);

        $i = 1 ;
        $venduPv = 0;
        $venduPa = 0;
        $gain = 0 ;

        foreach ($produits as $produit) 
        {   
            if ($i % 2 == 0) 
            {
                $pdf->SetFillColor(184,204,228);
            } 
            else 
            {
                $pdf->SetFillColor(255,255,255);
            }
            
            $pdf->SetX(25);
            $pdf->Cell(10, 5, utf8_decode($i), 1, 0, 'C', true);
            $pdf->Cell(50, 5, utf8_decode($produit->getLibelle()), 1, 0, 'L', true);
            $pdf->Cell(20, 5, utf8_decode(number_format($produit->getLot()->getQuantite(), 0, '', ' ')), 1, 0, 'C', true);
            $pdf->Cell(15, 5, utf8_decode(number_format($produit->getLot()->getPrixAchat(), 0, '', ' ')), 1, 0, 'C', true);
            $pdf->Cell(15, 5, utf8_decode(number_format($produit->getLot()->getPrixVente(), 0, '', ' ')), 1, 0, 'C', true);
            $pdf->Cell(30, 5, utf8_decode(number_format(($produit->getLot()->getPrixVente() - $produit->getLot()->getPrixAchat()), 0, '', ' ')), 1, 0, 'C', true);
            $pdf->Cell(15, 5, utf8_decode(number_format($produit->getLot()->getVendu(), 0, '', ' ')), 1, 0, 'C', true);
            $pdf->Cell(15, 5, utf8_decode(number_format(($produit->getLot()->getQuantite()-$produit->getLot()->getVendu()), 0, '', ' ')), 1, 0, 'C', true);
            $pdf->Cell(25, 5, utf8_decode(number_format(($produit->getLot()->getVendu() * $produit->getLot()->getPrixVente()), 0, '', ' ')), 1, 0, 'C', true);
            $pdf->Cell(25, 5, utf8_decode(number_format(($produit->getLot()->getVendu() * $produit->getLot()->getPrixAchat()), 0, '', ' ')), 1, 0, 'C', true);
            $pdf->Cell(25, 5, utf8_decode(number_format(($produit->getLot()->getVendu() * $produit->getLot()->getPrixVente()) - ($produit->getLot()->getVendu() * $produit->getLot()->getPrixAchat()), 0, '', ' ')), 1, 1, 'C', true);

            $venduPv += ($produit->getLot()->getVendu() * $produit->getLot()->getPrixVente());
            $venduPa += ($produit->getLot()->getVendu() * $produit->getLot()->getPrixAchat());
            $gain += (($produit->getLot()->getVendu() * $produit->getLot()->getPrixVente()) - ($produit->getLot()->getVendu() * $produit->getLot()->getPrixAchat()));

            $i++;
        }

        $pdf = $this->basTableau($pdf, $venduPv, $venduPa, $gain);
        
        $pdf->Ln(10);
        $pdf->SetX(15);
        
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(100, 5, utf8_decode('Le/La Responsable'), 0, 0, 'C');
        $pdf->Cell(180, 5, utf8_decode("L'Administration"), 0, 0, 'C');

        return $pdf;
    }


    public function enteteTableau(PaginationPaysage $pdf): PaginationPaysage
    {
        $pdf->SetX(25);
        $pdf->SetFillColor(240,240,240);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(10, 5, utf8_decode('N°'), 1, 0, 'C', true);
        $pdf->Cell(50, 5, utf8_decode('Désignation'), 1, 0, 'C', true);
        $pdf->Cell(20, 5, utf8_decode('Quantité'), 1, 0, 'C', true);
        $pdf->Cell(15, 5, utf8_decode("P.A."), 1, 0, 'C', true);
        $pdf->Cell(15, 5, utf8_decode('P.V.'), 1, 0, 'C', true);
        $pdf->Cell(30, 5, utf8_decode('Marge Bénef.'), 1, 0, 'C', true);
        $pdf->Cell(15, 5, utf8_decode('Vendu'), 1, 0, 'C', true);
        $pdf->Cell(15, 5, utf8_decode('Reste'), 1, 0, 'C', true);
        $pdf->Cell(25, 5, utf8_decode('Vendu x P.V.'), 1, 0, 'C', true);
        $pdf->Cell(25, 5, utf8_decode('Vendu x P.A.'), 1, 0, 'C', true);
        $pdf->Cell(25, 5, utf8_decode('Gain'), 1, 1, 'C', true);

        return $pdf;
    }

    public function basTableau(PaginationPaysage $pdf, int $venduPv, int $venduPa, int $gain): PaginationPaysage
    {
        $pdf->SetX(25);
        $pdf->SetFillColor(240,240,240);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(170, 5, utf8_decode('TOTAL'), 1, 0, 'C', true);
        $pdf->Cell(25, 5, utf8_decode(number_format($venduPv, 0, '', ' ')), 1, 0, 'C', true);
        $pdf->Cell(25, 5, utf8_decode(number_format($venduPa, 0, '', ' ')), 1, 0, 'C', true);
        $pdf->Cell(25, 5, utf8_decode(number_format($gain, 0, '', ' ')), 1, 1, 'C', true);

        return $pdf;
    }
}

