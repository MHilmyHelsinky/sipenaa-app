<?php

namespace App\Services;

use App\Models\Card;
use RuntimeException;
use setasign\Fpdi\Tcpdf\Fpdi;

class BatchCardPdfService
{
    // Same page size as the Word template: 7200900 x 10801350 EMU = 567 x 850.56 pt.
    // The supplied Word example places five card blocks on this single page vertically.
    private const PAGE_W = 567.0;
    private const PAGE_H = 850.56;
    private const SOURCE_CARD_W = 567.0;
    private const SOURCE_CARD_H = 850.56;

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
            foreach ($cards as $card) {
                $singlePaths[] = $singleService->render($card);
            }

            $outDir = storage_path('app/public/card_exports/batch');
            if (! is_dir($outDir) && ! mkdir($outDir, 0777, true) && ! is_dir($outDir)) {
                throw new RuntimeException('Folder cetak massal tidak dapat dibuat.');
            }

            $filename = 'cetak-massal-' . now()->format('Ymd-His') . '.pdf';
            $outputPath = $outDir . DIRECTORY_SEPARATOR . $filename;

            $pdf = new Fpdi('P', 'pt', [self::PAGE_W, self::PAGE_H]);
            $pdf->SetMargins(0, 0, 0);
            $pdf->SetAutoPageBreak(false);
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);

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

            $pdf->Output($outputPath, 'F');
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
        // This is intentionally a vertical layout because the supplied Word example
        // is one physical sheet containing five repeated card blocks.
        $gap = 0.0;
        $slotH = self::PAGE_H / $perPage;
        $cardH = $slotH - $gap;
        $scaleW = self::PAGE_W / self::SOURCE_CARD_W;
        $scaleH = $cardH / self::SOURCE_CARD_H;
        $scale = min($scaleW, $scaleH);
        $w = self::SOURCE_CARD_W * $scale;
        $h = self::SOURCE_CARD_H * $scale;
        $x = (self::PAGE_W - $w) / 2;

        $positions = [];
        for ($i = 0; $i < $perPage; $i++) {
            $positions[] = [$x, $i * $slotH, $w, $h];
        }

        return $positions;
    }
}
