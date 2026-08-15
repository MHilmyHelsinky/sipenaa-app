<?php

namespace App\Services;

use App\Models\Card;
use FPDF;
use setasign\Fpdi\Fpdi;
use RuntimeException;
use Illuminate\Support\Facades\Storage;

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

        $filename = 'kartu_' . preg_replace('/[^A-Za-z0-9_-]+/', '-', $card->nama_lengkap ?: 'siswa') . '_' . ($card->nisn ?: 'card') . '.pdf';
        $outputPath = $outputDir . DIRECTORY_SEPARATOR . $filename;

        $pdf = new Fpdi('P', 'pt', [self::PAGE_WIDTH, self::PAGE_HEIGHT]);
        $pdf->SetAutoPageBreak(false);
        $pdf->SetMargins(0, 0, 0);
        $pdf->SetCompression(true);

        $pageCount = $pdf->setSourceFile($template);
        if ($pageCount < 1) {
            throw new RuntimeException('Template PDF tidak memiliki halaman.');
        }

        $templatePage = $pdf->importPage(1);
        $pdf->AddPage('P', [self::PAGE_WIDTH, self::PAGE_HEIGHT]);
        $pdf->useTemplate($templatePage, 0, 0, self::PAGE_WIDTH, self::PAGE_HEIGHT, true);

        // Menutup hanya placeholder dinamis pada PDF asli.
        $this->coverPlaceholder($pdf, 100.8, 15.5, 100, 12.8);
        $this->coverPlaceholder($pdf, 100.8, 26.3, 100, 12.8);
        $this->coverPlaceholder($pdf, 100.8, 37.1, 100, 12.8);
        $this->coverPlaceholder($pdf, 100.8, 47.9, 100, 12.8);
        $this->coverPlaceholder($pdf, 100.8, 58.6, 105, 12.8);
        $this->coverPlaceholder($pdf, 100.0, 69.5, 105, 12.8);

        // Placeholder foto berada di shape Rectangle 6 pada DOCX: 61.35 x 68.20 pt.
        $this->coverPlaceholder($pdf, 20.8, 85.2, 62.0, 70.0);

        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('Arial', 'B', 9.5);

        $values = $this->values($card);

        $this->writeFitted($pdf, $values['nisn'], 102.5, 24.8, 145, 9.5);
        $this->writeFitted($pdf, $values['nama'], 102.5, 35.6, 145, 9.5);
        $this->writeFitted($pdf, $values['tempat_lahir'], 102.5, 46.4, 170, 9.5);
        $this->writeFitted($pdf, $values['tgl_lahir'], 102.5, 57.2, 145, 9.5);
        $this->writeFitted($pdf, $values['alamat'], 102.5, 67.9, 160, 9.5);
        $this->writeFitted($pdf, $values['jenis_kelamin'], 102.5, 78.7, 160, 9.5);

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

        $size = min($size, 9.5);
        while ($size >= 7.0) {
            $pdf->SetFont('Arial', 'B', $size);
            if ($pdf->GetStringWidth($this->latin1($text)) <= $maxWidth) {
                $pdf->Text($x, $baselineY, $this->latin1($text));
                return;
            }
            $size -= 0.25;
        }

        $pdf->SetFont('Arial', 'B', 7.0);
        $pdf->Text($x, $baselineY, $this->latin1($text));
    }

    private function latin1(string $text): string
    {
        return iconv('UTF-8', 'windows-1252//TRANSLIT//IGNORE', $text) ?: $text;
    }
}
