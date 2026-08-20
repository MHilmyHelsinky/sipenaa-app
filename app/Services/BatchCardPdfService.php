<?php

namespace App\Services;

use App\Models\Card;
use RuntimeException;
use setasign\Fpdi\Fpdi;

class BatchCardPdfService
{
    // Same physical sheet as the Word reference.
    private const PAGE_W = 567.0;
    private const PAGE_H = 850.56;

    public function render(iterable $cards, int $perPage = 5): string
    {
        $cards = collect($cards);
        if ($cards->isEmpty()) {
            throw new RuntimeException('Pilih minimal satu kartu untuk dicetak.');
        }

        $perPage = in_array($perPage, [4, 5], true) ? $perPage : 5;
        $singleService = app(CardPdfService::class);
        $singlePaths = [];

        try {
            // The single-card renderer already produces the exact card artwork.
            // For batch output we scale that page into one slot on the Word-sized sheet.
            foreach ($cards as $card) {
                $singlePaths[] = $singleService->render($card);
            }

            $outDir = storage_path('app/public/card_exports/batch');
            if (! is_dir($outDir) && ! mkdir($outDir, 0777, true) && ! is_dir($outDir)) {
                throw new RuntimeException('Folder cetak massal tidak dapat dibuat.');
            }

            $filename = 'cetak-massal-' . now()->format('Ymd-His-u') . '.pdf';
            $outputPath = $outDir . DIRECTORY_SEPARATOR . $filename;

            $pdf = new Fpdi('P', 'pt', [self::PAGE_W, self::PAGE_H]);
            $pdf->SetMargins(0, 0, 0);
            $pdf->SetAutoPageBreak(false);

            $positions = $this->positions($perPage);

            foreach ($singlePaths as $index => $path) {
                if (($index % $perPage) === 0) {
                    $pdf->AddPage('P', [self::PAGE_W, self::PAGE_H]);
                }

                [$x, $y, $w, $h] = $positions[$index % $perPage];
                if ($pdf->setSourceFile($path) < 1) {
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
        // The Word reference places the repeated cards vertically on one sheet.
        $slotH = self::PAGE_H / $perPage;
        $positions = [];

        for ($i = 0; $i < $perPage; $i++) {
            $positions[] = [0.0, $i * $slotH, self::PAGE_W, $slotH];
        }

        return $positions;
    }
}
