<?php

namespace App\Service;

use Fpdf\Fpdf;
use App\Entity\ElementsPiedDePage\PaginationPortrait;
use App\Service\EntetePortraitPagination;

class ImpressionDesExamensService extends FPDF
{
    public function __construct( 
        protected BlocChiffreService $blocChiffreService,
        protected EntetePortraitPagination $entetePortraitPagination,
        )
    {}

    /**
     * Fonction qui imprime la liste des examens
     *
     * @param array $examens
     * @return PaginationPortrait
     */
    public function impressionDesExamens(array $examens): PaginationPortrait
    {
        $pdf = new PaginationPortrait();

        $pdf->addPage('P');

        $pdf = $this->entetePortraitPagination->entetePortraitPagination($pdf);

        $pdf->SetLeftMargin(10);

        $positionY = 50;
        $pdf->SetXY(15, $positionY);

        // $pdf->Image('../public/images/qrcode/'.$examen->getQrCode(), 10, 40, 500);
        // $pdf->Image('../public/images/qrcode/'.$examen->getQrCode(), 165, 67, 34, 34);
        $pdf->SetX(15);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 5, 'LISTE DES EXAMENS ', 0, 1, 'C', 0);
        $pdf->Ln();

        $pdf->SetX(15);
        $pdf->SetFillColor(240,240,240);
        $pdf->Cell(15, 5, utf8_decode('N°'), 1, 0, 'C', true);
        $pdf->Cell(130, 5, utf8_decode('Examen'), 1, 0, 'C', true);
        $pdf->Cell(30, 5, utf8_decode('Prix'), 1, 1, 'C', true);

        $i = 1;
        foreach ($examens as $examen) 
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
            $pdf->Cell(130, 5, utf8_decode($examen->getLibelle()), 1, 0, 'L', true);
            
            $pdf->Cell(30, 5, utf8_decode(number_format($examen->getPrixVente(), 0, '', ' ')." FCFA"), 1, 1, 'C', true);
            // $pdf->Cell(30, 5, utf8_decode($this->blocChiffreService->diviserEnBlocs($examen->getPrixVente())." FCFA"), 1, 1, 'C', true);
            
            $i++;

        }
        
        $pdf->SetX($pdf->GetX() + 15);
        $pdf->SetY($pdf->GetY() + 15);
        $pdf->SetFont('Arial', 'BU', 12);
        $pdf->Cell(150, 5, utf8_decode(''), 0, 0, 'R');

        return $pdf;
    }
}

