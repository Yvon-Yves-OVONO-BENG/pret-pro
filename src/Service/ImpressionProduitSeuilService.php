<?php

namespace App\Service;

use Fpdf\Fpdf;
use App\Entity\User;
use App\Service\EntetePaysage;
use App\Service\EntetePortrait;
use App\Entity\ElementsPiedDePage\PDF;
use App\Entity\ElementsPiedDePage\Pagination;
use DateTime;

class ImpressionProduitSeuilService extends FPDF
{
    public function __construct(
        protected EntetePaysage $entetePaysage, 
        protected EntetePortrait $entetePortrait,
        ){}

    /**
     * Undocumented function
     *
     * @param array $produits
     * @return Pagination
     */
    public function impressionProduitSeuil(array $produits): Pagination
    {
        $pdf = new Pagination();
        $pdf->addPage('P');

        // $pdf = $this->entetePortrait->entetePortrait($pdf);
        $pdf->Image('../public/assets/images/brand/logo.png', 30, 12, 25);
		$pdf->Image('../public/assets/images/brand/arrierePlan.png', 95, 190, 150);
		$pdf->SetFont('Helvetica', 'B', 11);
		// fond de couleur gris (valeurs en RGB)
		$pdf->setFillColor(229, 255, 255);
		// position du coin supérieur gauche par rapport à la marge gauche (mm)


		$pdf->SetX(15);
		$pdf->Cell(70, 4, utf8_decode(""), 0, 0, 'C', 0);
		$pdf->Cell(40, 4, '', 0, 0, 'L', 0);
		$pdf->Cell(70, 4, utf8_decode(''), 0, 1, 'C', 0);

		$pdf->SetX(15);
		$pdf->SetFont('Helvetica', 'B', 20);
		$pdf->Cell(70, 2, '', 0, 0, 'C', 0);
		$pdf->Cell(40, 2, '', 0, 0, 'L', 0);
		$pdf->Cell(70, 15, utf8_decode('PRET-PRO'), 0, 1, 'C', 1);
        
		$pdf->SetFont('Helvetica', 'B', 8);
		$pdf->SetX(15);
		$pdf->Cell(70, 4, utf8_decode(''), 0, 0, 'C', 0);
		$pdf->Cell(40, 4, '', 0, 0, 'L', 0);
		$pdf->Cell(70, 4, utf8_decode("Vous habiller, c'est notre devoir"), 0, 1, 'C', 0);

		$pdf->SetX(15);
		$pdf->SetFont('Helvetica', 'B', 8);
		$pdf->Cell(70, 2, '', 0, 0, 'C', 0);
		$pdf->Cell(40, 2, '', 0, 0, 'L', 0);
		$pdf->Cell(70, 2, '******', 0, 1, 'C', 0);

        $pdf->SetLeftMargin(10);

        $positionY = 50;
        $pdf->SetXY(15, $positionY);

        // $pdf->Image('../public/images/qrcode/'.$facture->getQrCode(), 10, 40, 500);
        // $pdf->Image('../public/images/qrcode/'.$facture->getQrCode(), 165, 67, 34, 34);
        
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 5, 'ETAT DES PRODUITS SEUILS', 0, 1, 'C', 0);
        $pdf->SetFont('Arial', 'BI', 7);
        $pdf->Cell(0, 5, 'Date : '. date_format(new DateTime(), 'd-m-Y H:i:s'), 0, 1, 'C', 0);
        

        $pdf->SetFont('Arial', 'BI', 9);

        $pdf->SetX(15);
        $pdf->SetFont('Arial', 'B', 8);

        $pdf->Ln(5);
        $pdf->SetX(15);

        // Entête du tableau
        $pdf = $this->enteteTableau($pdf);

        $pdf->SetFont('Arial', '', 7);

        $i = 0 ;
        $montant = 0;
        foreach ($produits as $produit) 
        {   $i++;
            if ($i % 2 == 0) 
            {
                $pdf->SetFillColor(184,204,228);
            } 
            else 
            {
                $pdf->SetFillColor(255,255,255);
            }
            
            $pdf->SetX(10);
            $pdf->Cell(10, 5, utf8_decode($i), 1, 0, 'C', true);
            $pdf->Cell(115, 5, utf8_decode($produit->getLibelle()), 1, 0, 'L', true);
            $pdf->Cell(40, 5, utf8_decode(number_format($produit->getQuantiteSeuil(), 0, '', ' ')), 1, 0, 'C', true);
            $pdf->Cell(20, 5, utf8_decode(number_format(($produit->getLot()->getQuantite() - $produit->getLot()->getVendu()), 0, '', ' ')), 1, 1, 'C', true);
            
        }

        $pdf = $this->basTableau($pdf, $i);
        
        return $pdf;
    }

    public function enteteTableau(Pagination $pdf): Pagination
    {
        $pdf->SetX(10);
        $pdf->SetFillColor(240,240,240);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(10, 5, utf8_decode('N°'), 1, 0, 'C', true);
        $pdf->Cell(115, 5, utf8_decode('Désignation'), 1, 0, 'C', true);
        $pdf->Cell(40, 5, utf8_decode('Quantité seuille'), 1, 0, 'C', true);
        $pdf->Cell(20, 5, utf8_decode('Reste'), 1, 1, 'C', true);

        return $pdf;
    }

    public function basTableau(Pagination $pdf, int $i): Pagination
    {
        $pdf->SetX(10);
        $pdf->SetFillColor(240,240,240);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(125, 5, utf8_decode('TOTAL'), 1, 0, 'C', true);
        $pdf->Cell(60, 5, utf8_decode($i." produits"), 1, 1, 'C', true);

        return $pdf;
    }
}

