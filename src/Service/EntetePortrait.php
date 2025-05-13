<?php

namespace App\Service;

use Fpdf\Fpdf;

class EntetePortrait
{
	public function __construct()
	{
	}

	public function entetePortrait(Fpdf $pdf): Fpdf
	{
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
		$pdf->Cell(70, 15, utf8_decode('FACTURE'), 0, 1, 'C', 1);
        
		$pdf->SetFont('Helvetica', 'B', 8);
		$pdf->SetX(15);
		$pdf->Cell(70, 4, utf8_decode(''), 0, 0, 'C', 0);
		$pdf->Cell(40, 4, '', 0, 0, 'L', 0);
		$pdf->Cell(70, 4, utf8_decode(''), 0, 1, 'C', 0);

		$pdf->SetX(15);
		$pdf->SetFont('Helvetica', 'B', 8);
		$pdf->Cell(70, 2, '', 0, 0, 'C', 0);
		$pdf->Cell(40, 2, '', 0, 0, 'L', 0);
		$pdf->Cell(70, 2, '', 0, 1, 'C', 0);

		return $pdf;
	}
}
