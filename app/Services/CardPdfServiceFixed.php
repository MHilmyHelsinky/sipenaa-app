<?php

namespace App\Services;

use App\Models\Card;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use setasign\Fpdi\Tcpdf\Fpdi;

class CardPdfServiceFixed
{
    private const PAGE_WIDTH = 567.0;
    private const PAGE_HEIGHT = 850.56;

    // Posisi foto/stempel/tanda tangan dipertahankan.
    private const PHOTO_X = 21.20;
    private const PHOTO_Y = 85.65;
    private const PHOTO_W = 61.35;
    private const PHOTO_H = 68.20;

    private const STAMP_X = 78.50;
    private const STAMP_Y = 87.20;
    private const STAMP_W = 41.50;
    private const STAMP_H = 42.00;

    private const SIGN_X = 112.00;
    private const SIGN_Y = 108.50;
    private const SIGN_W = 48.00;
    private const SIGN_H = 23.10;

    private const FIELD_X = 102.50;
    private const FIELD_SIZE = 9.5;
    private const FIELD_MAX_WIDTH = 118.00;

    private const PRINT_DATE_X = 139.0;
    private const PRINT_DATE_Y = 93.20;

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

        $pdf = new Fpdi('P', 'pt', [self::PAGE_WIDTH, self::PAGE_HEIGHT], true, 'UTF-8');
        $pdf->SetAutoPageBreak(false);
        $pdf->SetMargins(0, 0, 0);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetCompression(true);

        if ($pdf->setSourceFile($template) < 1) {
            throw new RuntimeException('Template PDF tidak memiliki halaman.');
        }

        $templatePage = $pdf->importPage(1);
        $pdf->AddPage('P', [self::PAGE_WIDTH, self::PAGE_HEIGHT]);
        $pdf->useTemplate($templatePage, 0, 0, self::PAGE_WIDTH, self::PAGE_HEIGHT, true);

        $fontName = $this->ensureFranklinGothicMedium($template);
        $values = $this->values($card);

        // Semua field dinamis menggunakan Franklin Gothic Medium 9.5 pt.
        $this->writeFitted($pdf, $fontName, $values['nisn'], self::FIELD_X, 25.10, self::FIELD_MAX_WIDTH);
        $this->writeFitted($pdf, $fontName, $values['nama'], self::FIELD_X, 35.90, self::FIELD_MAX_WIDTH);
        $this->writeFitted($pdf, $fontName, $values['tempat_lahir'], self::FIELD_X, 46.70, self::FIELD_MAX_WIDTH);
        $this->writeFitted($pdf, $fontName, $values['tgl_lahir'], self::FIELD_X, 57.50, self::FIELD_MAX_WIDTH);
        $this->writeFitted($pdf, $fontName, $values['alamat'], self::FIELD_X, 68.20, self::FIELD_MAX_WIDTH);
        $this->writeFitted($pdf, $fontName, $values['jenis_kelamin'], self::FIELD_X, 79.00, self::FIELD_MAX_WIDTH);

        $tanggalCetak = Carbon::now()->locale('id')->translatedFormat('d F Y');
        $pdf->SetFont($fontName, '', self::FIELD_SIZE, '', false);
        $pdf->Text(self::PRINT_DATE_X, self::PRINT_DATE_Y, $tanggalCetak);

        $photo = $this->photoPath($card);
        if ($photo) {
            $pdf->Image($photo, self::PHOTO_X, self::PHOTO_Y, self::PHOTO_W, self::PHOTO_H);
        }

        // Jangan mengubah posisi/aset stempel dan tanda tangan saat memperbaiki font.
        $pdf->Image($stamp, self::STAMP_X, self::STAMP_Y, self::STAMP_W, self::STAMP_H);
        $pdf->Image($signature, self::SIGN_X, self::SIGN_Y, self::SIGN_W, self::SIGN_H);

