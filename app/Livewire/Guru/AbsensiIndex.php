<?php

namespace App\Livewire\Guru;

use Livewire\Component;
use App\Models\Jadwal;
use App\Models\Siswa;
use App\Models\Absensi;
use App\Services\FonnteService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AbsensiIndex extends Component
{
    public $jadwal_id;
    public $tanggal;
    public $absensiData = []; // State status (H/S/I/A)
    public $catatanData = []; // State keterangan/catatan

    public function mount($jadwal_id = null)
    {
        $this->jadwal_id = $jadwal_id;
        $this->tanggal = Carbon::today()->toDateString();
        
        if ($this->jadwal_id) {
            $this->loadDataSiswa();
        }
    }

    public function updatedJadwalId()
    {
        $this->loadDataSiswa();
    }

    public function loadDataSiswa()
    {
        $this->absensiData = [];
        $this->catatanData = [];

        if (!$this->jadwal_id) return;

        $jadwal = Jadwal::find($this->jadwal_id);
        if ($jadwal) {
            $siswas = Siswa::where('kelas_id', $jadwal->kelas_id)
                ->orderBy('nama_lengkap', 'asc')
                ->get();

            $existing = Absensi::where('jadwal_id', $this->jadwal_id)
                ->whereDate('tanggal', $this->tanggal)
                ->get()
                ->keyBy('siswa_id');

            foreach ($siswas as $s) {
                // Gunakan string key agar reaktivitas array di Livewire 3 lebih aman
                $sid = (string) $s->id;
                $this->absensiData[$sid] = $existing->has($s->id) ? $existing[$s->id]->status : 'H';
                $this->catatanData[$sid] = $existing->has($s->id) ? $existing[$s->id]->keterangan : '';
            }
        }
    }

    public function save()
    {
        if (!$this->jadwal_id || empty($this->absensiData)) {
            $this->dispatch('notify', message: 'Gagal! Data tidak ditemukan.');
            return;
        }

        $jadwal = Jadwal::with(['kelas', 'mapel'])->find($this->jadwal_id);
        $jumlahTerkirim = 0;

        try {
            DB::beginTransaction();

            foreach ($this->absensiData as $siswa_id => $status) {
                // Ambil catatan untuk siswa ini
                $catatanSiswa = $this->catatanData[$siswa_id] ?? null;

                // 1. Mapping dan simpan ke database
                Absensi::updateOrCreate(
                    [
                        'jadwal_id' => $this->jadwal_id,
                        'siswa_id'  => $siswa_id,
                        'tanggal'   => $this->tanggal,
                    ],
                    [
                        'status'     => $status,
                        'keterangan' => $catatanSiswa,
                    ]
                );

                // 2. Notifikasi WA via Fonnte (Hanya untuk yang tidak hadir)
                if (in_array($status, ['H','S', 'I', 'A'])) {
                    $siswa = Siswa::with('ortu')->find($siswa_id);
                    if ($siswa && $siswa->ortu && $siswa->ortu->no_hp_wa) {
                        // Kirim Catatan ke Service Message
                        $pesan = FonnteService::createAbsensiMessage(
                            $siswa->nama_lengkap,
                            $jadwal->mapel->nama_mapel,
                            $status,
                            Carbon::parse($this->tanggal)->translatedFormat('d F Y'),
                            $catatanSiswa 
                        );
                        
                        FonnteService::sendMessage($siswa->ortu->no_hp_wa, $pesan);
                        $jumlahTerkirim++;
                    }
                }
            }

            DB::commit();
            
            $notifMsg = "Absensi Berhasil Disimpan!";
            if($jumlahTerkirim > 0) $notifMsg .= " & {$jumlahTerkirim} WA Notifikasi Terkirim.";
            
            $this->dispatch('notify', message: $notifMsg);

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('notify', message: 'Error: ' . $e->getMessage());
        }
    }

    public function with(): array
    {
        $user = Auth::user();
        Carbon::setLocale('id');
        $hariIni = Carbon::now()->translatedFormat('l');

        $jadwals = Jadwal::where('guru_id', $user->guru?->id)
            ->where('hari', $hariIni)
            ->with(['kelas', 'mapel'])
            ->get();

        if ($jadwals->isEmpty()) {
            $jadwals = Jadwal::where('guru_id', $user->guru?->id)->with(['kelas', 'mapel'])->get();
        }

        return [
            'jadwal' => $this->jadwal_id ? Jadwal::with(['kelas', 'mapel'])->find($this->jadwal_id) : null,
            'daftarSiswa' => $this->jadwal_id ? Siswa::where('kelas_id', Jadwal::find($this->jadwal_id)->kelas_id)->orderBy('nama_lengkap', 'asc')->get() : collect(),
            'daftarJadwalHariIni' => $jadwals
        ];
    }

    public function render()
    {
        return view('livewire.guru.absensi-index');
    }
}