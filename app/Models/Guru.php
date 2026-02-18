<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Guru extends Model
{
    protected $guarded = [];

    // Relasi ke Akun Login
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke Kelas (Jika dia Walas)
    public function kelasBinaan()
    {
        return $this->hasOne(Kelas::class, 'wali_kelas_id');
    }

    // Relasi ke Jadwal Mengajar
    public function jadwals()
    {
        return $this->hasMany(Jadwal::class);
    }
}