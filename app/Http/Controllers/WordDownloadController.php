<?php

namespace App\Http\Controllers;

use App\Models\Card;
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

        // Masukkan foto SEBELUM TemplateProcessor menyentuh placeholder teks.
        // Placeholder foto berada di WPS shape Rectangle 6.
        $workingTemplate = $this->createTemplateWithPhoto($templatePath, $photoPath);

        try {
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
            if (is_file($workingTemplate)) {
                @unlink($workingTemplate);
            }
        }
    }

    private function createTemplateWithPhoto(string $templatePath, ?string $photoPath): string
    {
        if (! class_exists(\ZipArchive::class)) {
            throw new RuntimeException('PHP extension zip belum tersedia untuk membuat Word.');
        }

        $tempPath = storage_path('app/template_' . uniqid('', true) . '.docx');
        $source = new \ZipArchive();

        if ($source->open($templatePath) !== true) {
            throw new RuntimeException('Template Word tidak dapat dibuka.');
        }

        $target = new \ZipArchive();
        if ($target->open($tempPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            $source->close();
            throw new RuntimeException('Template Word sementara tidak dapat dibuat.');
        }

        $documentXml = $source->getFromName('word/document.xml');
        $relsXml = $source->getFromName('word/_rels/document.xml.rels');

        if ($documentXml === false || $relsXml === false) {
            $target->close();
            $source->close();
            throw new RuntimeException('Struktur XML template Word tidak lengkap.');
        }

        if ($photoPath && is_file($photoPath)) {
            $photoBytes = file_get_contents($photoPath);
            if ($photoBytes === false || $photoBytes === '') {
                $target->close();
                $source->close();
                throw new RuntimeException('Foto siswa tidak dapat dibaca.');
            }

            $mime = mime_content_type($photoPath) ?: 'image/jpeg';
            $extension = match ($mime) {
                'image/png' => 'png',
                'image/gif' => 'gif',
                default => 'jpg',
            };

            $mediaPath = 'word/media/sipena_photo.' . $extension;
            $rId = $this->nextRelationshipId($relsXml);

            $relationship = '<Relationship Id="' . $rId . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="media/sipena_photo.' . $extension . '"/>';
            $relsXml = preg_replace('/<\/Relationships>/', $relationship . '</Relationships>', $relsXml, 1);

            // Template asli memakai mc:Choice -> wps:wsp -> wps:txbx -> w:txbxContent.
            // Versi sebelumnya mencari langsung wps:txbx sehingga shape tidak pernah ketemu.
            $photoFill = '<a:blipFill><a:blip r:embed="' . $rId . '"/><a:stretch><a:fillRect/></a:stretch></a:blipFill>';

            $pattern = '~(<wps:wsp\b[^>]*>.*?<wps:spPr\b[^>]*>.*?)(<a:noFill\s*/>)(.*?<wps:txbx>.*?<wps:txbxContent>.*?<w:t>\$\{foto\}</w:t>.*?</wps:txbxContent>.*?</wps:txbx>.*?</wps:wsp>)~s';

            $count = 0;
            $documentXml = preg_replace_callback($pattern, function (array $m) use ($photoFill, &$count) {
                if ($count > 0) {
                    return $m[0];
                }

                $count++;
                $replacement = $m[1] . $photoFill . $m[3];
                $replacement = preg_replace('/<w:t>\$\{foto\}<\/w:t>/', '<w:t></w:t>', $replacement, 1);
                return $replacement;
            }, $documentXml, 1);

            if ($documentXml === null || $count === 0) {
                $target->close();
                $source->close();
                @unlink($tempPath);
                throw new RuntimeException('Shape ${foto} pada Rectangle 6 tidak ditemukan pada template Word.');
            }

            // Copy all original files and replace the patched XML/rels.
            for ($i = 0; $i < $source->numFiles; $i++) {
                $stat = $source->statIndex($i);
                if (! $stat || ! isset($stat['name'])) {
                    continue;
                }

                $name = $stat['name'];
                if ($name === 'word/document.xml') {
                    $target->addFromString($name, $documentXml);
                } elseif ($name === 'word/_rels/document.xml.rels') {
                    $target->addFromString($name, $relsXml);
                } elseif ($name !== $mediaPath) {
                    $bytes = $source->getFromIndex($i);
                    if ($bytes !== false) {
                        $target->addFromString($name, $bytes);
                    }
                }
            }

            $target->addFromString($mediaPath, $photoBytes);
        } else {
            for ($i = 0; $i < $source->numFiles; $i++) {
                $bytes = $source->getFromIndex($i);
                $stat = $source->statIndex($i);
                if ($bytes !== false && $stat && isset($stat['name'])) {
                    $target->addFromString($stat['name'], $bytes);
                }
            }
        }

        $target->close();
        $source->close();

        return $tempPath;
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
