<?php

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
