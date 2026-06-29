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
        $this->tanggal = Carbon::today()->toDateString();
        $hariIni = Carbon::now()->translatedFormat('l');

        if ($jadwal_id) {
            $jadwal = Jadwal::find($jadwal_id);
            if ($jadwal && $jadwal->hari !== $hariIni) {
                $this->dispatch('notify', message: 'Jadwal ini tidak untuk hari ini!', type: 'error');
                $this->jadwal_id = null;
                return;
            }
            $this->jadwal_id = $jadwal_id;
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
        $hariIni = Carbon::now()->translatedFormat('l');
        if ($jadwal && $jadwal->hari !== $hariIni) {
            $this->dispatch('notify', message: 'Jadwal ini tidak untuk hari ini!', type: 'error');
            $this->jadwal_id = null;
            return;
        }
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
                $this->absensiData[$sid] = $existing->has($s->id) ? $existing[$s->id]->status : '';
                $this->catatanData[$sid] = $existing->has($s->id) ? $existing[$s->id]->keterangan : '';
            }
        }
    }

    public function pilihSemuaHadir()
    {
        if (!$this->jadwal_id) return;
        $jadwal = Jadwal::find($this->jadwal_id);
        if (!$jadwal) return;

        $siswas = Siswa::where('kelas_id', $jadwal->kelas_id)
            ->orderBy('nama_lengkap', 'asc')
            ->get();

        foreach ($siswas as $s) {
            $this->absensiData[(string) $s->id] = 'H';
        }

        $this->dispatch('notify', message: 'Semua siswa di-set Hadir!', type: 'success');
    }

    public function save()
    {
        // 1. Validasi kalau jadwal belum dipilih
        if (!$this->jadwal_id) {
            $this->dispatch('notify', message: 'Waduh, pilih jadwalnya dulu ya kak!', type: 'error');
            return;
        }

        $jadwal = Jadwal::with(['kelas', 'mapel'])->find($this->jadwal_id);
        if (!$jadwal) {
            $this->dispatch('notify', message: 'Jadwal tidak ditemukan!', type: 'error');
            return;
        }

        // 2. Cek apakah ada siswa yang ke-skip belum diabsen
        $siswas = Siswa::where('kelas_id', $jadwal->kelas_id)->orderBy('nama_lengkap', 'asc')->get();
        $siswaBelumDiabsen = [];

        foreach ($siswas as $s) {
            $sid = (string) $s->id;
            if (!isset($this->absensiData[$sid]) || $this->absensiData[$sid] === '') {
                $siswaBelumDiabsen[] = $s->nama_lengkap;
            }
        }

        // Kalau ada yang bolong, kasih alert error!
        if (count($siswaBelumDiabsen) > 0) {
            $jumlah = count($siswaBelumDiabsen);
            // Spill 2 nama pertama biar guru gampang nyarinya
            $spillNama = collect($siswaBelumDiabsen)->take(2)->implode(', ');
            $tambahan = $jumlah > 2 ? " dan " . ($jumlah - 2) . " lainnya" : "";

            $pesanError = "Oops! Ada {$jumlah} siswa belum diabsen ({$spillNama}{$tambahan}). Cek lagi yuk!";
            
            $this->dispatch('notify', message: $pesanError, type: 'error');
            return; // Stop proses save
        }

        $jumlahTerkirim = 0;

        try {
            DB::beginTransaction();

            foreach ($this->absensiData as $siswa_id => $status) {
                // Ambil catatan untuk siswa ini
                $catatanSiswa = $this->catatanData[$siswa_id] ?? null;

                // 3. Mapping dan simpan ke database
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

                // 4. Notifikasi WA via Fonnte
                if (in_array($status, ['H','S', 'I', 'A'])) {
                    $siswa = Siswa::with('ortu')->find($siswa_id);
                    if ($siswa && $siswa->ortu && $siswa->ortu->no_hp_wa) {
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
            
            $notifMsg = "Yeay! Absensi Berhasil Disimpan!";
            if($jumlahTerkirim > 0) $notifMsg .= " & {$jumlahTerkirim} WA Notifikasi Terkirim.";
            
            // Kirim notifikasi sukses!
            $this->dispatch('notify', message: $notifMsg, type: 'success');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('notify', message: 'Error Server: ' . $e->getMessage(), type: 'error');
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
            ->orderBy('jam_mulai')
            ->get();

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