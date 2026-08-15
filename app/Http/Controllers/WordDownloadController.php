<?php

namespace App\Http\Controllers;

use App\Models\Card;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\TemplateProcessor;
use RuntimeException;

class WordDownloadController extends Controller
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

        $processor = new TemplateProcessor($templatePath);

        $processor->setValue('${nisn}', (string) ($card->nisn ?? '-'));
        $processor->setValue('${nama}', (string) ($card->nama_lengkap ?? '-'));
        $processor->setValue('${tempat_lahir}', (string) ($card->tempat_lahir ?? '-'));
        $processor->setValue('${tgl_lahir}', $tanggalLahir);
        $processor->setValue('${alamat}', $alamat !== '' ? $alamat : '-');
        $processor->setValue('${jenis_kelamin}', (string) ($card->jenis_kelamin ?? '-'));

        // The ${foto} placeholder is inside a Word text-box shape.
        // TemplateProcessor's image replacement does not reliably replace that shape,
        // so it is injected into the shape itself after saving the DOCX.
        $processor->setValue('${foto}', '');

        $directory = storage_path('app/public/card_exports');
        if (! is_dir($directory) && ! mkdir($directory, 0777, true) && ! is_dir($directory)) {
            throw new RuntimeException('Folder export Word tidak dapat dibuat: ' . $directory);
        }

        $filename = 'kartu_' . Str::slug($card->nama_lengkap ?: 'siswa') . '_' . ($card->nisn ?: 'card') . '.docx';
        $path = $directory . DIRECTORY_SEPARATOR . $filename;

        $processor->saveAs($path);

        if ($photoPath) {
            $this->injectPhotoIntoFotoShape($path, $photoPath);
        }

        return response()->download($path, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ])->deleteFileAfterSend(true);
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

        $source = new \ZipArchive();
        if ($source->open($docxPath) !== true) {
            throw new RuntimeException('Template Word hasil merge tidak dapat dibuka.');
        }

        $documentXml = $source->getFromName('word/document.xml');
        $relsXml = $source->getFromName('word/_rels/document.xml.rels');
        if ($documentXml === false || $relsXml === false) {
            $source->close();
            throw new RuntimeException('Struktur XML template Word tidak lengkap.');
        }

        $rId = $this->nextRelationshipId($relsXml);

        // Add the photo relationship.
        $relsInsertion = '<Relationship Id="' . $rId . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="media/sipena_photo.' . $extension . '"/>';
        $relsXml = preg_replace('/<\/Relationships>/', $relsInsertion . '</Relationships>', $relsXml, 1);

        // In the first WPS shape containing ${foto}, replace the noFill shape background
        // with a blipFill using the student's photo and remove the placeholder text.
        $photoFill = '<a:blipFill><a:blip r:embed="' . $rId . '"/><a:stretch><a:fillRect/></a:stretch></a:blipFill>';

        $pattern = '~(<wps:wsp\b[^>]*>.*?<wps:spPr\b[^>]*>.*?)(<a:noFill\s*/>)(.*?<wps:txbx>.*?<w:t>\$\{foto\}</w:t>.*?</wps:txbx>.*?</wps:wsp>)~s';
        $count = 0;
        $updatedDocument = preg_replace_callback($pattern, function (array $m) use ($photoFill, &$count) {
            if ($count > 0) {
                return $m[0];
            }

            $count++;
            $replacement = $m[1] . $photoFill . $m[3];
            $replacement = preg_replace('/<w:t>\$\{foto\}<\/w:t>/', '<w:t></w:t>', $replacement, 1);
            return $replacement;
        }, $documentXml, 1);

        if ($updatedDocument === null || $count === 0) {
            $source->close();
            throw new RuntimeException('Shape ${foto} tidak ditemukan pada template Word.');
        }

        $tempPath = $docxPath . '.tmp';
        @unlink($tempPath);

        $target = new \ZipArchive();
        if ($target->open($tempPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            $source->close();
            throw new RuntimeException('File Word sementara tidak dapat dibuat.');
        }

        for ($i = 0; $i < $source->numFiles; $i++) {
            $stat = $source->statIndex($i);
            if (! $stat || ! isset($stat['name'])) {
                continue;
            }

            $name = $stat['name'];
            if ($name === 'word/document.xml') {
                $target->addFromString($name, $updatedDocument);
            } elseif ($name === 'word/_rels/document.xml.rels') {
                $target->addFromString($name, $relsXml);
            } elseif ($name === $mediaPath) {
                $target->addFromString($name, $photoBytes);
            } else {
                $bytes = $source->getFromIndex($i);
                if ($bytes !== false) {
                    $target->addFromString($name, $bytes);
                }
            }
        }

        if ($target->locateName($mediaPath) === false) {
            $target->addFromString($mediaPath, $photoBytes);
        }

        $target->close();
        $source->close();

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
