<?php

namespace App\Livewire\Walas;

use Livewire\Component;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Absensi;
use App\Models\Jadwal;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class LaporanAbsensiIndex extends Component
{
    // --- UI STATE ---
    public $view_type = 'harian'; // Default view: Harian
    public $filter_tanggal;
    public $filter_bulan;
    public $filter_tahun;

    public function mount()
    {
        $this->filter_tanggal = Carbon::today()->toDateString();
        $this->filter_bulan = date('m');
        $this->filter_tahun = date('Y');
    }

    /**
     * Menyediakan data untuk tampilan (Matrix Harian & Rekap Bulanan)
     */
    public function with(): array
    {
        $user = Auth::user();
        $kelas = Kelas::where('wali_kelas_id', $user->guru?->id)->first();

        if (!$kelas) {
            return [
                'kelas' => null,
                'listBulan' => $this->getListBulan(),
                'jamPelajaran' => collect(),
                'rekapHarian' => collect(),
                'rekapData' => collect(),
            ];
        }

        // --- 1. LOGIKA DETAIL HARIAN (MATRIX MAPEL) ---
        $jamPelajaran = collect();
        $rekapHarian = collect();

        if ($this->view_type === 'harian') {
            // Ambil jadwal di hari tersebut (Filter by Day Name)
            $dayName = Carbon::parse($this->filter_tanggal)->translatedFormat('l');
            $jamPelajaran = Jadwal::with('mapel')
                ->where('kelas_id', $kelas->id)
                ->where('hari', $dayName)
                ->orderBy('jam_mulai')
                ->get();

            $siswas = Siswa::where('kelas_id', $kelas->id)->orderBy('nama_lengkap', 'asc')->get();

            $rekapHarian = $siswas->map(function ($siswa) use ($jamPelajaran) {
                $logs = [];
                $foundHadir = false;
                $foundAlpha = false;
                $isBolos = false;

                foreach ($jamPelajaran as $idx => $jam) {
                    $absen = Absensi::where('siswa_id', $siswa->id)
                        ->where('jadwal_id', $jam->id)
                        ->whereDate('tanggal', $this->filter_tanggal)
                        ->first();
                    
                    $status = $absen ? $absen->status : '-';
                    $logs[$jam->id] = $status;

                    // Logika Deteksi Cabut/Bolos
                    if($status === 'H') $foundHadir = true;
                    if($status === 'A' && $foundHadir) $isBolos = true; // Alpha setelah sempat Hadir
                }

                // Tentukan status akhir hari ini
                $statusAkhir = 'TANPA_DATA';
                if ($jamPelajaran->isNotEmpty()) {
                    if ($isBolos) $statusAkhir = 'BOLOS';
                    elseif ($foundHadir) $statusAkhir = 'HADIR';
                    elseif (in_array('A', $logs)) $statusAkhir = 'ALPHA';
                }

                return [
                    'nama' => $siswa->nama_lengkap,
                    'nisn' => $siswa->nisn,
                    'logs' => $logs,
                    'status_akhir' => $statusAkhir
                ];
            });
        }

        // --- 2. LOGIKA REKAP BULANAN (AKUMULASI) ---
        $rekapBulanan = collect();
        if ($this->view_type === 'bulanan') {
            $rekapBulanan = Siswa::where('kelas_id', $kelas->id)
                ->orderBy('nama_lengkap', 'asc')
                ->get()
                ->map(function ($siswa) {
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
                        'h' => $h, 's' => $s, 'i' => $i, 'a' => $a,
                        'persen' => $total > 0 ? round(($h / $total) * 100, 1) : 0
                    ];
                });
        }

        return [
            'kelas' => $kelas,
            'view_type' => $this->view_type,
            'jamPelajaran' => $jamPelajaran,
            'rekapHarian' => $rekapHarian,
            'rekapData' => $rekapBulanan,
            'listBulan' => $this->getListBulan()
        ];
    }

    private function getListBulan() {
        return ['01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April','05'=>'Mei','06'=>'Juni','07'=>'Juli','08'=>'Agustus','09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'];
    }

    public function render()
    {
        return view('livewire.walas.laporan-absensi-index');
    }
}