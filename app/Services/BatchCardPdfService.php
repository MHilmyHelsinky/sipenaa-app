<?php

namespace App\Services;

use App\Models\Card;
use RuntimeException;
use setasign\Fpdi\Tcpdf\Fpdi;

class BatchCardPdfService
{
    private const A4_W = 595.28;
    private const A4_H = 841.89;
    private const CARD_W = 567.0;
    private const CARD_H = 850.56;

    public function render(iterable $cards, int $perPage = 4): string
    {
        $cards = collect($cards);
        if ($cards->isEmpty()) {
            throw new RuntimeException('Pilih minimal satu kartu untuk dicetak.');
        }

        $perPage = in_array($perPage, [4, 5], true) ? $perPage : 4;
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

            // Use the FPDI-TCPDF adapter because this service needs the TCPDF API
            // and FPDI's standalone class does not provide setPrintHeader/setPrintFooter.
            $pdf = new Fpdi('P', 'pt', [self::A4_W, self::A4_H]);
            $pdf->SetMargins(0, 0, 0);
            $pdf->SetAutoPageBreak(false);
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);

            $positions = $this->positions($perPage);

            foreach ($singlePaths as $index => $path) {
                if (($index % $perPage) === 0) {
                    $pdf->AddPage('P', [self::A4_W, self::A4_H]);
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
        if ($perPage === 5) {
            $w = 187.0;
            $h = $w * (self::CARD_H / self::CARD_W);
            $xLeft = 38.0;
            $xRight = 370.0;
            $y1 = 4.0;
            $y2 = 281.0;
            $y3 = 558.0;

            return [
                [$xLeft, $y1, $w, $h],
                [$xRight, $y1, $w, $h],
                [$xLeft, $y2, $w, $h],
                [$xRight, $y2, $w, $h],
                [$xLeft, $y3, $w, $h],
            ];
        }

        $w = 283.0;
        $h = $w * (self::CARD_H / self::CARD_W);
        return [
            [5.0, 5.0, $w, $h],
            [307.0, 5.0, $w, $h],
            [5.0, 421.0, $w, $h],
            [307.0, 421.0, $w, $h],
        ];
    }
}
