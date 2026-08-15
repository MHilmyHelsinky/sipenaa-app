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
    private const PHOTO_X = 21.2;
    private const PHOTO_Y = 85.65;
    private const PHOTO_W = 61.35;
    private const PHOTO_H = 68.20;

    public function render(Card $card): string
    {
        $template = storage_path('app/templates/merge_nisn_2020.pdf');
        if (! is_file($template)) {
            throw new RuntimeException('Template PDF tidak ditemukan di storage/app/templates/merge_nisn_2020.pdf.');
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

        // Template PDF terbaru sudah bersih. Jangan menggambar kotak berwarna
        // di belakang field karena akan terlihat sebagai highlight.
        $values = $this->values($card);

        $this->writeFitted($pdf, $values['nisn'], 102.5, 24.8, 68.0, 9.5);
        $this->writeFitted($pdf, $values['nama'], 102.5, 35.6, 68.0, 9.5);
        $this->writeFitted($pdf, $values['tempat_lahir'], 102.5, 46.4, 68.0, 9.5);
        $this->writeFitted($pdf, $values['tgl_lahir'], 102.5, 57.2, 68.0, 9.5);
        $this->writeFitted($pdf, $values['alamat'], 102.5, 67.9, 68.0, 9.5);
        $this->writeFitted($pdf, $values['jenis_kelamin'], 102.5, 78.7, 68.0, 9.5);

        // Foto mengikuti Rectangle 6 pada dokumen Word.
        $photo = $this->photoPath($card);
        if ($photo) {
            $pdf->Image($photo, self::PHOTO_X, self::PHOTO_Y, self::PHOTO_W, self::PHOTO_H);
        }

        // Stempel + tanda tangan transparan diletakkan DI ATAS foto.
        $overlay = storage_path('app/templates/photo_official_overlay.png');
        if (is_file($overlay)) {
            $pdf->Image($overlay, self::PHOTO_X, self::PHOTO_Y, self::PHOTO_W, self::PHOTO_H);
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
