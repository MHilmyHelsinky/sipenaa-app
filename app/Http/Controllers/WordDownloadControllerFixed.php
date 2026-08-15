<?php

namespace App\Http\Controllers;

use App\Models\Card;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\TemplateProcessor;
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
            ? \Carbon\Carbon::parse($card->tanggal_lahir)->locale('id')->translatedFormat('d F Y')
            : '-';

        $workingTemplate = storage_path('app/templates/.working_' . Str::uuid() . '.docx');
        if (! copy($templatePath, $workingTemplate)) {
            throw new RuntimeException('Template Word sementara tidak dapat dibuat.');
        }

        try {
            if ($photoPath) {
                $this->injectPhotoIntoFotoShape($workingTemplate, $photoPath);
            }

            $processor = new TemplateProcessor($workingTemplate);
            $processor->setValue('${nisn}', (string) ($card->nisn ?? '-'));
            $processor->setValue('${nama}', (string) ($card->nama_lengkap ?? '-'));
            $processor->setValue('${tempat_lahir}', (string) ($card->tempat_lahir ?? '-'));
            $processor->setValue('${tgl_lahir}', $tanggalLahir);
            $processor->setValue('${alamat}', $alamat !== '' ? $alamat : '-');
            $processor->setValue('${jenis_kelamin}', (string) ($card->jenis_kelamin ?? '-'));

            $directory = storage_path('app/public/card_exports');
            if (! is_dir($directory) && ! mkdir($directory, 0777, true) && ! is_dir($directory)) {
                throw new RuntimeException('Folder export Word tidak dapat dibuat: ' . $directory);
            }

            $filename = 'kartu_' . Str::slug($card->nama_lengkap ?: 'siswa') . '_' . ($card->nisn ?: 'card') . '.docx';
            $path = $directory . DIRECTORY_SEPARATOR . $filename;
            $processor->saveAs($path);

            return response()->download($path, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ])->deleteFileAfterSend(true);
        } finally {
            @unlink($workingTemplate);
        }
    }

    private function injectPhotoIntoFotoShape(string $docxPath, string $photoPath): void
    {
        if (! class_exists(\ZipArchive::class)) {
            throw new RuntimeException('PHP extension zip belum tersedia untuk menyisipkan foto ke template Word.');
        }

        $photoBytes = @file_get_contents($photoPath);
        if ($photoBytes === false || $photoBytes === '') {
            throw new RuntimeException('Foto siswa tidak dapat dibaca: ' . $photoPath);
        }

        $mime = @mime_content_type($photoPath) ?: 'image/jpeg';
        $extension = match ($mime) {
            'image/png' => 'png',
            'image/gif' => 'gif',
            default => 'jpg',
        };
        $mediaPath = 'word/media/sipena_photo.' . $extension;

        $zip = new \ZipArchive();
        if ($zip->open($docxPath) !== true) {
            throw new RuntimeException('Template Word sementara tidak dapat dibuka.');
        }

        $documentXml = $zip->getFromName('word/document.xml');
        $relsXml = $zip->getFromName('word/_rels/document.xml.rels');
        if ($documentXml === false || $relsXml === false) {
            $zip->close();
            throw new RuntimeException('Struktur XML template Word tidak lengkap.');
        }

        $fotoPos = strpos($documentXml, '${foto}');
        if ($fotoPos === false) {
            $zip->close();
            throw new RuntimeException('Placeholder ${foto} tidak ditemukan pada template Word asli.');
        }

        $shapeStart = strrpos(substr($documentXml, 0, $fotoPos), '<wps:wsp');
        $shapeEnd = strpos($documentXml, '</wps:wsp>', $fotoPos);
        if ($shapeStart === false || $shapeEnd === false) {
            $zip->close();
            throw new RuntimeException('Shape Rectangle 6 untuk foto tidak ditemukan pada template Word.');
        }

        $shapeEnd += strlen('</wps:wsp>');
        $shapeXml = substr($documentXml, $shapeStart, $shapeEnd - $shapeStart);

        $rId = $this->nextRelationshipId($relsXml);
        $relationship = '<Relationship Id="' . $rId . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="media/sipena_photo.' . $extension . '"/>';
        $newRelsXml = preg_replace('/<\/Relationships>/', $relationship . '</Relationships>', $relsXml, 1);

        $blipFill = '<a:blipFill><a:blip r:embed="' . $rId . '"/><a:stretch><a:fillRect/></a:stretch></a:blipFill>';
        $newShapeXml = preg_replace(
            '/(<wps:spPr\b[^>]*>.*?)(<a:noFill\s*\/>)/s',
            '$1' . $blipFill,
            $shapeXml,
            1
        );

        if ($newShapeXml === null || $newShapeXml === $shapeXml) {
            $zip->close();
            throw new RuntimeException('Shape foto tidak dapat diubah menjadi gambar.');
        }

        $newShapeXml = str_replace('<w:t>${foto}</w:t>', '<w:t></w:t>', $newShapeXml);
        $updatedDocumentXml = str_replace('<w:t>${foto}</w:t>', '<w:t></w:t>', $documentXml);
        $updatedDocumentXml = substr($updatedDocumentXml, 0, $shapeStart)
            . $newShapeXml
            . substr($updatedDocumentXml, $shapeEnd);

        $tempPath = $docxPath . '.tmp';
        @unlink($tempPath);
        $target = new \ZipArchive();
        if ($target->open($tempPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            $zip->close();
            throw new RuntimeException('File Word sementara tidak dapat dibuat.');
        }

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $stat = $zip->statIndex($i);
            if (! $stat || ! isset($stat['name'])) {
                continue;
            }
            $name = $stat['name'];
            if ($name === 'word/document.xml') {
                $target->addFromString($name, $updatedDocumentXml);
            } elseif ($name === 'word/_rels/document.xml.rels') {
                $target->addFromString($name, $newRelsXml);
            } else {
                $bytes = $zip->getFromIndex($i);
                if ($bytes !== false) {
                    $target->addFromString($name, $bytes);
                }
            }
        }

        $target->addFromString($mediaPath, $photoBytes);
        $target->close();
        $zip->close();

        if (! @rename($tempPath, $docxPath)) {
            @unlink($tempPath);
            throw new RuntimeException('File Word hasil patch tidak dapat dipasang.');
        }
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
