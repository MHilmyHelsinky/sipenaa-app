<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\User;
use App\Services\CardPdfService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use PhpOffice\PhpWord\TemplateProcessor;
use RuntimeException;

class DashboardController extends Controller
{
    public function __construct(private readonly CardPdfService $cardPdfService)
    {
    }

    protected function authorizeSuperAdmin(): void
    {
        abort_if(Auth::user()?->role !== 'super_admin', 403);
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        $localNow = Carbon::now('Asia/Jakarta');

        if ($user->role === 'user') {
            $totalCards = Card::count();
            $printedCards = Card::whereNotNull('printed_at')->count();
            $notPrintedCards = max(0, $totalCards - $printedCards);
            $printedToday = Card::whereNotNull('printed_at')
                ->whereDate('printed_at', $localNow->toDateString())
                ->count();

            $inputTodayData = Card::query()
                ->whereDate('created_at', $localNow->toDateString())
                ->latest('created_at')
                ->get()
                ->map(fn (Card $card) => [
                    'nisn' => $card->nisn,
                    'nama_lengkap' => $card->nama_lengkap,
                    'tempat_lahir' => $card->tempat_lahir,
                    'tanggal_lahir' => optional($card->tanggal_lahir)->format('d-m-Y') ?? '-',
                    'jenis_kelamin' => $card->jenis_kelamin,
                    'waktu_input' => $card->created_at
                        ? $card->created_at->timezone('Asia/Jakarta')->format('H:i') . ' WIB'
                        : '-',
                ])
                ->values();

            $months = collect(range(5, 0))->map(function (int $monthsAgo) use ($localNow) {
                $date = $localNow->copy()->startOfMonth()->subMonths($monthsAgo);
                $next = $date->copy()->addMonth();

                return [
                    'label' => $date->locale('id')->translatedFormat('M'),
                    'full_label' => $date->locale('id')->translatedFormat('F'),
                    'count' => Card::whereNotNull('printed_at')
                        ->where('printed_at', '>=', $date->copy()->setTimezone('UTC'))
                        ->where('printed_at', '<', $next->copy()->setTimezone('UTC'))
                        ->count(),
                ];
            })->values();

            return view('dashboard-user', [
                'nisnTerdaftar' => $totalCards,
                'sudahCetak' => $printedCards,
                'belumCetak' => $notPrintedCards,
                'cetakHariIni' => $printedToday,
                'inputTodayCount' => $inputTodayData->count(),
                'inputTodayData' => $inputTodayData,
                'printChart' => $months,
                'printChartMax' => max(1, (int) $months->max('count')),
                'currentDate' => $localNow->locale('id')->isoFormat('dddd, D MMMM YYYY'),
                'currentTime' => $localNow->format('H:i'),
            ]);
        }

        $totalPetugas = User::where('role', 'user')->count();
        $inputToday = Card::whereDate('created_at', $localNow->toDateString())->count();
        $activeToday = User::where('role', 'user')->where('is_active', true)->whereDate('last_login_at', $localNow->toDateString())->count();
        $activeUsers = User::where('role', 'user')->where('is_active', true)->orderBy('name')->get();

        return view('dashboard', [
            'totalPetugas' => $totalPetugas,
            'inputToday' => $inputToday,
            'activeToday' => $activeToday,
            'activeUsers' => $activeUsers,
        ]);
    }

    public function aktivitasInput(): View
    {
        return view('aktivitas-input');
    }

    public function pembuatanKartu(): View
    {
        return view('pembuatan-kartu');
    }

    public function storeCard(Request $request)
    {
        $validated = $request->validate([
            'nisn' => ['required', 'string', 'max:50', 'unique:cards,nisn'],
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'tempat_lahir' => ['required', 'string', 'max:255'],
            'tanggal_lahir' => ['required', 'date'],
            'jenis_kelamin' => ['required', 'in:Laki-laki,Perempuan'],
            'desa' => ['nullable', 'string', 'max:255'],
            'kecamatan' => ['nullable', 'string', 'max:255'],
            'kabupaten' => ['nullable', 'string', 'max:255'],
            'foto' => ['nullable', 'image', 'max:2048'],
        ]);

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = Storage::disk('public')->putFile('card_photos', $request->file('foto'));
        }

        $card = Card::create([
            'nisn' => $validated['nisn'],
            'nama_lengkap' => $validated['nama_lengkap'],
            'tempat_lahir' => $validated['tempat_lahir'],
            'tanggal_lahir' => $validated['tanggal_lahir'],
            'jenis_kelamin' => $validated['jenis_kelamin'],
            'desa' => $validated['desa'] ?? null,
            'kecamatan' => $validated['kecamatan'] ?? null,
            'kabupaten' => $validated['kabupaten'] ?? null,
            'foto_path' => $fotoPath,
        ]);

