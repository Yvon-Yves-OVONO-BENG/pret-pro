<?php

namespace App\Service;

use App\Entity\ElementsPiedDePage\PaginationPaysage;

class EntetePaysagePaginantion
{
	public function __construct()
	{
	}

	public function entetePaysagePagination(PaginationPaysage $pdf): PaginationPaysage
	{
		$pdf->Image('../public/assets/images/brand/logo.png', 130, 12, 25);
		$pdf->Image('../public/assets/images/brand/arrierePlan.png', 190, 90, 150);

		$pdf->SetFont('Helvetica', 'B', 14);
		// fond de couleur gris (valeurs en RGB)
		$pdf->setFillColor(230, 230, 230);
		// position du coin supérieur gauche par rapport à la marge gauche (mm)
		
		$pdf->SetX(20);
		$pdf->Cell(70, 4, utf8_decode("PRET-PRO"), 0, 0, 'C', 0);
		$pdf->Cell(120, 4, '', 0, 0, 'L', 0);
		$pdf->Cell(70, 4, utf8_decode("PRET-PRO"), 0, 1, 'C', 0);

		$pdf->SetFont('Helvetica', 'B', 10);
		$pdf->SetX(20);
		$pdf->Cell(70, 4, utf8_decode("Vous habiller, c'est notre devoir"), 0, 0, 'C', 0);
		$pdf->Cell(120, 4, '', 0, 0, 'L', 0);
		$pdf->Cell(70, 4, utf8_decode("Dressing you is our duty"), 0, 1, 'C', 0);

		$pdf->SetX(20);
		$pdf->SetFont('Helvetica', 'B', 10);
		$pdf->Cell(70, 2, '*********', 0, 0, 'C', 0);
		$pdf->Cell(119, 2, '', 0, 0, 'L', 0);
		$pdf->Cell(70, 2, '*********', 0, 1, 'C', 0);

		################################
		$pdf->SetX(20);
		$pdf->Cell(70, 4, utf8_decode('BP : 14525 - Yaoundé, Tel : +237 697 993 386'), 0, 0, 'C', 0);
		$pdf->Cell(120, 4, '', 0, 0, 'L', 0);
		$pdf->Cell(70, 4, utf8_decode('Po.Box : 14525 - Yaoundé, Tel : +237 697 993 386'), 0, 1, 'C', 0);

		$pdf->SetX(20);
		$pdf->SetFont('Helvetica', 'B', 8);
		$pdf->Cell(70, 2, '*********', 0, 0, 'C', 0);
		$pdf->Cell(119, 2, '', 0, 0, 'L', 0);
		$pdf->Cell(70, 2, '*********', 0, 1, 'C', 0);

		return $pdf;
	}
}
