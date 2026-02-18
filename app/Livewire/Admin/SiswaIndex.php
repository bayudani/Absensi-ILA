<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Attributes\Url;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Ortu;
use Illuminate\Support\Facades\DB;

class SiswaIndex extends Component
{
    use WithPagination, WithFileUploads;

    // --- FORM STATE (SISWA) ---
    public $nisn, $nama_lengkap, $kelas_id, $jenis_kelamin = 'L', $alamat, $foto;
    
    // --- FORM STATE (ORTU) ---
    public $nama_ayah, $nama_ibu, $no_hp_wa;

    // --- UI STATE ---
    #[Url(history: true)]
    public $search = '';
    
    #[Url(history: true)]
    public $filter_kelas = '';

    public $editingSiswaId = null;
    public $isOpen = false;

    /**
     * Data yang dilempar ke template
     */
    public function with(): array
    {
        return [
            'daftarSiswa' => Siswa::with(['kelas', 'ortu'])
                ->when($this->search, function($q) {
                    $q->where('nama_lengkap', 'like', '%' . $this->search . '%')
                      ->orWhere('nisn', 'like', '%' . $this->search . '%');
                })
                ->when($this->filter_kelas, function($q) {
                    $q->where('kelas_id', $this->filter_kelas);
                })
                ->latest()
                ->paginate(10),
            'daftarKelas' => Kelas::orderBy('nama_kelas', 'asc')->get(),
        ];
    }

    public function resetFields()
    {
        $this->reset([
            'nisn', 'nama_lengkap', 'kelas_id', 'jenis_kelamin', 'alamat', 'foto',
            'nama_ayah', 'nama_ibu', 'no_hp_wa', 'editingSiswaId'
        ]);
        $this->resetValidation();
    }

    public function openModal($id = null)
    {
        $this->resetFields();
        $this->editingSiswaId = $id;

        if ($id) {
            $siswa = Siswa::with('ortu')->findOrFail($id);
            $this->nisn = $siswa->nisn;
            $this->nama_lengkap = $siswa->nama_lengkap;
            $this->kelas_id = $siswa->kelas_id;
            $this->jenis_kelamin = $siswa->jenis_kelamin;
            $this->alamat = $siswa->alamat;
            
            // Data Ortu
            if($siswa->ortu) {
                $this->nama_ayah = $siswa->ortu->nama_ayah;
                $this->nama_ibu = $siswa->ortu->nama_ibu;
                $this->no_hp_wa = $siswa->ortu->no_hp_wa;
            }
        }

        $this->isOpen = true;
    }

    public function save()
    {
        $this->validate([
            'nisn' => 'required|numeric|unique:siswas,nisn,' . $this->editingSiswaId,
            'nama_lengkap' => 'required|string|max:255',
            'kelas_id' => 'required|exists:kelas,id',
            'jenis_kelamin' => 'required|in:L,P',
            'no_hp_wa' => 'required|numeric',
        ]);

        DB::transaction(function () {
            // 1. Simpan/Update Siswa
            $siswa = Siswa::updateOrCreate(
                ['id' => $this->editingSiswaId],
                [
                    'nisn' => $this->nisn,
                    'nama_lengkap' => $this->nama_lengkap,
                    'kelas_id' => $this->kelas_id,
                    'jenis_kelamin' => $this->jenis_kelamin,
                    'alamat' => $this->alamat,
                ]
            );

            // 2. Simpan/Update Ortu
            Ortu::updateOrCreate(
                ['siswa_id' => $siswa->id],
                [
                    'nama_ayah' => $this->nama_ayah,
                    'nama_ibu' => $this->nama_ibu,
                    'no_hp_wa' => $this->no_hp_wa,
                ]
            );
        });

        $this->isOpen = false;
        $this->dispatch('notify', message: $this->editingSiswaId ? 'Data siswa berhasil diperbarui!' : 'Siswa baru berhasil ditambahkan!');
        $this->resetFields();
    }

    public function delete($id)
    {
        Siswa::findOrFail($id)->delete();
        $this->dispatch('notify', message: 'Data siswa telah dihapus.');
    }

    public function updatingSearch() { $this->resetPage(); }
    public function updatingFilterKelas() { $this->resetPage(); }

    public function render()
    {
        return view('livewire.admin.siswa-index');
    }
}