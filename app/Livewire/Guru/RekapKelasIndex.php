<?php

namespace App\Livewire\Guru;

use Livewire\Component;
use App\Models\Jadwal;
use App\Models\Absensi;
use App\Models\Siswa;
use App\Models\Kelas;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class RekapKelasIndex extends Component
{
    // --- UI STATE ---
    public $filter_kelas = '';
    public $filter_bulan;
    public $filter_tahun;

    public function mount()
    {
        $this->filter_bulan = date('m');
        $this->filter_tahun = date('Y');
        
        // Default pilih kelas pertama yang diajar guru ini
        $guruId = Auth::user()->guru->id;
        $kelasPertama = Jadwal::where('guru_id', $guruId)->first();
        if ($kelasPertama) {
            $this->filter_kelas = $kelasPertama->kelas_id;
        }
    }

    /**
     * Menyediakan data untuk tampilan
     */
    public function with(): array
    {
        $guruId = Auth::user()->guru->id;

        // 1. Ambil daftar kelas yang hanya diajar oleh guru ini
        $daftarKelas = Kelas::whereHas('jadwals', function($q) use ($guruId) {
            $q->where('guru_id', $guruId);
        })->get();

        // 2. Hitung rekap data jika kelas sudah dipilih
        $rekapData = collect();
        if ($this->filter_kelas) {
            $siswas = Siswa::where('kelas_id', $this->filter_kelas)
                ->orderBy('nama_lengkap', 'asc')
                ->get();

            $rekapData = $siswas->map(function ($siswa) {
                // Ambil semua log absensi siswa ini di bulan/tahun terpilih 
                // khusus untuk mata pelajaran yang diajar guru ini
                $logs = Absensi::where('siswa_id', $siswa->id)
                    ->whereMonth('tanggal', $this->filter_bulan)
                    ->whereYear('tanggal', $this->filter_tahun)
                    ->whereHas('jadwal', function($q) {
                        $q->where('guru_id', Auth::user()->guru->id);
                    })
                    ->get();

                $h = $logs->where('status', 'H')->count();
                $s = $logs->where('status', 'S')->count();
                $i = $logs->where('status', 'I')->count();
                $a = $logs->where('status', 'A')->count();
                $total = $logs->count();

                return [
                    'nama' => $siswa->nama_lengkap,
                    'nisn' => $siswa->nisn,
                    'h' => $h,
                    's' => $s,
                    'i' => $i,
                    'a' => $a,
                    'persen' => $total > 0 ? round(($h / $total) * 100, 1) : 0
                ];
            });
        }

        return [
            'daftarRekap' => $rekapData,
            'daftarKelas' => $daftarKelas,
            'listBulan' => [
                '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
                '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
                '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
            ]
        ];
    }

    public function render()
    {
        return view('livewire.guru.rekap-kelas-index');
    }
}