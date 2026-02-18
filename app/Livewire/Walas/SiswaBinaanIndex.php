<?php

namespace App\Livewire\Walas;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Ortu;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SiswaBinaanIndex extends Component
{
    use WithPagination;

    // --- State Form ---
    public $nisn, $nama_lengkap, $jenis_kelamin, $alamat;
    public $nama_ayah, $nama_ibu, $no_hp_wa;
    public $editingSiswaId = null;
    public $isOpen = false;

    #[Url(history: true)]
    public $search = '';

    /**
     * Ambil data siswa khusus kelas yang dibina Walas
     */
    public function with(): array
    {
        $user = Auth::user();
        $kelas = Kelas::where('wali_kelas_id', $user->guru?->id)->first();

        if (!$kelas) {
            return [
                'daftarSiswa' => collect(),
                'kelas' => null,
                'stats' => ['total' => 0, 'L' => 0, 'P' => 0]
            ];
        }

        $query = Siswa::with('ortu')
            ->where('kelas_id', $kelas->id)
            ->when($this->search, function($q) {
                $q->where('nama_lengkap', 'like', '%' . $this->search . '%')
                  ->orWhere('nisn', 'like', '%' . $this->search . '%');
            });

        return [
            'daftarSiswa' => $query->latest()->paginate(10),
            'kelas' => $kelas,
            'stats' => [
                'total' => Siswa::where('kelas_id', $kelas->id)->count(),
                'L' => Siswa::where('kelas_id', $kelas->id)->where('jenis_kelamin', 'L')->count(),
                'P' => Siswa::where('kelas_id', $kelas->id)->where('jenis_kelamin', 'P')->count(),
            ]
        ];
    }

    /**
     * Membuka Modal Edit
     */
    public function openModal($id)
    {
        $this->resetValidation();
        $siswa = Siswa::with('ortu')->findOrFail($id);
        
        $this->editingSiswaId = $id;
        $this->nisn = $siswa->nisn;
        $this->nama_lengkap = $siswa->nama_lengkap;
        $this->jenis_kelamin = $siswa->jenis_kelamin;
        $this->alamat = $siswa->alamat;
        
        $this->nama_ayah = $siswa->ortu->nama_ayah ?? '';
        $this->nama_ibu = $siswa->ortu->nama_ibu ?? '';
        $this->no_hp_wa = $siswa->ortu->no_hp_wa ?? '';

        $this->isOpen = true;
    }

    /**
     * Menyimpan Perubahan (Siswa & Ortu)
     */
    public function save()
    {
        $this->validate([
            'nisn' => 'required|numeric|unique:siswas,nisn,' . $this->editingSiswaId,
            'nama_lengkap' => 'required|string|max:255',
            'no_hp_wa' => 'required|numeric',
        ]);

        DB::transaction(function () {
            // Update Data Siswa
            $siswa = Siswa::findOrFail($this->editingSiswaId);
            $siswa->update([
                'nisn' => $this->nisn,
                'nama_lengkap' => $this->nama_lengkap,
                'jenis_kelamin' => $this->jenis_kelamin,
                'alamat' => $this->alamat,
            ]);

            // Update Data Ortu
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
        $this->dispatch('notify', message: 'Data ananda ' . $this->nama_lengkap . ' berhasil diperbarui!');
    }

    public function updatingSearch() { $this->resetPage(); }

    public function render()
    {
        return view('livewire.walas.siswa-binaan-index');
    }
}   