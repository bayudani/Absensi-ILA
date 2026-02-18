<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KepalaSekolah extends Model
{
    use HasFactory;

    // Definisikan nama tabel secara eksplisit karena formatnya custom (singular/snake_case)
    protected $table = 'kepala_sekolah';

    // Biar gak ribet definisikan fillable satu-satu
    protected $guarded = [];

    /**
     * Relasi ke model User (Akun Login)
     * Kepala Sekolah punya 1 akun login di tabel users
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}