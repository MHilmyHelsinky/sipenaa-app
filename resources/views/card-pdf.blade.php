<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kartu Siswa</title>
    <style>
        @page { size: 200mm 300mm; margin: 0; }
        html, body { margin: 0; padding: 0; width: 200mm; height: 300mm; }
        body { font-family: Arial, Helvetica, sans-serif; background: #fff; }
        .page {
            position: relative;
            width: 200mm;
            height: 300mm;
            overflow: hidden;
        }
        .template {
            position: absolute;
            left: 0;
            top: 0;
            width: 200mm;
            height: 300mm;
        }
        .field {
            position: absolute;
            left: 102pt;
            display: block;
            min-height: 11pt;
            padding: 0 2pt;
            background: #fff;
            color: #000;
            font-size: 10pt;
            line-height: 11pt;
            font-weight: 700;
            white-space: nowrap;
            overflow: hidden;
        }
        .nisn { top: 16pt; width: 145pt; }
        .nama { top: 31pt; width: 145pt; }
        .tempat { top: 46pt; width: 170pt; }
        .tgl { top: 61pt; width: 145pt; }
        .alamat { top: 76pt; width: 175pt; white-space: normal; height: 22pt; }
        .jenis { top: 91pt; width: 160pt; }
        .photo-box {
            position: absolute;
            left: 27pt;
            top: 98pt;
            width: 82pt;
            height: 91pt;
            overflow: hidden;
            background: #67c0de;
        }
        .photo-box img {
            width: 82pt;
            height: 91pt;
            object-fit: cover;
            display: block;
        }
    </style>
</head>
<body>
<div class="page">
    <img class="template" src="{{ $templateBackgroundDataUri }}" alt="">

    <div class="field nisn">{{ $templateValues['nisn'] ?? '-' }}</div>
    <div class="field nama">{{ $templateValues['nama'] ?? '-' }}</div>
    <div class="field tempat">{{ $templateValues['tempat_lahir'] ?? '-' }}</div>
    <div class="field tgl">{{ $templateValues['tgl_lahir'] ?? '-' }}</div>
    <div class="field alamat">{{ $templateValues['alamat'] ?? '-' }}</div>
    <div class="field jenis">{{ $templateValues['jenis_kelamin'] ?? '-' }}</div>

    <div class="photo-box">
        @if($photoDataUri)
            <img src="{{ $photoDataUri }}" alt="Foto siswa">
        @endif
    </div>
</div>
</body>
</html>
