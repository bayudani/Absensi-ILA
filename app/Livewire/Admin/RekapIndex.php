<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Absensi;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RekapIndex extends Component
{
    // --- UI STATE ---
    public $filter_kelas = '';
    public $filter_bulan;
    public $filter_tahun;

    public function mount()
    {
        // Default filter ke bulan dan tahun sekarang
        $this->filter_bulan = date('m');
        $this->filter_tahun = date('Y');
    }

    /**
     * Menghitung rekapitulasi data absensi per siswa
     */
    public function with(): array
    {
        $querySiswa = Siswa::with('kelas')
            ->when($this->filter_kelas, function($q) {
                $q->where('kelas_id', $this->filter_kelas);
            });

        $rekapData = $querySiswa->get()->map(function ($siswa) {
            // Ambil log absensi siswa pada bulan & tahun terpilih
            $logs = Absensi::where('siswa_id', $siswa->id)
                ->whereMonth('tanggal', $this->filter_bulan)
                ->whereYear('tanggal', $this->filter_tahun)
                ->get();

            $h = $logs->where('status', 'H')->count();
            $s = $logs->where('status', 'S')->count();
            $i = $logs->where('status', 'I')->count();
            $a = $logs->where('status', 'A')->count();
            $total = $logs->count();

            return [
                'nama' => $siswa->nama_lengkap,
                'nisn' => $siswa->nisn,
                'kelas' => $siswa->kelas->nama_kelas,
                'h' => $h,
                's' => $s,
                'i' => $i,
                'a' => $a,
                'persen' => $total > 0 ? round(($h / $total) * 100, 1) : 0
            ];
        });

        return [
            'daftarRekap' => $rekapData,
            'daftarKelas' => Kelas::all(),
            'listBulan' => [
                '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
                '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
                '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
            ]
        ];
    }

    public function render()
    {
        return view('livewire.admin.rekap-index');
    }
}