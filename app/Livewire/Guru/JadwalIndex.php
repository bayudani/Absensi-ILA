<?php

namespace App\Livewire\Guru;

use Livewire\Component;
use App\Models\Jadwal;
use Illuminate\Support\Facades\Auth;

class JadwalIndex extends Component
{
    /**
     * Mengambil data jadwal mengajar khusus guru yang sedang login.
     */
    public function with(): array
    {
        $user = Auth::user();
        
        // Pastikan user memiliki profil guru
        if (!$user->guru) {
            return ['daftarJadwal' => collect([])];
        }

        return [
            'daftarJadwal' => Jadwal::with(['kelas', 'mapel'])
                ->where('guru_id', $user->guru->id)
                ->orderByRaw("FIELD(hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu')")
                ->orderBy('jam_mulai')
                ->get()
                ->groupBy('hari'), // Dikelompokkan per hari agar enak dibaca
            'listHari' => ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']
        ];
    }

    public function render()
    {
        return view('livewire.guru.jadwal-index');
    }
}