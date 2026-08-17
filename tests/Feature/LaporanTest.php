<?php

namespace Tests\Feature;

use App\Models\Card;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LaporanTest extends TestCase
{
    use RefreshDatabase;

    public function test_laporan_page_displays_report_table_and_download_button(): void
    {
        $user = User::create([
            'name' => 'Admin SIPENA',
            'username' => 'admin-sipena-',
            'password' => Hash::make('password123'),
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        Card::create([
            'nisn' => '1234567890',
            'nama_lengkap' => 'Siti Nurhaliza',
            'tempat_lahir' => 'Bandung',
            'tanggal_lahir' => '2010-08-12',
            'jenis_kelamin' => 'Perempuan',
            'desa' => 'Cibiru',
            'kecamatan' => 'Bandung Kulon',
            'kabupaten' => 'Bandung',
            'printed_at' => now()->subDay(),
        ]);

        $response = $this->actingAs($user)->get('/laporan');

        $response->assertOk();
        $response->assertSee('NISN');
        $response->assertSee('Nama');
        $response->assertSee('Tempat Lahir');
        $response->assertSee('Tanggal Lahir');
        $response->assertSee('Alamat');
        $response->assertSee('Jenis Kelamin');
        $response->assertSee('Keterangan');
        $response->assertSee('Unduh Excel');
    }

    public function test_laporan_excel_export_downloads_xlsx_file(): void
    {
        $user = User::create([
            'name' => 'Admin SIPENA Export',
            'username' => 'admin-sipena-export',
            'password' => Hash::make('password123'),
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        Card::create([
            'nisn' => '1234567890',
            'nama_lengkap' => 'Siti Nurhaliza',
            'tempat_lahir' => 'Bandung',
            'tanggal_lahir' => '2010-08-12',
            'jenis_kelamin' => 'Perempuan',
            'desa' => 'Cibiru',
            'kecamatan' => 'Bandung Kulon',
            'kabupaten' => 'Bandung',
        ]);

        $response = $this->actingAs($user)->get('/laporan/export');

        $response->assertDownload('laporan.xlsx');
        $this->assertSame(
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            $response->headers->get('Content-Type')
        );
    }
}
