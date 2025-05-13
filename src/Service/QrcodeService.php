<?php

namespace App\Service;

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

class QrcodeService
{
    public function generateQrCode(string $data): string
    {
        // Créer une instance du QR Code
        $qrCode = QrCode::create($data)
            ->setSize(300)
            ->setMargin(10);

        // Créer un writer pour sauvegarder l'image
        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        // Générer un nom de fichier unique
        $fileName = uniqid('qrcode_', true) . '.png';
        $filePath = \dirname(__DIR__, 2) . '/public/images/qrCode/' . $fileName;

        // Sauvegarder le fichier PNG
        $result->saveToFile($filePath);

        return $fileName; // Retourner le nom du fichier généré
    }
}