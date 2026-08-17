<?php

namespace App\Services;

use App\Models\Card;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class CardPdfServiceFixed
{
    private const PAGE_WIDTH = 567.0;
    private const PAGE_HEIGHT = 850.56;

    // Background asli dari HEADER Word: dua kartu berdampingan.
    private const LEFT_CARD_X = 20.0;
    private const RIGHT_CARD_X = 290.3;
    private const CARD_Y = 0.0;
    private const CARD_W = 270.0;
    private const CARD_H = 170.6;

    // Enam field mengikuti text-box Word, Franklin Gothic Medium 9.5 pt.
    private const LABEL_X = 29.0;
    private const COLON_X = 81.5;
    private const VALUE_X = 94.0;
    private const FIELD_SIZE = 9.5;
    private const FIELD_ROWS = [24.0, 35.0, 46.0, 57.0, 68.0, 79.0];

    // Rectangle 6 pada Word: foto berada di kiri bawah field data.
    private const PHOTO_X = 29.5;
    private const PHOTO_Y = 82.5;
    private const PHOTO_W = 46.5;
    private const PHOTO_H = 45.0;

    // Aset stempel/tanda tangan yang Anda perbaiki; posisi mengikuti foto referensi pertama.
    private const STAMP_X = 80.0;
    private const STAMP_Y = 87.0;
    private const STAMP_W = 37.0;
    private const STAMP_H = 37.5;

    private const SIGN_X = 119.0;
    private const SIGN_Y = 116.0;
    private const SIGN_W = 46.0;
    private const SIGN_H = 16.2;

    // Text-box pejabat dari Word, ditulis sekali agar tidak pernah dobel.
    private const OFFICIAL_X = 110.0;
    private const OFFICIAL_DATE_Y = 83.0;
    private const OFFICIAL_DEPT1_Y = 95.0;
    private const OFFICIAL_DEPT2_Y = 106.0;
    private const OFFICIAL_NAME_Y = 128.0;
    private const OFFICIAL_NIP_Y = 141.5;

    public function render(Card $card): string
    {
        $templateDocx = storage_path('app/templates/merge_nisn_2020.docx');
        if (! is_file($templateDocx)) {
            throw new RuntimeException('Template Word tidak ditemukan di storage/app/templates/merge_nisn_2020.docx.');
        }

        $media = $this->extractWordMedia($templateDocx);
        if (! isset($media['left'], $media['right'])) {
            throw new RuntimeException('Background kartu asli dari template Word tidak dapat dibaca.');
        }

        $stamp = storage_path('app/templates/stamp.png');
        $signature = storage_path('app/templates/signature.png');
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

        $pdf = new \TCPDF('P', 'pt', [self::PAGE_WIDTH, self::PAGE_HEIGHT], true, 'UTF-8', false);
        $pdf->SetCreator('SIPENA');
        $pdf->SetAuthor('SIPENA');
        $pdf->SetTitle('Kartu Siswa');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(0, 0, 0);
        $pdf->SetAutoPageBreak(false, 0);
        $pdf->SetCompression(true);
        $pdf->AddPage('P', [self::PAGE_WIDTH, self::PAGE_HEIGHT]);
        $pdf->SetFillColor(255, 255, 255);
        $pdf->Rect(0, 0, self::PAGE_WIDTH, self::PAGE_HEIGHT, 'F');

        // Jangan lagi memakai merge_nisn_2020.pdf sebagai background.
        // PDF itu memiliki posisi text-box yang berbeda dari Word.
        $pdf->Image($media['left'], self::LEFT_CARD_X, self::CARD_Y, self::CARD_W, self::CARD_H, 'JPEG');
        $pdf->Image($media['right'], self::RIGHT_CARD_X, self::CARD_Y, self::CARD_W, self::CARD_H, 'PNG');

        $font = $this->resolveFont($pdf);
        $values = $this->values($card);

        $labels = ['NISN', 'Nama', 'Tempat Lahir', 'Tgl. Lahir', 'Alamat', 'Jenis Kelamin'];
        $keys = ['nisn', 'nama', 'tempat_lahir', 'tgl_lahir', 'alamat', 'jenis_kelamin'];

        foreach (self::FIELD_ROWS as $i => $y) {
            $pdf->SetFont($font, '', self::FIELD_SIZE);
            $pdf->Text(self::LABEL_X, $y, $labels[$i]);
            $pdf->Text(self::COLON_X, $y, ':');
            $this->writeFitted($pdf, $font, $values[$keys[$i]], self::VALUE_X, $y, 165.0);
        }

        // Blok pejabat ditulis satu kali, sesuai text-box Word.
        $pdf->SetFont($font, '', self::FIELD_SIZE);
        $pdf->Text(self::OFFICIAL_X, self::OFFICIAL_DATE_Y, 'Banda Aceh, 14 Juli 2026');
        $pdf->Text(self::OFFICIAL_X, self::OFFICIAL_DEPT1_Y, 'KEPALA DINAS PENDIDIKAN DAN');
        $pdf->Text(self::OFFICIAL_X, self::OFFICIAL_DEPT2_Y, 'KEBUDAYAAN KOTA BANDA ACEH');
        $pdf->Text(self::OFFICIAL_X, self::OFFICIAL_NAME_Y, 'SULAIMAN BAKRI, S.Pd., M.Pd.');
        $pdf->Text(self::OFFICIAL_X, self::OFFICIAL_NIP_Y, 'NIP. 196902101998011001');

        $photo = $this->photoPath($card);
        if ($photo) {
            $pdf->Image($photo, self::PHOTO_X, self::PHOTO_Y, self::PHOTO_W, self::PHOTO_H);
        }

        $pdf->Image($stamp, self::STAMP_X, self::STAMP_Y, self::STAMP_W, self::STAMP_H, 'PNG');
        $pdf->Image($signature, self::SIGN_X, self::SIGN_Y, self::SIGN_W, self::SIGN_H, 'PNG');

        $pdf->Output($outputPath, 'F');
        return $outputPath;
    }

    private function extractWordMedia(string $docxPath): array
    {
        if (! class_exists(\ZipArchive::class)) {
            throw new RuntimeException('PHP extension zip belum tersedia.');
        }

        $cacheDir = storage_path('app/card-template-assets');
        if (! is_dir($cacheDir) && ! mkdir($cacheDir, 0777, true) && ! is_dir($cacheDir)) {
            throw new RuntimeException('Folder aset template tidak dapat dibuat.');
        }

        $leftPath = $cacheDir . DIRECTORY_SEPARATOR . 'word-left-background.jpg';
        $rightPath = $cacheDir . DIRECTORY_SEPARATOR . 'word-right-background.png';

        if (is_file($leftPath) && is_file($rightPath)) {
            return ['left' => $leftPath, 'right' => $rightPath];
        }

        $zip = new \ZipArchive();
        if ($zip->open($docxPath) !== true) {
            throw new RuntimeException('Template Word tidak dapat dibuka sebagai ZIP.');
        }

        $left = $zip->getFromName('word/media/image4.jpeg');
        $right = $zip->getFromName('word/media/image5.png');
        $zip->close();

        if ($left === false || $right === false) {
            throw new RuntimeException('Aset background image4.jpeg/image5.png tidak ditemukan di template Word.');
        }

        if (file_put_contents($leftPath, $left) === false || file_put_contents($rightPath, $right) === false) {
            throw new RuntimeException('Aset background Word tidak dapat disimpan.');
        }

        return ['left' => $leftPath, 'right' => $rightPath];
    }

    private function resolveFont(\TCPDF $pdf): string
    {
        $fontFiles = [
            storage_path('app/fonts/framd.ttf'),
            storage_path('app/fonts/FranklinGothic-Medium.ttf'),
            'C:\\Windows\\Fonts\\framd.ttf',
        ];

        foreach ($fontFiles as $fontFile) {
            if (! is_file($fontFile) || filesize($fontFile) < 1024) {
                continue;
            }

            try {
                $registered = $pdf->addTTFfont($fontFile, 'TrueTypeUnicode', '', 32);
                if ($registered !== false) {
                    return $registered;
                }
            } catch (\Throwable) {
                // fallback ke Helvetica di bawah
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

    private function writeFitted(\TCPDF $pdf, string $fontName, string $text, float $x, float $y, float $maxWidth): void
    {
        $text = trim($text);
        if ($text === '') {
            return;
        }

        for ($size = self::FIELD_SIZE; $size >= 7.5; $size -= 0.25) {
            $pdf->SetFont($fontName, '', $size);
            if ($pdf->GetStringWidth($text) <= $maxWidth) {
                $pdf->Text($x, $y, $text);
                return;
            }
        }

        $pdf->SetFont($fontName, '', 7.5);
        $pdf->Text($x, $y, $text);
    }
}
