<?php

namespace App\Http\Controllers;

use App\Models\Card;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class WordDownloadControllerFixed extends Controller
{
    public function download(Card $card)
    {
        $templatePath = storage_path('app/templates/merge_nisn_2020.docx');
        if (! is_file($templatePath)) {
            abort(500, 'Template DOCX tidak ditemukan di storage/app/templates/merge_nisn_2020.docx');
        }

        $photoPath = null;
        if ($card->foto_path && Storage::disk('public')->exists($card->foto_path)) {
            $photoPath = Storage::disk('public')->path($card->foto_path);
        }

        $alamat = trim(
            ($card->desa ? $card->desa . ', ' : '') .
            ($card->kecamatan ? $card->kecamatan . ', ' : '') .
            ($card->kabupaten ?? '')
        );

        $tanggalLahir = $card->tanggal_lahir
            ? Carbon::parse($card->tanggal_lahir)->locale('id')->translatedFormat('d F Y')
            : '-';

        $values = [
            '${nisn}' => (string) ($card->nisn ?? '-'),
            '${nama}' => (string) ($card->nama_lengkap ?? '-'),
            '${tempat_lahir}' => (string) ($card->tempat_lahir ?? '-'),
            '${tgl_lahir}' => $tanggalLahir,
            '${alamat}' => $alamat !== '' ? $alamat : '-',
            '${jenis_kelamin}' => (string) ($card->jenis_kelamin ?? '-'),
        ];

        $directory = storage_path('app/public/card_exports');
        if (! is_dir($directory) && ! mkdir($directory, 0777, true) && ! is_dir($directory)) {
            throw new RuntimeException('Folder export Word tidak dapat dibuat: ' . $directory);
        }

        $filename = 'kartu_' . Str::slug($card->nama_lengkap ?: 'siswa') . '_' . ($card->nisn ?: 'card') . '.docx';
        $path = $directory . DIRECTORY_SEPARATOR . $filename;

        $this->buildDocx($templatePath, $path, $values, $photoPath);

        return response()->download($path, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ])->deleteFileAfterSend(true);
    }

    private function buildDocx(string $templatePath, string $outputPath, array $values, ?string $photoPath): void
    {
        if (! class_exists(\ZipArchive::class)) {
            throw new RuntimeException('PHP extension zip belum tersedia untuk membuat file Word.');
        }

        $source = new \ZipArchive();
        if ($source->open($templatePath) !== true) {
            throw new RuntimeException('Template Word tidak dapat dibuka.');
        }

        $documentXml = $source->getFromName('word/document.xml');
        $relsXml = $source->getFromName('word/_rels/document.xml.rels');
        if ($documentXml === false || $relsXml === false) {
            $source->close();
            throw new RuntimeException('Struktur XML template Word tidak lengkap.');
        }

        $photoBytes = null;
        $mediaPath = null;
        $newRelsXml = $relsXml;

        if ($photoPath) {
            $fotoPos = strpos($documentXml, '${foto}');
            if ($fotoPos === false) {
                $source->close();
                throw new RuntimeException('Placeholder ${foto} tidak ditemukan pada template Word asli.');
            }

            $shapeStart = strrpos(substr($documentXml, 0, $fotoPos), '<wps:wsp');
            $shapeEnd = strpos($documentXml, '</wps:wsp>', $fotoPos);
            if ($shapeStart === false || $shapeEnd === false) {
                $source->close();
                throw new RuntimeException('Rectangle 6 untuk foto tidak ditemukan pada template Word.');
            }

            $shapeEnd += strlen('</wps:wsp>');
            $shapeXml = substr($documentXml, $shapeStart, $shapeEnd - $shapeStart);

            $photoBytes = @file_get_contents($photoPath);
            if ($photoBytes === false || $photoBytes === '') {
                $source->close();
                throw new RuntimeException('Foto siswa tidak dapat dibaca: ' . $photoPath);
            }

            $mime = @mime_content_type($photoPath) ?: 'image/jpeg';
            $extension = match ($mime) {
                'image/png' => 'png',
                'image/gif' => 'gif',
                default => 'jpg',
            };
            $mediaPath = 'word/media/sipena_photo.' . $extension;

            $rId = $this->nextRelationshipId($relsXml);
            $relationship = '<Relationship Id="' . $rId . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="media/sipena_photo.' . $extension . '"/>';
            $newRelsXml = preg_replace('/<\/Relationships>/', $relationship . '</Relationships>', $relsXml, 1);

            $blipFill = '<a:blipFill><a:blip r:embed="' . $rId . '"/><a:stretch><a:fillRect/></a:stretch></a:blipFill>';
            $newShapeXml = preg_replace(
                '/(<wps:spPr\b[^>]*>.*?)(?:<a:noFill\s*\/?>)/s',
                '$1' . $blipFill,
                $shapeXml,
                1
            );

            if ($newShapeXml === null || $newShapeXml === $shapeXml) {
                $source->close();
                throw new RuntimeException('Rectangle 6 tidak dapat diisi dengan foto.');
            }

            $newShapeXml = str_replace('${foto}', '', $newShapeXml);
            $documentXml = substr($documentXml, 0, $shapeStart)
                . $newShapeXml
                . substr($documentXml, $shapeEnd);
        } else {
            $documentXml = str_replace('${foto}', '', $documentXml);
        }

        // Pastikan placeholder ${foto} juga hilang dari fallback/compatibility layer.
        $documentXml = str_replace('${foto}', '', $documentXml);

        // Isi teks langsung pada XML; TemplateProcessor tidak dipakai agar shape Word tetap utuh.
        foreach ($values as $placeholder => $value) {
            $escaped = htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
            $documentXml = str_replace($placeholder, $escaped, $documentXml);
        }

        $target = new \ZipArchive();
        if ($target->open($outputPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            $source->close();
            throw new RuntimeException('File Word hasil tidak dapat dibuat.');
        }

        for ($i = 0; $i < $source->numFiles; $i++) {
            $stat = $source->statIndex($i);
            if (! $stat || ! isset($stat['name'])) {
                continue;
            }

            $name = $stat['name'];
            if ($name === 'word/document.xml') {
                $target->addFromString($name, $documentXml);
            } elseif ($name === 'word/_rels/document.xml.rels') {
                $target->addFromString($name, $newRelsXml);
            } else {
                $bytes = $source->getFromIndex($i);
                if ($bytes !== false) {
                    $target->addFromString($name, $bytes);
                }
            }
        }

        if ($photoBytes !== null && $mediaPath !== null) {
            $target->addFromString($mediaPath, $photoBytes);
        }

        $target->close();
        $source->close();
    }

    private function nextRelationshipId(string $relsXml): string
    {
        $max = 0;
        if (preg_match_all('/Id="rId(\d+)"/', $relsXml, $matches)) {
            foreach ($matches[1] as $id) {
                $max = max($max, (int) $id);
            }
        }

        return 'rId' . ($max + 1);
    }
}
