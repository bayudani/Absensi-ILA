<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ortu extends Model
{
    protected $guarded = [];

    // Relasi: Data Ortu milik satu Siswa
    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }
}