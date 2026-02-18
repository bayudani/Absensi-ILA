<?php

namespace App\Livewire\Guru;

use Livewire\Component;
use App\Models\Jadwal;
use App\Models\Siswa;
use App\Models\Absensi;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AbsensiIndex extends Component
{
    public $jadwal_id;
    public $tanggal;
    public $absensiData = []; 
    public $catatanData = []; 

    public function mount($jadwal_id = null)
    {
        $this->jadwal_id = $jadwal_id;
        $this->tanggal = Carbon::today()->toDateString();
        $this->loadDataSiswa();
    }

    /**
     * Hook: Otomatis jalan saat $jadwal_id berubah via dropdown
     */
    public function updatedJadwalId()
    {
        $this->loadDataSiswa();
    }

    public function loadDataSiswa()
    {
        // Reset data lama
        $this->absensiData = [];
        $this->catatanData = [];

        if ($this->jadwal_id) {
            $jadwal = Jadwal::find($this->jadwal_id);
            if ($jadwal) {
                // Ambil siswa berdasarkan kelas di jadwal tersebut
                $siswas = Siswa::where('kelas_id', $jadwal->kelas_id)
                    ->orderBy('nama_lengkap', 'asc')
                    ->get();

                // Cek apakah sudah ada absen hari ini di database?
                $existing = Absensi::where('jadwal_id', $this->jadwal_id)
                    ->where('tanggal', $this->tanggal)
                    ->get()
                    ->keyBy('siswa_id');

                foreach ($siswas as $s) {
                    // Jika sudah ada di DB pakai data DB, jika belum set default 'H' (Hadir)
                    $this->absensiData[$s->id] = $existing->has($s->id) ? $existing[$s->id]->status : 'H';
                    $this->catatanData[$s->id] = $existing->has($s->id) ? $existing[$s->id]->keterangan : '';
                }
            }
        }
    }

    public function with(): array
    {
        $jadwal = $this->jadwal_id ? Jadwal::with(['kelas', 'mapel'])->find($this->jadwal_id) : null;
        
        return [
            'jadwal' => $jadwal,
            'daftarSiswa' => $jadwal ? Siswa::where('kelas_id', $jadwal->kelas_id)->orderBy('nama_lengkap', 'asc')->get() : collect(),
            'daftarJadwalHariIni' => Jadwal::where('guru_id', auth()->user()->guru->id)
                ->where('hari', Carbon::now()->translatedFormat('l'))
                ->with(['kelas', 'mapel'])
                ->get()
        ];
    }

    public function save()
    {
        if (!$this->jadwal_id || empty($this->absensiData)) return;

        DB::transaction(function () {
            foreach ($this->absensiData as $siswa_id => $status) {
                Absensi::updateOrCreate(
                    [
                        'jadwal_id' => $this->jadwal_id,
                        'siswa_id' => $siswa_id,
                        'tanggal' => $this->tanggal,
                    ],
                    [
                        'status' => $status,
                        'keterangan' => $this->catatanData[$siswa_id] ?? null,
                    ]
                );
            }
        });

        $this->dispatch('notify', message: 'Absensi berhasil disimpan!');
    }

    public function render()
    {
        return view('livewire.guru.absensi-index');
    }
}