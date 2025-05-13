<?php

namespace App\Service;

use App\Entity\ElementsPiedDePage\PaginationPortrait;

class EntetePortraitPagination
{
	public function __construct()
	{
	}

	public function entetePortraitPagination(PaginationPortrait $pdf): PaginationPortrait
	{
		$pdf->Image('../public/images/logo/logo.png', 95, 12, 25);
		$pdf->Image('../public/images/logo/arrierePlan.PNG', 95, 190, 150);
		$pdf->SetFont('Helvetica', 'B', 11);
		// fond de couleur gris (valeurs en RGB)
		$pdf->setFillColor(230, 230, 230);
		// position du coin supérieur gauche par rapport à la marge gauche (mm)
		$pdf->SetX(15);
		$pdf->Cell(70, 4, utf8_decode("REPUBLIQUE DU CAMEROUN"), 0, 0, 'C', 0);
		$pdf->Cell(40, 4, '', 0, 0, 'L', 0);
		$pdf->Cell(70, 4, utf8_decode("REPUBLIC OF CAMEROON"), 0, 1, 'C', 0);

		$pdf->SetFont('Helvetica', 'B', 7);
		$pdf->SetX(15);
		$pdf->Cell(70, 4, utf8_decode("Paix - Travail - Patrie"), 0, 0, 'C', 0);
		$pdf->Cell(40, 4, '', 0, 0, 'L', 0);
		$pdf->Cell(70, 4, utf8_decode("Peace - Work - Fatherland"), 0, 1, 'C', 0);

		$pdf->SetX(15);
		$pdf->SetFont('Helvetica', 'B', 8);
		$pdf->Cell(70, 2, '*********', 0, 0, 'C', 0);
		$pdf->Cell(40, 2, '', 0, 0, 'L', 0);
		$pdf->Cell(70, 2, '*********', 0, 1, 'C', 0);

		$pdf->SetX(15);
		$pdf->Cell(70, 4, utf8_decode('Ministère de la Santé Publique'), 0, 0, 'C', 0);
		$pdf->Cell(40, 4, '', 0, 0, 'L', 0);
		$pdf->Cell(70, 4, utf8_decode('Ministry of Public Health'), 0, 1, 'C', 0);

		$pdf->SetX(15);
		$pdf->SetFont('Helvetica', 'B', 8);
		$pdf->Cell(70, 2, '*********', 0, 0, 'C', 0);
		$pdf->Cell(40, 2, '', 0, 0, 'L', 0);
		$pdf->Cell(70, 2, '*********', 0, 1, 'C', 0);

		$pdf->SetX(15);
		$pdf->Cell(70, 4, utf8_decode("Prêt-Pro"), 0, 0, 'C', 0);
		$pdf->Cell(40, 4, '', 0, 0, 'L', 0);
		$pdf->Cell(70, 4, utf8_decode('Prêt-Pro'), 0, 1, 'C', 0);

		$pdf->SetX(15);
		$pdf->SetFont('Helvetica', 'B', 8);
		$pdf->Cell(70, 2, '*********', 0, 0, 'C', 0);
		$pdf->Cell(40, 2, '', 0, 0, 'L', 0);
		$pdf->Cell(70, 2, '*********', 0, 1, 'C', 0);


		$pdf->SetX(15);
		$pdf->Cell(70, 4, utf8_decode('BP : 14525 - Yaoundé, Tel : +237 697 993 386'), 0, 0, 'C', 0);
		$pdf->Cell(40, 4, '', 0, 0, 'L', 0);
		$pdf->Cell(70, 4, utf8_decode('Po.Box : 14525 - Yaoundé, Tel : +237 697 993 386'), 0, 1, 'C', 0);

		$pdf->SetX(15);
		$pdf->SetFont('Helvetica', 'B', 8);
		$pdf->Cell(70, 2, '*********', 0, 0, 'C', 0);
		$pdf->Cell(40, 2, '', 0, 0, 'L', 0);
		$pdf->Cell(70, 2, '*********', 0, 1, 'C', 0);

		$pdf->Ln(8);

		return $pdf;
	}
}