        return redirect()->route('preview.kartu', ['card' => $card->id])
            ->with('success', 'Kartu siswa berhasil disimpan ke database.');
    }

    protected function getTemplateValues(Card $card): array
    {
        $alamat = trim(
            ($card->desa ? $card->desa . ', ' : '') .
            ($card->kecamatan ? $card->kecamatan . ', ' : '') .
            ($card->kabupaten ?? '')
        );

        $fotoPath = null;
        if ($card->foto_path && Storage::disk('public')->exists($card->foto_path)) {
            $fotoPath = Storage::disk('public')->path($card->foto_path);
        }

        return [
            'nama' => $card->nama_lengkap ?? '-',
            'nisn' => $card->nisn ?? '-',
            'tempat_lahir' => $card->tempat_lahir ?? '-',
            'tgl_lahir' => optional($card->tanggal_lahir)->format('d-m-Y') ?? '-',
            'jenis_kelamin' => $card->jenis_kelamin ?? '-',
            'alamat' => $alamat ?: '-',
            'foto' => $fotoPath,
        ];
    }

    protected function generateTemplateDocument(Card $card): string
    {
        $templatePath = storage_path('app/templates/merge_nisn_2020.docx');

        if (! file_exists($templatePath)) {
            abort(500, 'Template DOCX tidak ditemukan di storage/app/templates/merge_nisn_2020.docx');
        }

        $processor = new TemplateProcessor($templatePath);
        $values = $this->getTemplateValues($card);

        foreach ($values as $key => $value) {
            if ($key === 'foto') {
                if ($value) {
                    $processor->setImageValue('${foto}', [
                        'path' => $value,
                        'width' => 82,
                        'height' => 91,
                        'ratio' => false,
                    ]);
                } else {
                    $processor->setValue('${foto}', '');
                }
                continue;
            }

            $processor->setValue('${' . $key . '}', (string) $value);
        }

        $directory = storage_path('app/public/card_exports');
        if (! is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        $filename = 'kartu_' . Str::slug($card->nama_lengkap ?: 'siswa') . '_' . ($card->nisn ?: 'card') . '.docx';
        $path = $directory . DIRECTORY_SEPARATOR . $filename;
        $processor->saveAs($path);

        return $path;
    }

    public function previewKartu(Card $card): View
    {
        return view('preview-kartu', ['card' => $card]);
    }

    public function previewPdf(Card $card)
    {
        $path = $this->renderPdf($card);
        $filename = 'kartu_' . Str::slug($card->nama_lengkap ?: 'siswa') . '_' . ($card->nisn ?: 'card') . '.pdf';

        return response()->file($path, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }

    public function downloadPdf(Card $card)
    {
        $path = $this->renderPdf($card);
        $card->forceFill(['printed_at' => now()])->save();
        $filename = 'kartu_' . Str::slug($card->nama_lengkap ?: 'siswa') . '_' . ($card->nisn ?: 'card') . '.pdf';

        return response()->download($path, $filename, [
            'Content-Type' => 'application/pdf',
        ])->deleteFileAfterSend(true);
    }

    protected function renderPdf(Card $card): string
    {
        try {
            return $this->cardPdfService->render($card);
        } catch (RuntimeException $e) {
            abort(500, $e->getMessage());
        }
    }

    public function downloadWord(Card $card)
    {
        $response = app(WordDownloadControllerFixed::class)->download($card);
        $card->forceFill(['printed_at' => now()])->save();
        return $response;
    }

    public function dataKartu(): View
    {
        return view('data-kartu', ['cards' => Card::latest()->get()]);
    }

    public function laporan(): View
    {
        return view('laporan');
    }

    public function manageUsers(Request $request): View
    {
        $this->authorizeSuperAdmin();
        $users = User::orderByRaw("role = 'super_admin' desc")->orderBy('name')->get();
        return view('manajemen-pengguna', ['users' => $users]);
    }

    protected function generateUniqueUsername(string $name): string
    {
        $base = Str::slug($name, '_');
        $username = $base ?: 'user';
        $candidate = $username;
        $count = 1;

        while (User::where('username', $candidate)->exists()) {
            $candidate = $username . $count;
            $count++;
        }

        return $candidate;
    }

    public function storeUser(Request $request)
    {
        $this->authorizeSuperAdmin();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'confirmed', 'min:8'],
            'role' => ['required', 'in:super_admin,user'],
        ]);

        $username = $this->generateUniqueUsername($validated['name']);
        User::create([
            'name' => $validated['name'],
            'username' => $username,
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'is_active' => true,
        ]);

        return back()->with('success', 'Akun baru berhasil dibuat. Username otomatis: ' . $username);
    }

    public function toggleUser(User $user)
    {
        $this->authorizeSuperAdmin();
        $user->update(['is_active' => ! $user->is_active]);
        return back()->with('success', 'Status akun berhasil diperbarui.');
    }

    public function destroyUser(User $user)
    {
        $this->authorizeSuperAdmin();
        $user->delete();
        return back()->with('success', 'Akun berhasil dihapus.');
    }
}
