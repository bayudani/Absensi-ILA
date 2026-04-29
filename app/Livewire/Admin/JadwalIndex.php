<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use Livewire\Attributes\Title;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Guru;

#[Title('Kelola Jadwal - SiAbsen')]
class JadwalIndex extends Component
{
    use WithPagination;

    // --- FORM STATE ---
    public $kelas_id, $mapel_id, $guru_id, $hari, $jam_mulai, $jam_selesai;
    
    // --- UI STATE ---
    #[Url(history: true)]
    public $filter_kelas = '';
    
    #[Url(history: true)]
    public $filter_hari = '';
    
    public $editingJadwalId = null;
    public $isOpen = false;

    public function with(): array
    {
        return [
            'daftarJadwal' => Jadwal::with(['kelas', 'mapel', 'guru'])
                ->when($this->filter_kelas, function($q) {
                    $q->where('kelas_id', $this->filter_kelas);
                })
                ->when($this->filter_hari, function($q) {
                    $q->where('hari', $this->filter_hari);
                })
                // Sorting Hari Custom: Senin dulu baru sampai Sabtu
                ->orderByRaw("FIELD(hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu')")
                ->orderBy('jam_mulai')
                ->paginate(15),
            'daftarKelas' => Kelas::all(),
            'daftarMapel' => Mapel::all(),
            'daftarGuru' => Guru::all(),
            'listHari' => ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'],
        ];
    }

    public function resetFields()
    {
        $this->reset(['kelas_id', 'mapel_id', 'guru_id', 'hari', 'jam_mulai', 'jam_selesai', 'editingJadwalId']);
        $this->resetValidation();
    }

    public function openModal($id = null)
    {
        $this->resetFields();
        $this->editingJadwalId = $id;

        if ($id) {
            $jadwal = Jadwal::findOrFail($id);
            $this->kelas_id = $jadwal->kelas_id;
            $this->mapel_id = $jadwal->mapel_id;
            $this->guru_id = $jadwal->guru_id;
            $this->hari = $jadwal->hari;
            $this->jam_mulai = $jadwal->jam_mulai;
            $this->jam_selesai = $jadwal->jam_selesai;
        }

        $this->isOpen = true;
    }

    public function save()
    {
        $this->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'mapel_id' => 'required|exists:mapel,id',
            'guru_id' => 'required|exists:gurus,id',
            'hari' => 'required',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required',
        ]);

        Jadwal::updateOrCreate(
            ['id' => $this->editingJadwalId],
            [
                'kelas_id' => $this->kelas_id,
                'mapel_id' => $this->mapel_id,
                'guru_id' => $this->guru_id,
                'hari' => $this->hari,
                'jam_mulai' => $this->jam_mulai,
                'jam_selesai' => $this->jam_selesai,
            ]
        );

        $this->isOpen = false;
        $this->dispatch('notify', message: $this->editingJadwalId ? 'Jadwal diperbarui!' : 'Jadwal baru ditambahkan!');
        $this->resetFields();
    }

    public function delete($id)
    {
        Jadwal::findOrFail($id)->delete();
        $this->dispatch('notify', message: 'Jadwal berhasil dihapus.');
    }

    public function updatingFilterKelas() { $this->resetPage(); }
    public function updatingFilterHari() { $this->resetPage(); }

    public function render()
    {
        return view('livewire.admin.jadwal-index');
    }
}