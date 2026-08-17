<?php

// This file is loaded by Composer before Laravel's Application container is booted.
// Therefore it MUST NOT call Laravel helpers such as storage_path().
// Build the persistent TCPDF font directory from the project root instead.
$projectRoot = dirname(__DIR__);
$tcpdfFontDir = $projectRoot . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'fonts' . DIRECTORY_SEPARATOR . 'tcpdf';

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
