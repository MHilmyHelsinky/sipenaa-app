<?php

// Run once after placing storage/app/fonts/framd.ttf:
// php scripts/prepare-franklin-font.php

declare(strict_types=1);

set_time_limit(0);

require dirname(__DIR__) . '/vendor/autoload.php';

$fontSource = dirname(__DIR__) . '/storage/app/fonts/framd.ttf';
$fontDir = dirname(__DIR__) . '/storage/app/fonts/tcpdf';

if (! is_file($fontSource)) {
    fwrite(STDERR, "Franklin Gothic Medium tidak ditemukan: {$fontSource}\n");
    exit(1);
}

if (! is_dir($fontDir) && ! mkdir($fontDir, 0777, true) && ! is_dir($fontDir)) {
    fwrite(STDERR, "Tidak dapat membuat folder: {$fontDir}\n");
    exit(1);
}

if (! defined('K_PATH_FONTS')) {
    define('K_PATH_FONTS', $fontDir . DIRECTORY_SEPARATOR);
}

$pdf = new TCPDF();

try {
    // TCPDF 6.x performs the expensive TTF -> font-definition conversion here.
    // It runs from CLI with no 30-second web timeout.
    $registered = $pdf->addTTFfont($fontSource, 'TrueTypeUnicode', '', 32);

    if ($registered === false) {
        fwrite(STDERR, "Gagal mendaftarkan Franklin Gothic Medium.\n");
        exit(2);
    }

    echo "Franklin berhasil disiapkan: {$registered}\n";
    echo "Folder font TCPDF: {$fontDir}\n";
    exit(0);
} catch (Throwable $e) {
    fwrite(STDERR, $e->getMessage() . "\n");
    exit(3);
}
