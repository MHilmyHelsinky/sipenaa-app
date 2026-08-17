<?php

declare(strict_types=1);

set_time_limit(0);

$root = dirname(__DIR__);
$vendorFontDir = $root . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'tecnickcom' . DIRECTORY_SEPARATOR . 'tcpdf' . DIRECTORY_SEPARATOR . 'fonts';
$fontSource = $root . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'fonts' . DIRECTORY_SEPARATOR . 'framd.ttf';
$fontDir = $root . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'fonts' . DIRECTORY_SEPARATOR . 'tcpdf';
$marker = $fontDir . DIRECTORY_SEPARATOR . 'franklin.family';

if (! is_file($fontSource)) {
    fwrite(STDERR, "Franklin Gothic Medium tidak ditemukan: {$fontSource}\n");
    exit(1);
}
if (! is_dir($vendorFontDir)) {
    fwrite(STDERR, "Folder font TCPDF tidak ditemukan: {$vendorFontDir}\n");
    exit(1);
}
if (! is_dir($fontDir) && ! mkdir($fontDir, 0777, true) && ! is_dir($fontDir)) {
    fwrite(STDERR, "Tidak dapat membuat folder: {$fontDir}\n");
    exit(1);
}

// Keep TCPDF's bundled core font definitions (Helvetica, Times, Courier) on vendor.
if (! defined('K_PATH_FONTS')) {
    define('K_PATH_FONTS', $vendorFontDir . DIRECTORY_SEPARATOR);
}

require $root . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

$pdf = new TCPDF();

try {
    $registered = $pdf->addTTFfont(
        $fontSource,
        'TrueTypeUnicode',
        '',
        'UTF-8',
        32,
        $fontDir . DIRECTORY_SEPARATOR
    );

    if (! is_string($registered) || $registered === '') {
        fwrite(STDERR, "Gagal mendaftarkan Franklin Gothic Medium.\n");
        exit(2);
    }

    $definition = $fontDir . DIRECTORY_SEPARATOR . $registered . '.php';
    if (! is_file($definition)) {
        fwrite(STDERR, "Definisi font tidak ditemukan setelah registrasi: {$definition}\n");
        exit(3);
    }

    file_put_contents($marker, $registered . PHP_EOL, LOCK_EX);
    echo "Franklin berhasil disiapkan: {$registered}\n";
    echo "Definisi font: {$definition}\n";
} catch (Throwable $e) {
    fwrite(STDERR, $e->getMessage() . "\n");
    exit(4);
}
