<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Absensi;
use App\Models\KepalaSekolah; 

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
     * Helper buat ngambil data (dipisah biar bisa dipake view & export)
     */
    private function fetchRekapData()
    {
        $querySiswa = Siswa::with('kelas')
            ->when($this->filter_kelas, function($q) {
                $q->where('kelas_id', $this->filter_kelas);
            });

        return $querySiswa->get()->map(function ($siswa) {
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
    }

    /**
     * Action Export Excel via Stream (Aman banget buat Vercel)
     */
    public function exportExcel()
    {
        $data = $this->fetchRekapData();
        $fileName = 'Rekap_Absensi_' . $this->filter_bulan . '_' . $this->filter_tahun . '.csv';

        return response()->streamDownload(function () use ($data) {
            $file = fopen('php://output', 'w');
            
            // Tulis Header Kolom Excel
            fputcsv($file, ['Nama Siswa', 'NISN', 'Kelas', 'Hadir', 'Izin', 'Sakit', 'Alpha', 'Persentase Efektif (%)']);

            // Looping isi datanya
            foreach ($data as $row) {
                fputcsv($file, [
                    $row['nama'],
                    // 🪄 JURUS ANTI-SCIENTIFIC EXCEL: Kasih karakter Tab di depannya biar Excel baca sebagai Text
                    "\t" . $row['nisn'],
                    $row['kelas'],
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

    public function with(): array
    {
        return [
            'daftarRekap' => $this->fetchRekapData(),
            'daftarKelas' => Kelas::all(),
            // 👇 Ambil data Kepala Sekolah buat ditampilin di Print PDF
            'kepsek' => KepalaSekolah::first(), 
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