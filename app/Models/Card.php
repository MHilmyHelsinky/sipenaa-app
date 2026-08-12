<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['nisn', 'nama_lengkap', 'tempat_lahir', 'tanggal_lahir', 'jenis_kelamin', 'desa', 'kecamatan', 'kabupaten', 'foto_path'])]
class Card extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
        ];
    }
}
