<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    protected $guarded = [];

    public function jadwal()
    {
        return $this->belongsTo(Jadwal::class);
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }
    
    // Scope untuk mempermudah filter rekap
    public function scopeHadir($query)
    {
        return $query->where('status', 'H');
    }
    
    public function scopeAlpha($query)
    {
        return $query->where('status', 'A');
    }
}