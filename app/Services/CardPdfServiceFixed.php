<?php

namespace App\Services;

use App\Models\Card;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use setasign\Fpdi\Tcpdf\Fpdi;

class CardPdfServiceFixed
{
    private const PAGE_W = 567.0;
    private const PAGE_H = 850.56;
    private const FONT_SIZE = 9.5;

    private const VALUE_X = 102.5;
    private const VALUE_Y = [17.30, 28.10, 38.90, 49.70, 60.40, 71.20];
    private const VALUE_MAX_WIDTH = 165.0;

    private const PHOTO_X = 21.20;
    private const PHOTO_Y = 85.65;
    private const PHOTO_W = 61.35;
    private const PHOTO_H = 68.20;

    private const STAMP_X = 69.15;
    private const STAMP_Y = 85.65;
    private const STAMP_W = 69.37;
    private const STAMP_H = 70.39;

    private const SIGN_X = 132.10;
    private const SIGN_Y = 105.05;
    private const SIGN_W = 53.30;
    private const SIGN_H = 32.50;

    // Print date raised a little more to align with the "Banda Aceh," baseline.
    private const PRINT_DATE_X = 170.50;
    private const PRINT_DATE_Y = 85.00;

    public function render(Card $card): string
    {
        $template = storage_path('app/templates/merge_nisn_2020.pdf');
        $stamp = storage_path('app/templates/stamp.png');
        $signature = storage_path('app/templates/signature.png');

        if (! is_file($template)) {
            throw new RuntimeException('Template PDF tidak ditemukan di storage/app/templates/merge_nisn_2020.pdf.');
        }

        if (! is_file($stamp) || ! is_file($signature)) {
            throw new RuntimeException('stamp.png dan signature.png harus tersedia di storage/app/templates/.');
        }

        $outputDir = storage_path('app/public/card_exports/pdf');
        if (! is_dir($outputDir) && ! mkdir($outputDir, 0777, true) && ! is_dir($outputDir)) {
            throw new RuntimeException('Folder PDF tidak dapat dibuat: ' . $outputDir);
        }

        $safeName = preg_replace('/[^A-Za-z0-9_-]+/', '-', $card->nama_lengkap ?: 'siswa') ?: 'siswa';
        $filename = 'kartu_' . $safeName . '_' . ($card->nisn ?: 'card') . '.pdf';
        $outputPath = $outputDir . DIRECTORY_SEPARATOR . $filename;

        $pdf = new Fpdi('P', 'pt', [self::PAGE_W, self::PAGE_H]);
        $pdf->SetMargins(0, 0, 0);
        $pdf->SetAutoPageBreak(false);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetCompression(true);

        if ($pdf->setSourceFile($template) < 1) {
            throw new RuntimeException('Template PDF tidak memiliki halaman.');
        }

        $templatePage = $pdf->importPage(1);
        $pdf->AddPage('P', [self::PAGE_W, self::PAGE_H]);
        $pdf->useTemplate($templatePage, 0, 0, self::PAGE_W, self::PAGE_H, true);

        $font = $this->resolveFranklin($pdf);
        $values = $this->values($card);

        foreach (array_values($values) as $i => $value) {
            $this->writeFitted($pdf, $font, $value, self::VALUE_X, self::VALUE_Y[$i], self::VALUE_MAX_WIDTH);
        }

        $pdf->SetFont($font, '', self::FONT_SIZE);
        $pdf->Text(
            self::PRINT_DATE_X,
            self::PRINT_DATE_Y,
            Carbon::now()->locale('id')->translatedFormat('d F Y')
        );

        $photo = $this->photoPath($card);
        if ($photo) {
            $pdf->Image($photo, self::PHOTO_X, self::PHOTO_Y, self::PHOTO_W, self::PHOTO_H, '', '', '', false, 300, '', false, false, 0, 'CM', false, false);
        }

        $pdf->Image($stamp, self::STAMP_X, self::STAMP_Y, self::STAMP_W, self::STAMP_H, '', '', '', false, 300, '', false, false, 0, 'CM', false, false);
        $pdf->Image($signature, self::SIGN_X, self::SIGN_Y, self::SIGN_W, self::SIGN_H, '', '', '', false, 300, '', false, false, 0, 'CM', false, false);

        $pdf->Output($outputPath, 'F');

        return $outputPath;
    }

    private function resolveFranklin(Fpdi $pdf): string
    {
        $paths = array_filter([
            config('sipena.franklin_font_path'),
            storage_path('app/fonts/framd.ttf'),
            storage_path('app/fonts/FranklinGothic-Medium.ttf'),
            'C:\\Windows\\Fonts\\framd.ttf',
        ]);

        foreach ($paths as $fontFile) {
            if (! is_file($fontFile) || filesize($fontFile) < 1024) {
                continue;
            }

            try {
                $registered = $pdf->addTTFfont($fontFile, 'TrueTypeUnicode', '', 32);
                if ($registered !== false) {
                    return $registered;
                }
            } catch (\Throwable) {
                // Keep preview working even when the optional font is unavailable.
            }
        }

        return 'helvetica';
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
            'tgl_lahir' => $card->tanggal_lahir
                ? Carbon::parse($card->tanggal_lahir)->locale('id')->translatedFormat('d F Y')
                : '-',
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

    private function writeFitted(Fpdi $pdf, string $font, string $text, float $x, float $y, float $maxWidth): void
    {
        $text = trim($text);
        if ($text === '') {
            return;
        }

        for ($size = self::FONT_SIZE; $size >= 8.0; $size -= 0.25) {
            $pdf->SetFont($font, '', $size);
            if ($pdf->GetStringWidth($text) <= $maxWidth) {
                $pdf->Text($x, $y, $text);
                return;
            }
        }

        $pdf->SetFont($font, '', 8.0);
        $pdf->Text($x, $y, $text);
    }
}
