<?php

namespace App\Services;

use RuntimeException;

class OfficeDocumentService
{
    public function convertDocxToPdf(string $docxPath, string $outputDirectory): string
    {
        if (! is_file($docxPath)) {
            throw new RuntimeException('DOCX sumber tidak ditemukan: ' . $docxPath);
        }

        if (! is_dir($outputDirectory) && ! mkdir($outputDirectory, 0777, true) && ! is_dir($outputDirectory)) {
            throw new RuntimeException('Direktori output PDF tidak dapat dibuat: ' . $outputDirectory);
        }

        $binary = env('LIBREOFFICE_BIN', 'soffice');
        $command = sprintf(
            '%s --headless --convert-to pdf --outdir %s %s 2>&1',
            escapeshellarg($binary),
            escapeshellarg($outputDirectory),
            escapeshellarg($docxPath)
        );

        exec($command, $output, $exitCode);

        if ($exitCode !== 0) {
            throw new RuntimeException(
                'Konversi DOCX ke PDF gagal. Pastikan LibreOffice/soffice terpasang. Output: ' . implode("\n", $output)
            );
        }

        $pdfPath = $outputDirectory . DIRECTORY_SEPARATOR . pathinfo($docxPath, PATHINFO_FILENAME) . '.pdf';

        if (! is_file($pdfPath)) {
            throw new RuntimeException('PDF hasil konversi tidak ditemukan: ' . $pdfPath);
        }

        return $pdfPath;
    }
}
