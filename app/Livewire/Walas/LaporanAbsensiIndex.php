<?php

namespace App\Livewire\Walas;

use Livewire\Component;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Absensi;
use App\Models\Jadwal;
use App\Models\KepalaSekolah; 
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
     * Logic Export Excel Dinamis (Nyesuaiin tab yang aktif)
     */
    public function exportExcel()
    {
        $user = Auth::user();
        $kelas = Kelas::where('wali_kelas_id', $user->guru?->id)->first();

        if (!$kelas) return;

        if ($this->view_type === 'harian') {
            return $this->exportHarian($kelas);
        } else {
            return $this->exportBulanan($kelas);
        }
    }

    private function exportHarian($kelas)
    {
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
            $isBolos = false;

            foreach ($jamPelajaran as $jam) {
                $absen = Absensi::where('siswa_id', $siswa->id)
                    ->where('jadwal_id', $jam->id)
                    ->whereDate('tanggal', $this->filter_tanggal)
                    ->first();
                
                $status = $absen ? $absen->status : '-';
                $logs[$jam->id] = $status;

                if($status === 'H') $foundHadir = true;
                if($status === 'A' && $foundHadir) $isBolos = true;
            }

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

        $fileName = 'Absensi_Harian_Kelas_' . str_replace(' ', '_', $kelas->nama_kelas) . '_' . $this->filter_tanggal . '.csv';

        return response()->streamDownload(function () use ($rekapHarian, $jamPelajaran) {
            $file = fopen('php://output', 'w');
            
            // Header Dinamis Mapel
            $headers = ['Nama Siswa', 'NISN'];
            foreach ($jamPelajaran as $jam) {
                $headers[] = $jam->jam_mulai . ' (' . ($jam->mapel->kode_mapel ?? '-') . ')';
            }
            $headers[] = 'Kesimpulan';
            fputcsv($file, $headers);

            // Row Data
            foreach ($rekapHarian as $row) {
                $csvRow = [
                    $row['nama'],
                    "\t" . $row['nisn'] // 🪄 Jurus tab anti-scientific excel
                ];
                foreach ($jamPelajaran as $jam) {
                    $csvRow[] = $row['logs'][$jam->id] ?? '-';
                }
                $csvRow[] = $row['status_akhir'];
                fputcsv($file, $csvRow);
            }
            fclose($file);
        }, $fileName);
    }

    private function exportBulanan($kelas)
    {
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

        $fileName = 'Rekap_Bulanan_Kelas_' . str_replace(' ', '_', $kelas->nama_kelas) . '_' . $this->filter_bulan . '_' . $this->filter_tahun . '.csv';

        return response()->streamDownload(function () use ($rekapBulanan) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Nama Siswa', 'NISN', 'Hadir', 'Izin', 'Sakit', 'Alpha', 'Persentase Efektif (%)']);

            foreach ($rekapBulanan as $row) {
                fputcsv($file, [
                    $row['nama'],
                    "\t" . $row['nisn'],
                    $row['h'], $row['i'], $row['s'], $row['a'],
                    $row['persen'] . '%'
                ]);
            }
            fclose($file);
        }, $fileName);
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
                'kepsek' => KepalaSekolah::first()
            ];
        }

        // --- 1. LOGIKA DETAIL HARIAN (MATRIX MAPEL) ---
        $jamPelajaran = collect();
        $rekapHarian = collect();

        if ($this->view_type === 'harian') {
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

                    if($status === 'H') $foundHadir = true;
                    if($status === 'A' && $foundHadir) $isBolos = true;
                }

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
            'listBulan' => $this->getListBulan(),
            'kepsek' => KepalaSekolah::first() // 👈 Inject Data Kepsek
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