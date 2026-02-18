<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Siswa extends Model
{
    protected $guarded = [];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function ortu()
    {
        return $this->hasOne(Ortu::class);
    }

    public function absensis()
    {
        return $this->hasMany(Absensi::class);
    }
}