<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use Livewire\Attributes\Title;
use App\Models\Kelas;
use App\Models\Guru;

#[Title('Manajemen Kelas - SiAbsen')]
class KelasIndex extends Component
{
    use WithPagination;

    // --- State Form ---
    public $tingkat = '';
    public $lokal = '';
    public $wali_kelas_id = '';
    public $tahun_ajaran = '2025/2026';
    
    // --- State UI ---
    #[Url(history: true)]
    public $search = '';
    
    public $editingKelasId = null;
    public $isOpen = false;

    /**
     * Mengatur data yang dikirim ke view.
     * Best Practice: Query database di sini agar performa render efisien.
     */
    public function placeholder()
    {
        return <<<'HTML'
            <div class="flex justify-center p-20">
                <div class="w-10 h-10 border-4 border-emerald-500 border-t-transparent rounded-full animate-spin"></div>
            </div>
        HTML;
    }

    public function with(): array
    {
        return [
            'daftarKelas' => Kelas::with('waliKelas')
                ->where('nama_kelas', 'like', '%' . $this->search . '%')
                ->latest()
                ->paginate(10),
            'daftarGuru' => Guru::orderBy('nama_lengkap', 'asc')->get(),
        ];
    }

    /**
     * Reset input form dan validasi
     */
    public function resetFields()
    {
        $this->reset(['tingkat', 'lokal', 'wali_kelas_id', 'editingKelasId']);
        $this->resetValidation();
    }

    /**
     * Logika membuka modal Tambah/Edit
     */
    public function openModal($id = null)
    {
        $this->resetFields();
        $this->editingKelasId = $id;

        if ($id) {
            $kelas = Kelas::find($id);
            $this->tingkat = $kelas->tingkat;
            $this->lokal = $kelas->lokal;
            $this->wali_kelas_id = $kelas->wali_kelas_id;
            $this->tahun_ajaran = $kelas->tahun_ajaran;
        }

        $this->isOpen = true;
    }

    /**
     * Simpan atau Update data
     */
    public function save()
    {
        $this->validate([
            'tingkat' => 'required',
            'lokal' => 'required|max:10',
            'wali_kelas_id' => 'required|exists:gurus,id',
            'tahun_ajaran' => 'required',
        ], [
            'tingkat.required' => 'Tingkat kelas harus dipilih!',
            'lokal.required' => 'Lokal kelas harus diisi!',
            'lokal.max' => 'Lokal kelas maksimal 10 karakter!',
            'wali_kelas_id.required' => 'Wali kelas harus dipilih!',
            'wali_kelas_id.exists' => 'Wali kelas tidak ditemukan!',
            'tahun_ajaran.required' => 'Tahun ajaran harus diisi!',
        ]);

        $namaKelas = $this->tingkat . '-' . $this->lokal;

        // Simpan wali_kelas_id lama sebelum di-update (untuk rollback role nantinya)
        $oldWaliKelasId = null;
        if ($this->editingKelasId) {
            $oldWaliKelasId = Kelas::find($this->editingKelasId)?->wali_kelas_id;
        }

        Kelas::updateOrCreate(
            ['id' => $this->editingKelasId],
            [
                'nama_kelas' => $namaKelas,
                'tingkat' => $this->tingkat,
                'lokal' => $this->lokal,
                'wali_kelas_id' => $this->wali_kelas_id,
                'tahun_ajaran' => $this->tahun_ajaran,
            ]
        );

        // Sync role: wali kelas baru mendapat role 'walas'
        if ($this->wali_kelas_id) {
            $guru = Guru::with('user')->find($this->wali_kelas_id);
            if ($guru && $guru->user && $guru->user->role !== 'walas') {
                $guru->user->update(['role' => 'walas']);
            }
        }

        // Sync role: wali kelas lama (jika diganti) dikembalikan ke 'guru' jika tidak jadi wali kelas lagi
        if ($oldWaliKelasId && $oldWaliKelasId != $this->wali_kelas_id) {
            $masihWaliKelas = Kelas::where('wali_kelas_id', $oldWaliKelasId)->exists();
            if (!$masihWaliKelas) {
                $guru = Guru::with('user')->find($oldWaliKelasId);
                if ($guru && $guru->user && $guru->user->role !== 'guru') {
                    $guru->user->update(['role' => 'guru']);
                }
            }
        }

        $this->isOpen = false;
        
        // Dispatch event untuk notifikasi Alpine.js
        $this->dispatch('notify', message: $this->editingKelasId ? 'Berhasil memperbarui data kelas!' : 'Berhasil menambah kelas baru!');
        
        $this->resetFields();
    }

    /**
     * Hapus data kelas
     */
    public function delete($id)
    {
        $kelas = Kelas::find($id);
        $oldWaliKelasId = $kelas?->wali_kelas_id;

        $kelas->delete();

        // Kembalikan role ke 'guru' jika tidak menjadi wali kelas di kelas lain
        if ($oldWaliKelasId) {
            $masihWaliKelas = Kelas::where('wali_kelas_id', $oldWaliKelasId)->exists();
            if (!$masihWaliKelas) {
                $guru = Guru::with('user')->find($oldWaliKelasId);
                if ($guru && $guru->user && $guru->user->role !== 'guru') {
                    $guru->user->update(['role' => 'guru']);
                }
            }
        }

        $this->dispatch('notify', message: 'Data kelas telah dihapus.');
    }

    /**
     * Reset paginasi saat melakukan pencarian
     */
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.admin.kelas-index');
    }
}