        $pdf->Output($outputPath, 'F');
        return $outputPath;
    }

    private function ensureFranklinGothicMedium(string $template): string
    {
        // TCPDF 7 memakai tc-lib-pdf-font. Jangan override K_PATH_FONTS ke folder aplikasi,
        // karena TCPDF juga mencari font core seperti helvetica.json di sana.
        if (! class_exists('TCPDF_FONTS')) {
            throw new RuntimeException('TCPDF_FONTS tidak tersedia. Jalankan composer install/update.');
        }

        $cacheDir = storage_path('app/tcpdf-fonts');
        if (! is_dir($cacheDir) && ! mkdir($cacheDir, 0777, true) && ! is_dir($cacheDir)) {
            throw new RuntimeException('Folder cache font TCPDF tidak dapat dibuat.');
        }

        $cacheKey = substr(sha1_file($template), 0, 16);
        $fontTtf = $cacheDir . DIRECTORY_SEPARATOR . 'FranklinGothic-Medium-' . $cacheKey . '.ttf';
        $fontName = 'franklin_gothic_medium_' . $cacheKey;
        $definitionFile = $cacheDir . DIRECTORY_SEPARATOR . strtolower($fontName) . '.php';

        if (! is_file($fontTtf) || filesize($fontTtf) < 1024) {
            $fontBytes = $this->extractFranklinFontFromPdf($template);
            if ($fontBytes !== null) {
                @file_put_contents($fontTtf, $fontBytes);
            }
        }

        if (is_file($fontTtf) && filesize($fontTtf) >= 1024) {
            try {
                if (! is_file($definitionFile)) {
                    $converted = \TCPDF_FONTS::addTTFfont(
                        $fontTtf,
                        'TrueTypeUnicode',
                        '',
                        32,
                        $cacheDir,
                        3,
                        1,
                        false,
                        false
                    );

                    if ($converted !== false) {
                        $fontName = $converted;
                    }
                }

                return $fontName;
            } catch (\Throwable) {
                // Fall through to the vendor TCPDF core font.
            }
        }

        // Fallback memakai font core TCPDF dari vendor, bukan dari storage/app/tcpdf-fonts.
        return 'helvetica';
    }

    private function extractFranklinFontFromPdf(string $pdfPath): ?string
    {
        $pdf = @file_get_contents($pdfPath);
        if ($pdf === false || $pdf === '') {
            return null;
        }

        if (! preg_match('/\/FontName\s*\/[^\s\/]*\+FranklinGothic-Medium\b.*?\/FontFile2\s+(\d+)\s+0\s+R/s', $pdf, $fontMatch)) {
            return null;
        }

        $fontObjectNumber = (int) $fontMatch[1];
        $objectPattern = '/\b' . $fontObjectNumber . '\s+0\s+obj\s*<<(.*?)>>\s*stream\r?\n/s';

        if (! preg_match($objectPattern, $pdf, $objectMatch, PREG_OFFSET_CAPTURE)) {
            return null;
        }

        $dictionary = $objectMatch[1][0];
        $streamStart = $objectMatch[0][1] + strlen($objectMatch[0][0]);

        if (! preg_match('/\/Length\s+(\d+)/', $dictionary, $lengthMatch)) {
            return null;
        }

        $length = (int) $lengthMatch[1];
        if ($length <= 0) {
            return null;
        }

        $rawStream = substr($pdf, $streamStart, $length);
        if ($rawStream === false) {
            return null;
        }

        $font = str_contains($dictionary, '/FlateDecode') ? @gzuncompress($rawStream) : $rawStream;

        if (! is_string($font) || ! str_starts_with($font, "\x00\x01\x00\x00")) {
            return null;
        }

        return $font;
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

    private function writeFitted(Fpdi $pdf, string $fontName, string $text, float $x, float $y, float $maxWidth): void
    {
        $text = trim($text);
        if ($text === '') {
            return;
        }

        for ($size = self::FIELD_SIZE; $size >= 7.5; $size -= 0.25) {
            $pdf->SetFont($fontName, '', $size, '', false);
            if ($pdf->GetStringWidth($text) <= $maxWidth) {
                $pdf->Text($x, $y, $text);
                return;
            }
        }

        $pdf->SetFont($fontName, '', 7.5, '', false);
        $pdf->Text($x, $y, $text);
    }
}
