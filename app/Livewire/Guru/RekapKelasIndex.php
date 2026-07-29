<?php

namespace App\Livewire\Guru;

use Livewire\Component;
use App\Models\Jadwal;
use App\Models\Absensi;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\KepalaSekolah; 
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class RekapKelasIndex extends Component
{
    // --- UI STATE ---
    public $filter_kelas = '';
    public $filter_mapel = '';
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
            $this->autoSelectMapel();
        }
    }

    public function updatedFilterKelas()
    {
        $this->filter_mapel = '';
        $this->autoSelectMapel();
    }

    private function autoSelectMapel()
    {
        if (!$this->filter_kelas) return;

        $guruId = Auth::user()->guru->id;
        $mapels = Jadwal::where('guru_id', $guruId)
            ->where('kelas_id', $this->filter_kelas)
            ->pluck('mapel_id')
            ->unique();

        if ($mapels->count() === 1) {
            $this->filter_mapel = $mapels->first();
        }
    }

    /**
     * Helper buat ngambil data (dipisah biar bisa dipake view & export)
     */
    private function fetchRekapData()
    {
        if (!$this->filter_kelas || !$this->filter_mapel) {
            return collect();
        }

        $siswas = Siswa::where('kelas_id', $this->filter_kelas)
            ->orderBy('nama_lengkap', 'asc')
            ->get();

        return $siswas->map(function ($siswa) {
            $logs = Absensi::where('siswa_id', $siswa->id)
                ->whereMonth('tanggal', $this->filter_bulan)
                ->whereYear('tanggal', $this->filter_tahun)
                ->whereHas('jadwal', function($q) {
                    $q->where('guru_id', Auth::user()->guru->id)
                      ->where('kelas_id', $this->filter_kelas)
                      ->where('mapel_id', $this->filter_mapel);
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

    /**
     * Action Export Excel via Stream
     */
    public function exportExcel()
    {
        $data = $this->fetchRekapData();
        
        $namaKelas = '';
        $namaMapel = '';
        if($this->filter_kelas) {
            $namaKelas = Kelas::find($this->filter_kelas)->nama_kelas ?? 'Unknown';
        }
        if($this->filter_mapel) {
            $namaMapel = Mapel::find($this->filter_mapel)->nama_mapel ?? 'Unknown';
        }
        
        $fileName = 'Rekap_Kelas_' . ($namaKelas ? str_replace(' ', '_', $namaKelas) . '_' : '') . ($namaMapel ? str_replace(' ', '_', $namaMapel) . '_' : '') . $this->filter_bulan . '_' . $this->filter_tahun . '.csv';

        return response()->streamDownload(function () use ($data) {
            $file = fopen('php://output', 'w');
            fwrite($file, "\xEF\xBB\xBF");
            fwrite($file, "sep=,\n");
            fputcsv($file, ['Nama Siswa', 'NISN', 'Hadir', 'Izin', 'Sakit', 'Alpha', 'Persentase Efektif (%)']);

            // Looping isi datanya
            foreach ($data as $row) {
                fputcsv($file, [
                    $row['nama'],
                    // 🪄 Kasih karakter Tab di depannya biar Excel baca sebagai Text
                    "\t" . $row['nisn'],
                    $row['h'],
                    $row['i'],
                    $row['s'],
                    $row['a'],
                    $row['persen'] . '%'
                ]);
            }
            
            fclose($file);
        }, $fileName);
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

        return [
            'daftarRekap' => $this->fetchRekapData(),
            'daftarKelas' => $daftarKelas,
            'daftarMapel' => $this->getMapelByKelas(),
            'kepsek' => KepalaSekolah::first(), 
            'listBulan' => [
                '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
                '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
                '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
            ]
        ];
    }

    private function getMapelByKelas()
    {
        if (!$this->filter_kelas) return collect();

        $guruId = Auth::user()->guru->id;
        $mapelIds = Jadwal::where('guru_id', $guruId)
            ->where('kelas_id', $this->filter_kelas)
            ->pluck('mapel_id')
            ->unique();

        return Mapel::whereIn('id', $mapelIds)->get();
    }

    public function render()
    {
        return view('livewire.guru.rekap-kelas-index');
    }
}