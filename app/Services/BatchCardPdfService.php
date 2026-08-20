<?php

namespace App\Services;

use App\Models\Card;
use RuntimeException;
use setasign\Fpdi\Fpdi;

class BatchCardPdfService
{
    // One sheet follows the supplied Word example: portrait 567 x 850.56 pt.
    private const PAGE_W = 567.0;
    private const PAGE_H = 850.56;

    public function render(iterable $cards, int $perPage = 5): string
    {
        $cards = collect($cards);
        if ($cards->isEmpty()) {
            throw new RuntimeException('Pilih minimal satu kartu untuk dicetak.');
        }

        // This service is specifically for the one-sheet Word-style layout.
        $perPage = in_array($perPage, [4, 5], true) ? $perPage : 5;
        $singleService = app(CardPdfService::class);
        $singlePaths = [];

        try {
            foreach ($cards as $card) {
                $singlePaths[] = $singleService->render($card);
            }

            $outDir = storage_path('app/public/card_exports/batch');
            if (! is_dir($outDir) && ! mkdir($outDir, 0777, true) && ! is_dir($outDir)) {
                throw new RuntimeException('Folder cetak massal tidak dapat dibuat.');
            }

            $filename = 'cetak-massal-' . now()->format('Ymd-His-u') . '.pdf';
            $outputPath = $outDir . DIRECTORY_SEPARATOR . $filename;

            // Use standalone FPDI here. Batch composition only needs FPDI's import/useTemplate
            // API; using the TCPDF adapter made the imported pages appear blank in this project.
            $pdf = new Fpdi('P', 'pt', [self::PAGE_W, self::PAGE_H]);
            $pdf->SetMargins(0, 0, 0);
            $pdf->SetAutoPageBreak(false);

            $positions = $this->positions($perPage);

            foreach ($singlePaths as $index => $path) {
                if (($index % $perPage) === 0) {
                    $pdf->AddPage('P', [self::PAGE_W, self::PAGE_H]);
                }

                [$x, $y, $w, $h] = $positions[$index % $perPage];

                $pageCount = $pdf->setSourceFile($path);
                if ($pageCount < 1) {
                    throw new RuntimeException('PDF kartu tidak memiliki halaman: ' . $path);
                }

                $templatePage = $pdf->importPage(1);
                $pdf->useTemplate($templatePage, $x, $y, $w, $h, true);
            }

            $pdf->Output('F', $outputPath);
            return $outputPath;
        } finally {
            foreach ($singlePaths as $path) {
                if (is_file($path)) {
                    @unlink($path);
                }
            }
        }
    }

    private function positions(int $perPage): array
    {
        // The Word reference shows five repeated card blocks on one physical sheet.
        // Each slot keeps the exact card aspect ratio while filling one fifth of the page.
        $slotH = self::PAGE_H / $perPage;
        $w = self::PAGE_W;
        $h = $slotH;

        // 4-up is still supported when selected manually; it simply uses four equal slots.
        $positions = [];
        for ($i = 0; $i < $perPage; $i++) {
            $positions[] = [0.0, $i * $slotH, $w, $h];
        }

        return $positions;
    }
}
