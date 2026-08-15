<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kartu Siswa</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #dfeef9;
            color: #111827;
        }
        .page {
            width: 100%;
            min-height: 100vh;
            box-sizing: border-box;
            background: url('{{ asset('images/card-template-bg.jpg') }}') center/cover no-repeat;
            padding: 0;
        }
        .card {
            width: 100%;
            min-height: 100vh;
            position: relative;
            box-sizing: border-box;
            overflow: hidden;
        }
        .content {
            position: absolute;
            inset: 0;
            display: grid;
            grid-template-columns: 38% 62%;
            padding: 2.2rem 1.2rem 1.2rem 1.3rem;
            box-sizing: border-box;
        }
        .left-panel {
            padding-top: 1rem;
            color: #000000;
        }
        .title {
            font-size: 32px;
            line-height: 0.92;
            font-weight: 900;
            letter-spacing: -0.05em;
            margin-bottom: 16px;
            max-width: 280px;
        }
        .row {
            display: flex;
            align-items: center;
            min-height: 42px;
            margin-bottom: 6px;
            font-size: 28px;
            font-weight: 800;
            color: #0f172a;
            border-bottom: 2px solid rgba(15, 23, 42, 0.2);
        }
        .label {
            display: inline-block;
            min-width: 130px;
            font-weight: 900;
            color: #111827;
        }
        .value {
            display: inline;
            word-break: break-word;
        }
        .right-panel {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem 1.2rem 2rem 1rem;
        }
        .logo {
            width: 380px;
            max-width: 90%;
        }
        .logo img {
            width: 100%;
            height: auto;
        }
        .footer-note {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 14px;
            text-align: center;
            font-size: 16px;
            font-style: italic;
            font-weight: 700;
            color: rgba(17, 24, 39, 0.85);
        }
        .footer-note::before,
        .footer-note::after {
            content: "";
            display: inline-block;
            width: 24%;
            height: 2px;
            background: rgba(15, 23, 42, 0.75);
            vertical-align: middle;
            margin: 0 1rem;
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="card">
            <div class="content">
                <div class="left-panel">
                    <div class="title">Kartu<br>Nomor Induk<br>Siswa Nasional</div>
                    <div class="row"><span class="label">NISN</span><span class="value">{{ $card->nisn ?? '-' }}</span></div>
                    <div class="row"><span class="label">Nama</span><span class="value">{{ $card->nama_lengkap ?? '-' }}</span></div>
                    <div class="row"><span class="label">Tempat Lahir</span><span class="value">{{ $card->tempat_lahir ?? '-' }}</span></div>
                    <div class="row"><span class="label">Tgl. Lahir</span><span class="value">{{ optional($card->tanggal_lahir)->format('d F Y') ?? '-' }}</span></div>
                    <div class="row"><span class="label">Alamat</span><span class="value">{{ trim(($card->desa ? $card->desa . ', ' : '') . ($card->kecamatan ? $card->kecamatan . ', ' : '') . ($card->kabupaten ?? '')) ?: '-' }}</span></div>
                    <div class="row"><span class="label">Jenis kelamin</span><span class="value">{{ $card->jenis_kelamin ?? '-' }}</span></div>
                </div>

                <div class="right-panel">
                    <div class="logo"><img src="{{ asset('images/card-template-logo.png') }}" alt="Logo template kartu"></div>
                    <div class="footer-note">hanya berlaku selama pemegang menjadi siswa</div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
