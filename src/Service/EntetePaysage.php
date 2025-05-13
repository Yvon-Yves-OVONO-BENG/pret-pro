<?php

namespace App\Service;

use Fpdf\Fpdf;

class EntetePaysage
{
	public function __construct()
	{
	}

	public function entetePaysage(Fpdf $pdf): Fpdf
	{
		$pdf->Image('../public/images/logo/logo.png', 130, 12, 25);
		$pdf->Image('../public/images/logo/arrierePlan.PNG', 190, 90, 150);
		$pdf->SetFont('Helvetica', 'B', 12);
		// fond de couleur gris (valeurs en RGB)
		$pdf->setFillColor(230, 230, 230);
		// position du coin supérieur gauche par rapport à la marge gauche (mm)
		$pdf->SetX(20);
		$pdf->Cell(70, 4, utf8_decode("REPUBLIQUE DU CAMEROUN"), 0, 0, 'C', 0);
		$pdf->Cell(120, 4, '', 0, 0, 'L', 0);
		$pdf->Cell(70, 4, utf8_decode("REPUBLIC OF CAMEROON"), 0, 1, 'C', 0);

		$pdf->SetFont('Helvetica', 'B', 7);
		$pdf->SetX(20);
		$pdf->Cell(70, 4, utf8_decode("Paix - Travail - Patrie"), 0, 0, 'C', 0);
		$pdf->Cell(120, 4, '', 0, 0, 'L', 0);
		$pdf->Cell(70, 4, utf8_decode("Peace - Work - Fatherland"), 0, 1, 'C', 0);

		$pdf->SetX(20);
		$pdf->SetFont('Helvetica', 'B', 8);
		$pdf->Cell(70, 2, '*********', 0, 0, 'C', 0);
		$pdf->Cell(119, 2, '', 0, 0, 'L', 0);
		$pdf->Cell(70, 2, '*********', 0, 1, 'C', 0);

		#################################
		$pdf->SetX(20);
		$pdf->Cell(70, 4, utf8_decode('Ministère de la Santé Publique'), 0, 0, 'C', 0);
		$pdf->Cell(120, 4, '', 0, 0, 'L', 0);
		$pdf->Cell(70, 4, utf8_decode('Ministry of Public Health'), 0, 1, 'C', 0);

		$pdf->SetX(20);
		$pdf->SetFont('Helvetica', 'B', 8);
		$pdf->Cell(70, 2, '*********', 0, 0, 'C', 0);
		$pdf->Cell(119, 2, '', 0, 0, 'L', 0);
		$pdf->Cell(70, 2, '*********', 0, 1, 'C', 0);

		####################################
		// $pdf->SetX(20);
		// $pdf->Cell(70, 4, utf8_decode("Délégation Régionale de l'Est"), 0, 0, 'C', 0);
		// $pdf->Cell(120, 4, '', 0, 0, 'L', 0);
		// $pdf->Cell(70, 4, utf8_decode('East Regional Delegation'), 0, 1, 'C', 0);

		// $pdf->SetX(20);
		// $pdf->SetFont('Helvetica', 'B', 8);
		// $pdf->Cell(70, 2, '*********', 0, 0, 'C', 0);
		// $pdf->Cell(130, 2, '', 0, 0, 'L', 0);
		// $pdf->Cell(70, 2, '*********', 0, 1, 'C', 0);
		// ##################################

		// $pdf->SetX(20);
		// $pdf->Cell(70, 4, utf8_decode("Délégation Départementale du Haut Nyong"), 0, 0, 'C', 0);
		// $pdf->Cell(120, 4, '', 0, 0, 'L', 0);
		// $pdf->Cell(70, 4, utf8_decode('Haut Nyong Divisional Delegation'), 0, 1, 'C', 0);

		// $pdf->SetX(20);
		// $pdf->SetFont('Helvetica', 'B', 8);
		// $pdf->Cell(70, 2, '*********', 0, 0, 'C', 0);
		// $pdf->Cell(130, 2, '', 0, 0, 'L', 0);
		// $pdf->Cell(70, 2, '*********', 0, 1, 'C', 0);

		// ##############################
		// $pdf->SetX(20);
		// $pdf->Cell(70, 4, utf8_decode("District d'Abong Mbang"), 0, 0, 'C', 0);
		// $pdf->Cell(120, 4, '', 0, 0, 'L', 0);
		// $pdf->Cell(70, 4, utf8_decode('Abong Mbang District'), 0, 1, 'C', 0);

		// $pdf->SetX(20);
		// $pdf->SetFont('Helvetica', 'B', 8);
		// $pdf->Cell(70, 2, '*********', 0, 0, 'C', 0);
		// $pdf->Cell(130, 2, '', 0, 0, 'L', 0);
		// $pdf->Cell(70, 2, '*********', 0, 1, 'C', 0);
		########################################

		$pdf->SetX(20);
		$pdf->Cell(70, 4, utf8_decode("Prêt-Pro"), 0, 0, 'C', 0);
		$pdf->Cell(120, 4, '', 0, 0, 'L', 0);
		$pdf->Cell(70, 4, utf8_decode('Prêt-Pro'), 0, 1, 'C', 0);

		$pdf->SetX(20);
		$pdf->SetFont('Helvetica', 'B', 8);
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
