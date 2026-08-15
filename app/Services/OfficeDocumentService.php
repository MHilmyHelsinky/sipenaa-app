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

        $binary = $this->resolveLibreOfficeBinary();

        $command = sprintf(
            '%s --headless --convert-to pdf --outdir %s %s 2>&1',
            escapeshellarg($binary),
            escapeshellarg($outputDirectory),
            escapeshellarg($docxPath)
        );

        exec($command, $output, $exitCode);

        if ($exitCode !== 0) {
            throw new RuntimeException(
                'Konversi DOCX ke PDF gagal. LibreOffice tidak dapat dijalankan. Output: ' . implode("\n", $output)
            );
        }

        $pdfPath = $outputDirectory . DIRECTORY_SEPARATOR . pathinfo($docxPath, PATHINFO_FILENAME) . '.pdf';

        if (! is_file($pdfPath)) {
            throw new RuntimeException('PDF hasil konversi tidak ditemukan: ' . $pdfPath);
        }

        return $pdfPath;
    }

    protected function resolveLibreOfficeBinary(): string
    {
        $configured = trim((string) env('LIBREOFFICE_BIN', ''));
        if ($configured !== '') {
            return $configured;
        }

        $candidates = PHP_OS_FAMILY === 'Windows'
            ? [
                'C:\\Program Files\\LibreOffice\\program\\soffice.exe',
                'C:\\Program Files (x86)\\LibreOffice\\program\\soffice.exe',
            ]
            : [
                '/usr/bin/soffice',
                '/usr/local/bin/soffice',
            ];

        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        $command = PHP_OS_FAMILY === 'Windows' ? 'where soffice' : 'command -v soffice';
        exec($command, $output, $exitCode);
        if ($exitCode === 0 && ! empty($output[0])) {
            return trim($output[0]);
        }

        throw new RuntimeException(
            'LibreOffice tidak ditemukan. Pasang LibreOffice pada SERVER aplikasi, atau isi LIBREOFFICE_BIN pada file .env.'
        );
    }
}
