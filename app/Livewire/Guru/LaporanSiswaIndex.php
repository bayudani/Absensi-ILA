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

class LaporanSiswaIndex extends Component
{
    public $kelas_id = '';
    public $mapel_id = '';
    public $siswa_id = '';
    public $semester = 'ganjil';
    public $tahun_ajaran;

    public $dataAbsensi = [];
    public $selectedSiswa = null;
    public $selectedMapel = null;

    public function mount()
    {
        $now = Carbon::now();
        $month = $now->month;
        $year = $now->year;

        if ($month >= 7) {
            $this->semester = 'ganjil';
            $this->tahun_ajaran = $year . '/' . ($year + 1);
        } else {
            $this->semester = 'genap';
            $this->tahun_ajaran = ($year - 1) . '/' . $year;
        }
    }

    public function updatedKelasId()
    {
        $this->mapel_id = '';
        $this->siswa_id = '';
        $this->dataAbsensi = [];
        $this->selectedSiswa = null;
        $this->selectedMapel = null;
    }

    public function updatedMapelId()
    {
        $this->siswa_id = '';
        $this->dataAbsensi = [];
        $this->selectedSiswa = null;
        $this->selectedMapel = null;
    }

    public function updatedSiswaId()
    {
        $this->dataAbsensi = [];
        $this->selectedSiswa = null;
        $this->selectedMapel = null;
    }

    public function lihatLaporan()
    {
        if (!$this->kelas_id || !$this->mapel_id || !$this->siswa_id) {
            $this->dispatch('notify', message: 'Pilih kelas, mapel, dan siswa terlebih dahulu!', type: 'error');
            return;
        }

        $this->selectedSiswa = Siswa::find($this->siswa_id);
        $this->selectedMapel = Mapel::find($this->mapel_id);

        $years = explode('/', $this->tahun_ajaran);
        $year1 = (int)$years[0];
        $year2 = (int)$years[1];

        if ($this->semester === 'ganjil') {
            $startDate = Carbon::create($year1, 7, 1);
            $endDate = Carbon::create($year1, 12, 31);
        } else {
            $startDate = Carbon::create($year2, 1, 1);
            $endDate = Carbon::create($year2, 6, 30);
        }

        $jadwalIds = Jadwal::where('kelas_id', $this->kelas_id)
            ->where('mapel_id', $this->mapel_id)
            ->pluck('id');

        $records = Absensi::where('siswa_id', $this->siswa_id)
            ->whereIn('jadwal_id', $jadwalIds)
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->orderBy('tanggal', 'asc')
            ->get();

        $h = $records->where('status', 'H')->count();
        $s = $records->where('status', 'S')->count();
        $i = $records->where('status', 'I')->count();
        $a = $records->where('status', 'A')->count();
        $total = $records->count();

        $this->dataAbsensi = [
            'records' => $records,
            'h' => $h,
            's' => $s,
            'i' => $i,
            'a' => $a,
            'total' => $total,
            'persen' => $total > 0 ? round(($h / $total) * 100, 1) : 0,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ];
    }

    public function with(): array
    {
        $user = Auth::user();
        $guruId = $user->guru?->id;

        $daftarKelas = Kelas::whereHas('jadwals', function($q) use ($guruId) {
            $q->where('guru_id', $guruId);
        })->get();

        if ($user->role === 'walas') {
            $daftarKelas = $daftarKelas->merge(
                Kelas::where('wali_kelas_id', $guruId)->get()
            )->unique('id');
        }

        $daftarMapel = collect();
        $daftarSiswa = collect();

        if ($this->kelas_id) {
            $kelas = Kelas::find($this->kelas_id);
            $isHomeroom = $kelas && $kelas->wali_kelas_id === $guruId && $user->role === 'walas';

            if ($isHomeroom) {
                $daftarMapel = Mapel::whereHas('jadwals', function($q) use ($guruId) {
                    $q->where('kelas_id', $this->kelas_id);
                })->get();
            } else {
                $daftarMapel = Mapel::whereHas('jadwals', function($q) use ($guruId) {
                    $q->where('guru_id', $guruId)->where('kelas_id', $this->kelas_id);
                })->get();
            }

            $daftarSiswa = Siswa::where('kelas_id', $this->kelas_id)
                ->orderBy('nama_lengkap', 'asc')
                ->get();
        }

        return [
            'daftarKelas' => $daftarKelas,
            'daftarMapel' => $daftarMapel,
            'daftarSiswa' => $daftarSiswa,
            'kepsek' => KepalaSekolah::first(),
            'listSemester' => ['ganjil' => 'Ganjil', 'genap' => 'Genap'],
        ];
    }

    public function render()
    {
        return view('livewire.guru.laporan-siswa-index');
    }
}
