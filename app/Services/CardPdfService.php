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
    private const BATCH_CARD_HEIGHT = self::PAGE_HEIGHT / 5;

    // Exact coordinates measured from the original Word-generated PDF.
    private const PHOTO_X = 21.20;
    private const PHOTO_Y = 85.65;
    private const PHOTO_W = 61.35;
    private const PHOTO_H = 68.20;

    private const STAMP_X = 80.50;
    private const STAMP_Y = 85.65;
    private const STAMP_W = 69.33;
    private const STAMP_H = 70.35;

    private const SIGN_X = 143.45;
    private const SIGN_Y = 105.05;
    private const SIGN_W = 53.30;
    private const SIGN_H = 32.50;

    public function render(Card $card): string
    {
        return $this->renderInternal($card, self::PAGE_HEIGHT, false);
    }

    /**
     * Renders only the physical card area used by the five-card Word sheet.
     * The original template page is clipped to the first 1/5 of the sheet;
     * no coordinate or card artwork is changed.
     */
    public function renderForBatch(Card $card): string
    {
        return $this->renderInternal($card, self::BATCH_CARD_HEIGHT, true);
    }

    private function renderInternal(Card $card, float $pageHeight, bool $compact): string
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
        $suffix = $compact ? '_batch_' . bin2hex(random_bytes(4)) : '';
        $filename = 'kartu_' . $safeName . '_' . ($card->nisn ?: 'card') . $suffix . '.pdf';
        $outputPath = $outputDir . DIRECTORY_SEPARATOR . $filename;

        $pdf = new Fpdi('P', 'pt', [self::PAGE_WIDTH, $pageHeight]);
        $pdf->SetAutoPageBreak(false);
        $pdf->SetMargins(0, 0, 0);
        $pdf->SetCompression(true);

        if ($pdf->setSourceFile($template) < 1) {
            throw new RuntimeException('Template PDF tidak memiliki halaman.');
        }

        $templatePage = $pdf->importPage(1);
        $pdf->AddPage('P', [self::PAGE_WIDTH, $pageHeight]);

        // The template artwork is already positioned exactly at the top of the
        // full Word page. On the compact page, everything below pageHeight is
        // naturally clipped by the PDF page boundary.
        $pdf->useTemplate($templatePage, 0, 0, self::PAGE_WIDTH, self::PAGE_HEIGHT, true);

        $values = $this->values($card);

        // The template already contains the labels. Only the values are added.
        $this->writeFitted($pdf, $values['nisn'], 104.87, 25.20, 70.0, 9.48);
        $this->writeFitted($pdf, $values['nama'], 104.87, 36.00, 70.0, 9.48);
        $this->writeFitted($pdf, $values['tempat_lahir'], 104.87, 46.80, 70.0, 9.48);
        $this->writeFitted($pdf, $values['tgl_lahir'], 104.87, 57.60, 70.0, 9.48);
        $this->writeFitted($pdf, $values['alamat'], 104.87, 68.40, 70.0, 8.50);
        $this->writeFitted($pdf, $values['jenis_kelamin'], 102.59, 79.20, 70.0, 9.48);

        $photo = $this->photoPath($card);
        if ($photo) {
            $pdf->Image($photo, self::PHOTO_X, self::PHOTO_Y, self::PHOTO_W, self::PHOTO_H);
        }

        $stamp = storage_path('app/templates/stamp.png');
        $signature = storage_path('app/templates/signature.png');

        if (! is_file($stamp) || ! is_file($signature)) {
            throw new RuntimeException(
                'Aset stempel/tanda tangan belum tersedia. Letakkan stamp.png dan signature.png di storage/app/templates.'
            );
        }

        $pdf->Image($stamp, self::STAMP_X, self::STAMP_Y, self::STAMP_W, self::STAMP_H);
        $pdf->Image($signature, self::SIGN_X, self::SIGN_Y, self::SIGN_W, self::SIGN_H);
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

        $tanggalLahir = $card->tanggal_lahir
            ? \Carbon\Carbon::parse($card->tanggal_lahir)->locale('id')->translatedFormat('d F Y')
            : '-';

        return [
            'nisn' => (string) ($card->nisn ?? '-'),
            'nama' => (string) ($card->nama_lengkap ?? '-'),
            'tempat_lahir' => (string) ($card->tempat_lahir ?? '-'),
            'tgl_lahir' => $tanggalLahir,
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

    private function writeFitted(
        FPDF $pdf,
        string $text,
        float $x,
        float $baselineY,
        float $maxWidth,
        float $size
    ): void {
        $text = trim($text);
        if ($text === '') {
            return;
        }

        $value = $this->latin1($text);

        for ($current = $size; $current >= 7.0; $current -= 0.25) {
            $pdf->SetFont('Arial', '', $current);
            if ($pdf->GetStringWidth($value) <= $maxWidth) {
                $pdf->Text($x, $baselineY, $value);
                return;
            }
        }

        $pdf->SetFont('Arial', '', 7.0);
        $pdf->Text($x, $baselineY, $value);
    }

    private function latin1(string $text): string
    {
        return iconv('UTF-8', 'windows-1252//TRANSLIT//IGNORE', $text) ?: $text;
    }
}
