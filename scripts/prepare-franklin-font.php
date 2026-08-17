<?php

declare(strict_types=1);

// Run once after placing storage/app/fonts/framd.ttf:
// php scripts/prepare-franklin-font.php
set_time_limit(0);
require dirname(__DIR__) . '/vendor/autoload.php';

$fontSource = dirname(__DIR__) . '/storage/app/fonts/framd.ttf';
$fontDir = dirname(__DIR__) . '/storage/app/fonts/tcpdf';
$marker = $fontDir . DIRECTORY_SEPARATOR . 'franklin.family';

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
    $registered = $pdf->addTTFfont($fontSource, 'TrueTypeUnicode', '', 32);
    if (! is_string($registered) || $registered === '') {
        fwrite(STDERR, "Gagal mendaftarkan Franklin Gothic Medium.\n");
        exit(2);
    }
    file_put_contents($marker, $registered . PHP_EOL, LOCK_EX);
    echo "Franklin berhasil disiapkan: {$registered}\n";
    echo "Font definitions: {$fontDir}\n";
} catch (Throwable $e) {
    fwrite(STDERR, $e->getMessage() . "\n");
    exit(3);
}
