<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mapel extends Model
{
    // Karena nama tabel di migration 'mapel' (bukan plural 'mapels'), kita definisikan manual
    protected $table = 'mapel';
    
    protected $guarded = [];

    // Relasi: Satu Mapel bisa ada di banyak Jadwal
    public function jadwals()
    {
        return $this->hasMany(Jadwal::class);
    }
}