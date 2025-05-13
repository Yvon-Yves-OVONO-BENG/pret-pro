<?php

namespace App\Service;

use Fpdf\Fpdf;
use App\Entity\ElementsPiedDePage\PaginationPortrait;
use App\Entity\Produit;
use App\Service\EntetePortraitPagination;

class ImpressionEnsembleService extends FPDF
{
    public function __construct( 
        protected EntetePortraitPagination $entetePortraitPagination,
        )
    {
    }

    /**
     * Fonction qui permet d'imprimer une ensemble
     * Undocumented function
     *
     * @param Produit $ensemble
     * @return PaginationPortrait
     */
    public function impressionEnsemble(Produit $ensemble): PaginationPortrait
    {
        $pdf = new PaginationPortrait();
        $pdf->addPage('P');

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

        // $pdf->Image('../public/images/qrcode/'.$ensemble->getQrCode(), 10, 40, 500);
        // $pdf->Image('../public/images/qrcode/'.$ensemble->getQrCode(), 165, 67, 34, 34);
        
        // $pdf->Ln(15);
        $pdf->SetX(15);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(100, 5, "DETAILS DE L'ENSEMBLE : ".$ensemble->getLibelle(), 0, 1, 'L', 0);

        $pdf->SetX(15);
        
        $positionY = 80;
        $pdf->Ln(5);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetX(15);
        
        $pdf->Cell(0, 10, utf8_decode("Eléménts de l'ensemble"), 0, 1, 'L', 0);

        $pdf->SetX(15);
        $pdf->SetFillColor(240,240,240);
        $pdf->Cell(10, 5, utf8_decode('N°'), 1, 0, 'C', true);
        $pdf->Cell(100, 5, utf8_decode('Produits'), 1, 0, 'C', true);
        $pdf->Cell(20, 5, utf8_decode('Prix'), 1, 0, 'C', true);
        $pdf->Cell(20, 5, utf8_decode('Qté'), 1, 0, 'C', true);
        $pdf->Cell(30, 5, utf8_decode('Total'), 1, 1, 'C', true);

        $i = 1;
        foreach ($ensemble->getProduitLigneDeEnsembles() as $ligneDeEnsemble) 
        {
            if ($i % 2 == 0) 
            {
                $pdf->SetFillColor(202,219,255);
            }else {
                $pdf->SetFillColor(255,255,255);
            }
            $pdf->SetX(15);
            $pdf->SetFont('Arial', '', 8);
            $pdf->Cell(10, 5, utf8_decode($i), 1, 0, 'C', true);
            $pdf->Cell(100, 5, utf8_decode($ligneDeEnsemble->getProduit()->getLibelle()), 1, 0, 'L', true);
            $pdf->Cell(20, 5, utf8_decode(number_format($ligneDeEnsemble->getPrix(), 0, '', ' ')), 1, 0, 'C', true);
            $pdf->Cell(20, 5, utf8_decode(number_format($ligneDeEnsemble->getQuantite(), 0, '', ' ')), 1, 0, 'C', true);
            $pdf->Cell(30, 5, utf8_decode(number_format(($ligneDeEnsemble->getQuantite() * $ligneDeEnsemble->getPrix()), 0, '', ' ')), 1, 1, 'C', true);

            $i++;
            
        }

        $pdf->SetX(15);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetFillColor(240,240,240);
        $pdf->Cell(150, 5, utf8_decode('PRIX TOTAL'), 0, 0, 'R');
        $pdf->Cell(30, 5, utf8_decode(number_format($ensemble->getPrixVente(), 0, '', ' ')." FCFA"), 1, 1, 'C', true);

        $pdf->SetX($pdf->GetX() + 15);
        $pdf->SetY($pdf->GetY() + 15);
        $pdf->SetFont('Arial', 'BU', 12);
        $pdf->Cell(142, 5, utf8_decode(''), 0, 0, 'R');

        return $pdf;
    }
}

