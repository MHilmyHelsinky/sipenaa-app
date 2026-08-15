<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview Kartu - SIPENA</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; }
        html, body { margin: 0; min-height: 100%; background: #dfeefb; font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
        body { color: #102a43; }
        .preview-shell {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        .preview-panel {
            width: min(1100px, 100%);
            background: #f8fbff;
            border-radius: 1.25rem;
            box-shadow: 0 22px 50px rgba(15, 23, 42, 0.12);
            padding: 1.5rem 1.5rem 1rem;
        }
        .top-actions {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            margin-bottom: 1rem;
        }
        .back-button {
            width: 42px;
            height: 42px;
            border: 1px solid #cbd5e1;
            background: #ffffff;
            color: #0f172a;
            border-radius: 0.75rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            text-decoration: none;
            cursor: pointer;
        }
        .cards-wrap {
            display: flex;
            justify-content: center;
            margin-top: 0.5rem;
        }
        .card-ui {
            position: relative;
            width: min(1000px, 100%);
            min-height: 620px;
            overflow: hidden;
            background: url('{{ asset('images/card-template-bg.jpg') }}') center/cover no-repeat;
            box-shadow: 0 15px 35px rgba(15, 23, 42, 0.18);
        }
        .word-template {
            position: relative;
            z-index: 1;
            display: grid;
            grid-template-columns: 38% 62%;
            min-height: 620px;
            background: transparent;
        }
        .blue-pane {
            background: transparent;
            position: relative;
            padding: 2.2rem 1.2rem 1rem;
            overflow: hidden;
        }
        .blue-pane-content {
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            gap: 0.8rem;
            padding-top: 1.2rem;
        }
        .title-block {
            font-size: clamp(2.2rem, 3vw, 4.2rem);
            line-height: 0.92;
            font-weight: 900;
            letter-spacing: -0.04em;
            color: #000000;
            text-transform: none;
            max-width: 300px;
            margin-bottom: 0.8rem;
        }
        .field-list {
            display: flex;
            flex-direction: column;
            gap: 0.45rem;
            margin-top: 0.2rem;
        }
        .field-row {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            min-height: 48px;
            font-size: clamp(1.2rem, 1.5vw, 2rem);
            font-weight: 800;
            color: #0f172a;
            border-bottom: 2px solid rgba(15, 23, 42, 0.18);
        }
        .field-row:last-child {
            border-bottom: 0;
        }
        .field-label {
            min-width: 120px;
            font-weight: 900;
        }
        .field-value {
            font-weight: 800;
            word-break: break-word;
        }
        .white-pane {
            background: rgba(255,255,255,0.9);
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.2rem 1.2rem 1rem;
            min-height: 620px;
        }
        .logo-mark {
            width: 420px;
            max-width: 90%;
            opacity: 0.9;
            filter: drop-shadow(0 6px 10px rgba(15, 23, 42, 0.08));
        }
        .logo-mark img {
            width: 100%;
            height: auto;
            display: block;
            opacity: 0.95;
        }
        .watermark-line {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 1.8rem;
            text-align: center;
            font-size: clamp(1.2rem, 1.45vw, 2rem);
            font-style: italic;
            font-weight: 700;
            color: rgba(17, 24, 39, 0.9);
            letter-spacing: 0.02em;
        }
        .watermark-line::before,
        .watermark-line::after {
            content: "";
            display: inline-block;
            width: 26%;
            height: 2px;
            background: rgba(15, 23, 42, 0.8);
            vertical-align: middle;
            margin: 0 1rem;
        }
        .action-row {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 0.8rem;
            margin-top: 1.15rem;
            padding-top: 0.4rem;
        }
        .icon-button {
            width: 46px;
            height: 46px;
            border-radius: 0.8rem;
            border: 1px solid #cbd5e1;
            background: #ffffff;
            color: #1d4ed8;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            cursor: pointer;
        }
        .print-btn {
            min-width: 130px;
            border: none;
            border-radius: 0.9rem;
            padding: 0.9rem 1.2rem;
            background: #1d4ed8;
            color: #ffffff;
            font-weight: 700;
            cursor: pointer;
        }
        .word-btn {
            min-width: 130px;
            border: 1px solid #cbd5e1;
            border-radius: 0.9rem;
            padding: 0.9rem 1.2rem;
            background: #ffffff;
            color: #1d4ed8;
            font-weight: 700;
            text-decoration: none;
            display: inline-flex;
            justify-content: center;
            align-items: center;
        }
        @media (max-width: 760px) {
            .preview-shell { padding: 1rem; }
            .card-front { grid-template-columns: 1fr; }
            .photo-box { justify-content: flex-start; }
            .card-ui { width: 100%; }
            .action-row { justify-content: center; }
        }
        @media print {
            body { background: #ffffff; }
            .no-print { display: none !important; }
            .preview-shell { padding: 0; }
            .preview-panel { box-shadow: none; background: #ffffff; border: none; }
            .cards-wrap { gap: 0; }
            .card-ui { box-shadow: none; border: 1px solid #000; }
        }
    </style>
</head>
<body>
    <div class="preview-shell">
        <div class="preview-panel">
            <div class="top-actions no-print">
                <button class="back-button" type="button" onclick="history.back();">&#8249;</button>
            </div>

            <div style="margin-bottom: 0.9rem; color: #475569; font-size: 0.88rem; font-weight: 600; text-align: center;">
                Preview mengikuti template DOCX: storage/app/templates/merge_nisn_2020.docx
            </div>

            <div class="cards-wrap">
                <div class="card-ui">
                    <div class="word-template">
                        <div class="blue-pane">
                            <div class="blue-pane-content">
                                <div class="title-block">Kartu<br>Nomor Induk<br>Siswa Nasional</div>
                                <div class="field-list">
                                    <div class="field-row"><span class="field-label">NISN</span><span class="field-value">{{ $card->nisn ?? '-' }}</span></div>
                                    <div class="field-row"><span class="field-label">Nama</span><span class="field-value">{{ $card->nama_lengkap ?? '-' }}</span></div>
                                    <div class="field-row"><span class="field-label">Tempat Lahir</span><span class="field-value">{{ $card->tempat_lahir ?? '-' }}</span></div>
                                    <div class="field-row"><span class="field-label">Tgl. Lahir</span><span class="field-value">{{ optional($card->tanggal_lahir)->format('d F Y') ?? '-' }}</span></div>
                                    <div class="field-row"><span class="field-label">Alamat</span><span class="field-value">{{ trim(($card->desa ? $card->desa . ', ' : '') . ($card->kecamatan ? $card->kecamatan . ', ' : '') . ($card->kabupaten ?? '')) ?: '-' }}</span></div>
                                    <div class="field-row"><span class="field-label">Jenis kelamin</span><span class="field-value">{{ $card->jenis_kelamin ?? '-' }}</span></div>
                                </div>
                            </div>
                        </div>

                        <div class="white-pane">
                            <div class="logo-mark">
                                <img src="{{ asset('images/card-template-logo.png') }}" alt="Logo template kartu">
                            </div>
                            <div class="watermark-line">hanya berlaku selama pemegang menjadi siswa</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="action-row no-print">
                <a class="icon-button" href="{{ route('preview.kartu.download.word', ['card' => $card->id]) }}" title="Download Word" download>
                    <i class="fa-solid fa-file-word"></i>
                </a>
                <a class="print-btn" href="{{ route('preview.kartu.download.pdf', ['card' => $card->id]) }}" download>PDF</a>
            </div>
        </div>
    </div>
</body>
</html>
