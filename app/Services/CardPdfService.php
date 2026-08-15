<?php

namespace App\Services;

use App\Models\Card;
use FPDF;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use setasign\Fpdi\Fpdi;

class CardPdfService
{
    private const PAGE_WIDTH = 567.0;
    private const PAGE_HEIGHT = 850.56;
    private const CARD_BLUE = [103, 192, 222];

    public function render(Card $card): string
    {
        $template = storage_path('app/templates/merge_nisn_2020.pdf');

        if (! is_file($template)) {
            throw new RuntimeException(
                'Template PDF tidak ditemukan di storage/app/templates/merge_nisn_2020.pdf.'
            );
        }

        $outputDir = storage_path('app/public/card_exports/pdf');
        if (! is_dir($outputDir) && ! mkdir($outputDir, 0777, true) && ! is_dir($outputDir)) {
            throw new RuntimeException('Folder PDF tidak dapat dibuat: ' . $outputDir);
        }

        $safeName = preg_replace('/[^A-Za-z0-9_-]+/', '-', $card->nama_lengkap ?: 'siswa') ?: 'siswa';
        $filename = 'kartu_' . $safeName . '_' . ($card->nisn ?: 'card') . '.pdf';
        $outputPath = $outputDir . DIRECTORY_SEPARATOR . $filename;

        $pdf = new Fpdi('P', 'pt', [self::PAGE_WIDTH, self::PAGE_HEIGHT]);
        $pdf->SetAutoPageBreak(false);
        $pdf->SetMargins(0, 0, 0);
        $pdf->SetCompression(true);

        if ($pdf->setSourceFile($template) < 1) {
            throw new RuntimeException('Template PDF tidak memiliki halaman.');
        }

        $templatePage = $pdf->importPage(1);
        $pdf->AddPage('P', [self::PAGE_WIDTH, self::PAGE_HEIGHT]);
        $pdf->useTemplate($templatePage, 0, 0, self::PAGE_WIDTH, self::PAGE_HEIGHT, true);

        // Hanya menutup teks placeholder. Ukuran berdasarkan bounding-box teks dari PDF asli.
        $this->coverPlaceholder($pdf, 101.0, 15.4, 70.0, 12.6);
        $this->coverPlaceholder($pdf, 101.0, 26.2, 70.0, 12.6);
        $this->coverPlaceholder($pdf, 101.0, 37.0, 70.0, 12.6);
        $this->coverPlaceholder($pdf, 101.0, 47.8, 70.0, 12.6);
        $this->coverPlaceholder($pdf, 101.0, 58.5, 70.0, 12.6);
        $this->coverPlaceholder($pdf, 100.0, 69.4, 72.0, 12.6);

        // Rectangle 6 pada DOCX: 779145 x 866140 EMU = 61.35 x 68.20 pt.
        $this->coverPlaceholder($pdf, 20.8, 85.2, 62.0, 70.0);

        $values = $this->values($card);
        $this->writeFitted($pdf, $values['nisn'], 102.5, 24.8, 68.0, 9.5);
        $this->writeFitted($pdf, $values['nama'], 102.5, 35.6, 68.0, 9.5);
        $this->writeFitted($pdf, $values['tempat_lahir'], 102.5, 46.4, 68.0, 9.5);
        $this->writeFitted($pdf, $values['tgl_lahir'], 102.5, 57.2, 68.0, 9.5);
        $this->writeFitted($pdf, $values['alamat'], 102.5, 67.9, 68.0, 9.5);
        $this->writeFitted($pdf, $values['jenis_kelamin'], 102.5, 78.7, 68.0, 9.5);

        $photo = $this->photoPath($card);
        if ($photo) {
            $pdf->Image($photo, 21.2, 85.65, 61.35, 68.20);
        }

        $pdf->Output('F', $outputPath);

        return $outputPath;
    }

    private function values(Card $card): array
    {
        $alamat = trim(
            ($card->desa ? $card->desa . ', ' : '') .
            ($card->kecamatan ? $card->kecamatan . ', ' : '') .
            ($card->kabupaten ?? '')
        );

        return [
            'nisn' => (string) ($card->nisn ?? '-'),
            'nama' => (string) ($card->nama_lengkap ?? '-'),
            'tempat_lahir' => (string) ($card->tempat_lahir ?? '-'),
            'tgl_lahir' => optional($card->tanggal_lahir)->format('d-m-Y') ?? '-',
            'alamat' => $alamat !== '' ? $alamat : '-',
            'jenis_kelamin' => (string) ($card->jenis_kelamin ?? '-'),
        ];
    }

    private function photoPath(Card $card): ?string
    {
        if (! $card->foto_path || ! Storage::disk('public')->exists($card->foto_path)) {
            return null;
        }

        return Storage::disk('public')->path($card->foto_path);
    }

    private function coverPlaceholder(FPDF $pdf, float $x, float $y, float $w, float $h): void
    {
        [$r, $g, $b] = self::CARD_BLUE;
        $pdf->SetFillColor($r, $g, $b);
        $pdf->Rect($x, $y, $w, $h, 'F');
    }

    private function writeFitted(FPDF $pdf, string $text, float $x, float $baselineY, float $maxWidth, float $size): void
    {
        $text = trim($text);
        if ($text === '') {
            return;
        }

        for ($current = min($size, 9.5); $current >= 7.0; $current -= 0.25) {
            $pdf->SetFont('Arial', 'B', $current);
            $value = $this->latin1($text);
            if ($pdf->GetStringWidth($value) <= $maxWidth) {
                $pdf->Text($x, $baselineY, $value);
                return;
            }
        }

        $pdf->SetFont('Arial', 'B', 7.0);
        $pdf->Text($x, $baselineY, $this->latin1($text));
    }

    private function latin1(string $text): string
    {
        return iconv('UTF-8', 'windows-1252//TRANSLIT//IGNORE', $text) ?: $text;
    }
}
