<?php

namespace App\Service;

use Fpdf\Fpdf;
use App\Entity\ElementsPiedDePage\PaginationPortrait;
use App\Repository\LigneDeEnsembleRepository;
use App\Service\EntetePortraitPagination;

class ImpressionDesEnsemblesService extends FPDF
{
    public function __construct( 
        protected BlocChiffreService $blocChiffreService,
        protected LigneDeEnsembleRepository $ligneDeEnsembleRepository,
        protected EntetePortraitPagination $entetePortraitPagination,
        )
    {}

    /**
     * Fonction qui imprime la liste des ensembles
     *
     * @param array $ensembles
     * @return PaginationPortrait
     */
    public function impressionDesEnsembles(array $ensembles): PaginationPortrait
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
        $pdf->SetX(15);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 5, 'LISTE DES ENSEMBLES ', 0, 1, 'C', 0);
        $pdf->Ln();

        $pdf->SetX(15);
        $pdf->SetFillColor(240,240,240);
        $pdf->Cell(15, 5, utf8_decode('N°'), 1, 0, 'C', true);
        $pdf->Cell(100, 5, utf8_decode('Ensemble'), 1, 0, 'C', true);
        $pdf->Cell(40, 5, utf8_decode('Qté produits'), 1, 0, 'C', true);
        $pdf->Cell(30, 5, utf8_decode('Prix'), 1, 1, 'C', true);

        $prix = 0;
        $i = 1;

        foreach ($ensembles as $ensemble) 
        {
            $pdf->SetX(15);
            
            $positionY = 80;
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->SetX(15);
            
            if ($i % 2 == 0) 
            {
                $pdf->SetFillColor(202,219,255);
            }else {
                $pdf->SetFillColor(255,255,255);
            }
            $pdf->SetX(15);
            $pdf->SetFont('Arial', '', 8);
            $pdf->Cell(15, 5, utf8_decode($i), 1, 0, 'C', true);
            $pdf->Cell(100, 5, utf8_decode($ensemble->getLibelle()), 1, 0, 'L', true);

            #je récupère ses lignes des ensemble
            $ligneDeEnsembles = $this->ligneDeEnsembleRepository->findBy([
                'produitEnsemble' => $ensemble
            ]);

            $pdf->Cell(40, 5, utf8_decode(number_format(count($ligneDeEnsembles), 0, '', ' ')), 1, 0, 'C', true);
            
            $pdf->Cell(30, 5, utf8_decode(number_format($ensemble->getPrixVente(), 0, '', ' ')." FCFA"), 1, 1, 'C', true);
            // $pdf->Cell(30, 5, utf8_decode($this->blocChiffreService->diviserEnBlocs($ensemble->getPrixVente())." FCFA"), 1, 1, 'C', true);
            
            $i++;

        }
        
        $pdf->SetX($pdf->GetX() + 15);
        $pdf->SetY($pdf->GetY() + 15);
        $pdf->SetFont('Arial', 'BU', 12);
        $pdf->Cell(150, 5, utf8_decode(''), 0, 0, 'R');

        return $pdf;
    }
}

