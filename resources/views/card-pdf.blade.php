<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kartu Siswa</title>
    <style>
        @page {
            size: 200mm 300mm;
            margin: 0;
        }

        html, body {
            margin: 0;
            padding: 0;
            width: 200mm;
            height: 300mm;
            background: #fff;
            font-family: "Franklin Gothic Medium", "Arial Narrow", Arial, sans-serif;
        }

        * { box-sizing: border-box; }

        .page {
            position: relative;
            width: 200mm;
            height: 300mm;
            overflow: hidden;
        }

        /* Dua gambar asli yang memang dipakai oleh template Word */
        .left-card,
        .right-card {
            position: absolute;
            top: 0;
            width: 100mm;
            height: 60mm;
        }

        .left-card { left: 0; }
        .right-card { left: 100mm; }

        .card-bg {
            position: absolute;
            inset: 0;
            width: 100mm;
            height: 60mm;
            object-fit: fill;
        }

        /* Nilai dinamis diletakkan tepat di atas baris yang sudah ada pada image6.jpg */
        .value {
            position: absolute;
            left: 35mm;
            color: #000;
            font-family: "Franklin Gothic Medium", Arial, sans-serif;
            font-size: 6.5pt;
            line-height: 2.45mm;
            font-weight: 700;
            white-space: nowrap;
            max-width: 58mm;
            overflow: hidden;
        }

        .v-nisn   { top: 2.35mm; }
        .v-nama   { top: 4.80mm; }
        .v-tempat { top: 7.25mm; }
        .v-tgl    { top: 9.70mm; }
        .v-alamat {
            top: 12.15mm;
            width: 58mm;
            white-space: normal;
            line-height: 2.25mm;
            max-height: 4.5mm;
        }
        .v-jk     { top: 14.60mm; }

        /* Komponen asli dari dokumen Word: stempel dan tanda tangan */
        .stamp {
            position: absolute;
            left: 25mm;
            top: 31.5mm;
            width: 13.5mm;
            height: 13.7mm;
            opacity: .98;
        }

        .signature {
            position: absolute;
            left: 35.8mm;
            top: 40.2mm;
            width: 25mm;
            height: auto;
        }

        /* Blok teks asli yang ada pada template Word */
        .official {
            position: absolute;
            left: 43mm;
            top: 31.3mm;
            width: 51mm;
            color: #000;
            font-family: Arial, Helvetica, sans-serif;
            text-align: center;
        }

        .official .date {
            font-size: 6.5pt;
            line-height: 3mm;
            font-weight: 700;
        }

        .official .department {
            margin-top: 0.2mm;
            font-size: 6.2pt;
            line-height: 2.7mm;
            font-weight: 700;
        }

        .official .name {
            margin-top: 3.3mm;
            font-size: 6.4pt;
            line-height: 3mm;
            font-weight: 700;
        }

        .official .nip {
            font-size: 6.1pt;
            line-height: 2.7mm;
            font-weight: 700;
        }

        /*
         * Ukuran mengikuti Rectangle 6 pada DOCX:
         * 779145 x 866140 EMU ≈ 21.67 x 24.09 mm.
         */
        .photo {
            position: absolute;
            left: 7.4mm;
            top: 29.5mm;
            width: 21.67mm;
            height: 24.09mm;
            overflow: hidden;
            background: transparent;
        }

        .photo img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
    </style>
</head>
<body>
@php
    /*
     * Jangan memakai screenshot/template PDF penuh sebagai background.
     * Ambil kembali gambar-gambar asli yang memang tertanam di DOCX:
     * image6.jpg  = panel kiri + label/baris
     * image1.jpeg = kartu kanan
     * image2.png  = stempel
     * image3.png  = tanda tangan
     */
    $docxPath = storage_path('app/templates/merge_nisn_2020.docx');
    $media = [];

    if (is_file($docxPath) && class_exists(\ZipArchive::class)) {
        $zip = new \ZipArchive();

        if ($zip->open($docxPath) === true) {
            foreach ([
                'left' => 'word/media/image6.jpg',
                'right' => 'word/media/image1.jpeg',
                'stamp' => 'word/media/image2.png',
                'signature' => 'word/media/image3.png',
            ] as $key => $entry) {
                $bytes = $zip->getFromName($entry);

                if ($bytes !== false) {
                    $mime = match (strtolower(pathinfo($entry, PATHINFO_EXTENSION))) {
                        'jpg', 'jpeg' => 'image/jpeg',
                        'png' => 'image/png',
                        default => 'application/octet-stream',
                    };

                    $media[$key] = 'data:' . $mime . ';base64,' . base64_encode($bytes);
                }
            }

            $zip->close();
        }
    }

    $leftImage = $media['left'] ?? null;
    $rightImage = $media['right'] ?? null;
    $stampImage = $media['stamp'] ?? null;
    $signatureImage = $media['signature'] ?? null;

    $photo = $photoDataUri ?? null;
    $values = $templateValues ?? [];
@endphp

<div class="page">
    <div class="left-card">
        @if($leftImage)
            <img class="card-bg" src="{{ $leftImage }}" alt="">
        @endif

        @if($values['nisn'] ?? null)
            <div class="value v-nisn">{{ $values['nisn'] }}</div>
        @endif

        @if($values['nama'] ?? null)
            <div class="value v-nama">{{ $values['nama'] }}</div>
        @endif

        @if($values['tempat_lahir'] ?? null)
            <div class="value v-tempat">{{ $values['tempat_lahir'] }}</div>
        @endif

        @if($values['tgl_lahir'] ?? null)
            <div class="value v-tgl">{{ $values['tgl_lahir'] }}</div>
        @endif

        @if($values['alamat'] ?? null)
            <div class="value v-alamat">{{ $values['alamat'] }}</div>
        @endif

        @if($values['jenis_kelamin'] ?? null)
            <div class="value v-jk">{{ $values['jenis_kelamin'] }}</div>
        @endif

        @if($stampImage)
            <img class="stamp" src="{{ $stampImage }}" alt="">
        @endif

        @if($signatureImage)
            <img class="signature" src="{{ $signatureImage }}" alt="">
        @endif

        <div class="official">
            <div class="date">Banda Aceh, 14 Juli 2026</div>
            <div class="department">
                KEPALA DINAS PENDIDIKAN DAN<br>
                KEBUDAYAAN KOTA BANDA ACEH
            </div>
            <div class="name">SULAIMAN BAKRI, S.Pd., M.Pd.</div>
            <div class="nip">NIP. 196902101998011001</div>
        </div>

        @if($photo)
            <div class="photo">
                <img src="{{ $photo }}" alt="Foto siswa">
            </div>
        @endif
    </div>

    <div class="right-card">
        @if($rightImage)
            <img class="card-bg" src="{{ $rightImage }}" alt="">
        @endif
    </div>
</div>
</body>
</html>