<?php

namespace App\Services;

use App\Models\Card;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use TCPDF;
use ZipArchive;

class CardPdfServiceFixed
{
    private const PAGE_W = 567.0;
    private const PAGE_H = 850.56;
    private const CARD_H = 170.08;
    private const FONT_SIZE = 9.5;

    private const VALUE_X = 113.5;
    private const VALUE_Y = [26.1, 39.1, 51.5, 63.9, 76.3, 88.7];
    private const PHOTO_X = 26.9;
    private const PHOTO_Y = 84.8;
    private const PHOTO_W = 61.4;
    private const PHOTO_H = 68.2;
    private const STAMP_X = 79.3;
    private const STAMP_Y = 84.9;
    private const STAMP_W = 69.6;
    private const STAMP_H = 70.6;
    private const SIGN_X = 134.0;
    private const SIGN_Y = 108.0;
    private const SIGN_W = 53.3;
    private const SIGN_H = 32.5;
    private const OFFICIAL_X = 119.0;
    private const OFFICIAL_DATE_Y = 95.0;
    private const OFFICIAL_DEPT1_Y = 108.5;
    private const OFFICIAL_DEPT2_Y = 120.5;
    private const OFFICIAL_NAME_Y = 144.0;
    private const OFFICIAL_NIP_Y = 157.0;

    public function render(Card $card): string
    {
        $templateDocx = storage_path('app/templates/merge_nisn_2020.docx');
        if (! is_file($templateDocx)) {
            throw new RuntimeException('Template Word tidak ditemukan: storage/app/templates/merge_nisn_2020.docx');
        }

        $assets = $this->extractAssets($templateDocx);
        $stamp = is_file(storage_path('app/templates/stamp.png')) ? storage_path('app/templates/stamp.png') : $assets['stamp'];
        $signature = is_file(storage_path('app/templates/signature.png')) ? storage_path('app/templates/signature.png') : $assets['signature'];

        $outputDir = storage_path('app/public/card_exports/pdf');
        if (! is_dir($outputDir) && ! mkdir($outputDir, 0777, true) && ! is_dir($outputDir)) {
            throw new RuntimeException('Folder PDF tidak dapat dibuat: ' . $outputDir);
        }

        $safeName = preg_replace('/[^A-Za-z0-9_-]+/', '-', $card->nama_lengkap ?: 'siswa') ?: 'siswa';
        $filename = 'kartu_' . $safeName . '_' . ($card->nisn ?: 'card') . '.pdf';
        $outputPath = $outputDir . DIRECTORY_SEPARATOR . $filename;

        $pdf = new TCPDF('P', 'pt', [self::PAGE_W, self::PAGE_H], true, 'UTF-8', false);
        $pdf->SetMargins(0, 0, 0);
        $pdf->SetAutoPageBreak(false, 0);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetCompression(true);
        $pdf->AddPage('P', [self::PAGE_W, self::PAGE_H]);

        $font = $this->resolveFranklin($pdf);
        $pdf->Image($assets['left_crop'], 0, 0, self::PAGE_W, self::CARD_H, 'JPEG');
        $pdf->Image($assets['right'], 274.4, 0, 267.9, 170.2, 'JPEG');

        $values = $this->values($card);
        foreach ($values as $i => $value) {
            $this->writeFitted($pdf, $font, $value, self::VALUE_X, self::VALUE_Y[$i], 155.0);
        }

        $today = Carbon::now()->locale('id')->translatedFormat('d F Y');
        $this->writeLine($pdf, $font, 'Banda Aceh, ' . $today, self::OFFICIAL_X, self::OFFICIAL_DATE_Y);
        $this->writeLine($pdf, $font, 'KEPALA DINAS PENDIDIKAN DAN', self::OFFICIAL_X, self::OFFICIAL_DEPT1_Y);
        $this->writeLine($pdf, $font, 'KEBUDAYAAN KOTA BANDA ACEH', self::OFFICIAL_X, self::OFFICIAL_DEPT2_Y);
        $this->writeLine($pdf, $font, 'SULAIMAN BAKRI, S.Pd., M.Pd.', self::OFFICIAL_X, self::OFFICIAL_NAME_Y);
        $this->writeLine($pdf, $font, 'NIP. 196902101998011001', self::OFFICIAL_X, self::OFFICIAL_NIP_Y);

        $photo = $this->photoPath($card);
        if ($photo) {
            $pdf->Image($photo, self::PHOTO_X, self::PHOTO_Y, self::PHOTO_W, self::PHOTO_H);
        }
        $pdf->Image($stamp, self::STAMP_X, self::STAMP_Y, self::STAMP_W, self::STAMP_H, 'PNG');
        $pdf->Image($signature, self::SIGN_X, self::SIGN_Y, self::SIGN_W, self::SIGN_H, 'PNG');

        $pdf->Output($outputPath, 'F');
        $this->cleanup($assets);

        return $outputPath;
    }

