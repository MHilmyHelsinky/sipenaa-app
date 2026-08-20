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
        html, body { margin: 0; min-height: 100%; background: #eef4f9; font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
        body { color: #102a43; }
        .preview-shell { min-height: 100vh; padding: 24px; }
        .preview-panel { width: min(1280px, 100%); min-height: calc(100vh - 48px); margin: 0 auto; background: #fff; border-radius: 18px; box-shadow: 0 18px 45px rgba(15, 23, 42, .12); padding: 18px; display: flex; flex-direction: column; }
        .topbar { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 14px; flex-wrap: wrap; }
        .actions { display: flex; gap: 10px; flex-wrap: wrap; }
        .btn { border-radius: 10px; padding: 10px 14px; text-decoration: none; border: 1px solid #d5dde7; background: #fff; color: #16324f; font-weight: 700; display: inline-flex; align-items: center; gap: 8px; cursor: pointer; }
        .btn-primary { border-color: #1d4ed8; background: #1d4ed8; color: #fff; }
        .btn-success { border-color: #16a34a; background: #16a34a; color: #fff; }
        .btn-danger { border-color: #dc2626; color: #b91c1c; background: #fff; }
        .summary { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 12px 14px; background: #f8fbff; border: 1px solid #e2e8f0; border-radius: 12px; margin-bottom: 12px; flex-wrap: wrap; }
        .summary strong { color: #1d4ed8; }
        .cards-list { display: flex; flex-wrap: wrap; gap: 8px; }
        .chip { display: inline-flex; align-items: center; gap: 7px; padding: 7px 10px; background: #eef4ff; border-radius: 999px; color: #1e3a8a; font-size: .84rem; font-weight: 700; }
        .subtitle { text-align: center; color: #64748b; font-size: .9rem; margin-bottom: 12px; }
        .pdf-wrap { flex: 1; min-height: 0; background: #e9eef3; border: 1px solid #d8e0e8; border-radius: 14px; overflow: hidden; }
        .pdf-frame { width: 100%; height: calc(100vh - 235px); min-height: 650px; border: 0; display: block; background: #fff; }
        @media (max-width: 700px) {
            .preview-shell { padding: 10px; }
            .preview-panel { min-height: calc(100vh - 20px); padding: 12px; border-radius: 12px; }
            .pdf-frame { height: calc(100vh - 270px); min-height: 520px; }
        }
    </style>
</head>
<body>
<div class="preview-shell">
    <div class="preview-panel">
        <div class="topbar">
            <a class="btn" href="{{ route('pembuatan.kartu') }}"><i class="fa-solid fa-arrow-left"></i> Kembali</a>
            <div class="actions">
                <a class="btn btn-success" href="{{ route('pembuatan.kartu') }}"><i class="fa-solid fa-plus"></i> Tambah Kartu</a>
                @if($previewCount === 1)
                    <a class="btn" href="{{ route('preview.kartu.download.word', ['card' => $card->id]) }}"><i class="fa-solid fa-file-word"></i> Word</a>
                    <a class="btn btn-primary" href="{{ route('preview.kartu.download.pdf', ['card' => $card->id]) }}"><i class="fa-solid fa-file-pdf"></i> Download PDF</a>
                @else
                    <a class="btn btn-primary" href="{{ route('preview.kartu.batch.pdf') }}" target="_blank"><i class="fa-solid fa-file-pdf"></i> Buka Preview 1 Lembar</a>
                @endif
            </div>
        </div>

        <div class="summary">
            <div>Daftar kartu saat ini: <strong>{{ $previewCount }} kartu</strong></div>
            <div class="cards-list">
                @foreach($previewCards as $item)
                    <span class="chip"><i class="fa-solid fa-id-card"></i>{{ $item->nisn }} · {{ $item->nama_lengkap }}</span>
                @endforeach
            </div>
            @if($previewCount > 1)
                <form method="POST" action="{{ route('preview.kartu.batch.clear') }}">
                    @csrf
                    <button class="btn btn-danger" type="submit"><i class="fa-solid fa-trash"></i> Mulai Daftar Baru</button>
                </form>
            @endif
        </div>

        @if($previewCount > 1)
            <div class="subtitle">Preview mengikuti susunan pada file Word acuan: semua kartu berada pada satu lembar dan disusun vertikal. File acuan yang Anda kirim berisi lima kartu pada satu halaman.</div>
            <div class="pdf-wrap">
                <iframe class="pdf-frame" src="{{ route('preview.kartu.batch.pdf') }}#toolbar=1&navpanes=0&scrollbar=1" title="Preview kartu dalam satu lembar"></iframe>
            </div>
        @else
            <div class="subtitle">Preview kartu menggunakan template cetak yang sama dengan PDF kartu tunggal.</div>
            <div class="pdf-wrap">
                <iframe class="pdf-frame" src="{{ route('preview.kartu.pdf', ['card' => $card->id]) }}#toolbar=1&navpanes=0&scrollbar=1" title="Preview kartu siswa"></iframe>
            </div>
        @endif
    </div>
</div>
</body>
</html>
