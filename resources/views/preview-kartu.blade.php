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
        .preview-panel { width: min(1200px, 100%); min-height: calc(100vh - 48px); margin: 0 auto; background: #fff; border-radius: 18px; box-shadow: 0 18px 45px rgba(15, 23, 42, .12); padding: 18px; display: flex; flex-direction: column; }
        .topbar { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 14px; }
        .back-button, .word-btn, .pdf-btn { border-radius: 10px; padding: 10px 14px; text-decoration: none; border: 1px solid #d5dde7; background: #fff; color: #16324f; font-weight: 700; display: inline-flex; align-items: center; gap: 8px; cursor: pointer; }
        .actions { display: flex; gap: 10px; }
        .pdf-btn { border-color: #1d4ed8; background: #1d4ed8; color: #fff; }
        .subtitle { text-align: center; color: #64748b; font-size: .9rem; margin-bottom: 12px; }
        .pdf-wrap { flex: 1; min-height: 0; background: #e9eef3; border: 1px solid #d8e0e8; border-radius: 14px; overflow: hidden; }
        .pdf-frame { width: 100%; height: calc(100vh - 170px); min-height: 650px; border: 0; display: block; background: #fff; }
        @media (max-width: 700px) {
            .preview-shell { padding: 10px; }
            .preview-panel { min-height: calc(100vh - 20px); padding: 12px; border-radius: 12px; }
            .topbar { align-items: flex-start; flex-direction: column; }
            .actions { width: 100%; }
            .actions a { flex: 1; justify-content: center; }
            .pdf-frame { height: calc(100vh - 150px); min-height: 520px; }
        }
    </style>
</head>
<body>
    <div class="preview-shell">
        <div class="preview-panel">
            <div class="topbar">
                <a class="back-button" href="javascript:history.back()"><i class="fa-solid fa-arrow-left"></i> Kembali</a>
                <div class="actions">
                    <a class="word-btn" href="{{ route('preview.kartu.download.word', ['card' => $card->id]) }}"><i class="fa-solid fa-file-word"></i> Word</a>
                    <a class="pdf-btn" href="{{ route('preview.kartu.download.pdf', ['card' => $card->id]) }}"><i class="fa-solid fa-file-pdf"></i> Download PDF</a>
                </div>
            </div>
            <div class="subtitle">Preview menggunakan template cetak yang sama dengan file PDF yang akan diunduh dan dicetak.</div>
            <div class="pdf-wrap">
                <iframe
                    class="pdf-frame"
                    src="{{ route('preview.kartu.pdf', ['card' => $card->id]) }}#toolbar=1&navpanes=0&scrollbar=1"
                    title="Preview kartu siswa"
                ></iframe>
            </div>
        </div>
    </div>
</body>
</html>