    private function extractAssets(string $docxPath): array
    {
        if (! class_exists(ZipArchive::class)) {
            throw new RuntimeException('PHP extension zip belum tersedia untuk membaca aset template Word.');
        }

        $dir = storage_path('app/pdf-template-assets');
        if (! is_dir($dir) && ! mkdir($dir, 0777, true) && ! is_dir($dir)) {
            throw new RuntimeException('Folder aset PDF tidak dapat dibuat.');
        }

        $paths = [
            'left_raw' => $dir . DIRECTORY_SEPARATOR . uniqid('left_', true) . '.jpg',
            'left_crop' => $dir . DIRECTORY_SEPARATOR . uniqid('left_crop_', true) . '.jpg',
            'right' => $dir . DIRECTORY_SEPARATOR . uniqid('right_', true) . '.jpeg',
            'stamp' => $dir . DIRECTORY_SEPARATOR . uniqid('stamp_', true) . '.png',
            'signature' => $dir . DIRECTORY_SEPARATOR . uniqid('signature_', true) . '.png',
        ];

        $entries = [
            'left_raw' => 'word/media/image6.jpg',
            'right' => 'word/media/image1.jpeg',
            'stamp' => 'word/media/image2.png',
            'signature' => 'word/media/image3.png',
        ];

        $zip = new ZipArchive();
        if ($zip->open($docxPath) !== true) {
            throw new RuntimeException('Template Word tidak dapat dibuka.');
        }

        foreach ($entries as $key => $entry) {
            $bytes = $zip->getFromName($entry);
            if ($bytes === false || file_put_contents($paths[$key], $bytes) === false) {
                $zip->close();
                throw new RuntimeException('Aset template tidak ditemukan: ' . $entry);
            }
        }
        $zip->close();

        $this->createLeftCrop($paths['left_raw'], $paths['left_crop']);

        return $paths;
    }

    private function createLeftCrop(string $leftRaw, string $leftCrop): void
    {
        if (function_exists('imagecreatefromjpeg') && function_exists('imagejpeg')) {
            $src = @imagecreatefromjpeg($leftRaw);
            if ($src !== false) {
                $w = imagesx($src);
                $h = imagesy($src);
                $cropH = max(1, (int) round($h * 0.5));
                $crop = imagecreatetruecolor($w, $cropH);
                imagecopy($crop, $src, 0, 0, 0, 0, $w, $cropH);
                imagejpeg($crop, $leftCrop, 95);
                imagedestroy($crop);
                imagedestroy($src);
                return;
            }
        }

        if (! copy($leftRaw, $leftCrop)) {
            throw new RuntimeException('Background kiri tidak dapat diproses.');
        }
    }

    private function cleanup(array $assets): void
    {
        foreach ($assets as $path) {
            if (is_file($path)) {
                @unlink($path);
            }
        }
    }

    private function resolveFranklin(TCPDF $pdf): string
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
                // try next candidate
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
            (string) ($card->nisn ?? '-'),
            (string) ($card->nama_lengkap ?? '-'),
            (string) ($card->tempat_lahir ?? '-'),
            $card->tanggal_lahir ? Carbon::parse($card->tanggal_lahir)->locale('id')->translatedFormat('d F Y') : '-',
            $alamat !== '' ? $alamat : '-',
            (string) ($card->jenis_kelamin ?? '-'),
        ];
    }

    private function photoPath(Card $card): ?string
    {
        if (! $card->foto_path || ! Storage::disk('public')->exists($card->foto_path)) {
            return null;
        }
        return Storage::disk('public')->path($card->foto_path);
    }

    private function writeFitted(TCPDF $pdf, string $font, string $text, float $x, float $y, float $maxWidth): void
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

    private function writeLine(TCPDF $pdf, string $font, string $text, float $x, float $y): void
    {
        $pdf->SetFont($font, '', self::FONT_SIZE);
        $pdf->Text($x, $y, $text);
    }
}
