<?php

// Keep the generated TCPDF font definitions in a persistent application folder.
// This avoids asking TCPDF to parse the TTF on every Preview request.
$tcpdfFontDir = storage_path('app/fonts/tcpdf');
if (! is_dir($tcpdfFontDir)) {
    @mkdir($tcpdfFontDir, 0777, true);
}
if (! defined('K_PATH_FONTS')) {
    define('K_PATH_FONTS', $tcpdfFontDir . DIRECTORY_SEPARATOR);
}

// Compatibility aliases keep existing routes/controllers working.
if (! class_exists(\App\Http\Controllers\WordDownloadController::class, false)) {
    class_alias(
        \App\Http\Controllers\WordDownloadControllerFixed::class,
        \App\Http\Controllers\WordDownloadController::class
    );
}

if (! class_exists(\App\Services\CardPdfService::class, false)) {
    class_alias(
        \App\Services\CardPdfServiceFixed::class,
        \App\Services\CardPdfService::class
    );
}
