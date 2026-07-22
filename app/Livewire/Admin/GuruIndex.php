<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use Livewire\Attributes\Title;
use App\Models\Guru;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

#[Title('Manajemen Guru - SiAbsen')]
class GuruIndex extends Component
{
    use WithPagination;

    // --- FORM STATE ---
    public $nip, $nama_lengkap, $no_hp, $jenis_kelamin = 'L', $alamat;
    public $password; // Password opsional saat edit

    // --- UI STATE ---
    #[Url(history: true)]
    public $search = '';
    
    public $editingGuruId = null;
    public $isOpen = false;

    /**
     * Data untuk View
     */
    public function with(): array
    {
        return [
            'daftarGuru' => Guru::with('user')
                ->where('nama_lengkap', 'like', '%' . $this->search . '%')
                ->orWhere('nip', 'like', '%' . $this->search . '%')
                ->latest()
                ->paginate(10),
        ];
    }

    public function resetFields()
    {
        $this->reset(['nip', 'nama_lengkap', 'no_hp', 'jenis_kelamin', 'alamat', 'password', 'editingGuruId']);
        $this->resetValidation();
    }

    public function openModal($id = null)
    {
        $this->resetFields();
        $this->editingGuruId = $id;

        if ($id) {
            $guru = Guru::findOrFail($id);
            $this->nip = $guru->nip;
            $this->nama_lengkap = $guru->nama_lengkap;
            $this->no_hp = $guru->no_hp;
            $this->jenis_kelamin = $guru->jenis_kelamin;
            $this->alamat = $guru->alamat;
        }

        $this->isOpen = true;
    }

    public function save()
    {
        $this->validate([
            'nip' => 'required|numeric|unique:gurus,nip,' . $this->editingGuruId,
            'nama_lengkap' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:L,P',
            'password' => $this->editingGuruId ? 'nullable|min:6' : 'required|min:6',
        ]);

        DB::transaction(function () {
            // 1. Kelola Akun User
            if ($this->editingGuruId) {
                $guru = Guru::find($this->editingGuruId);
                $user = $guru->user;
                $user->update([
                    'name' => $this->nama_lengkap,
                    'username' => $this->nip,
                ]);
                if ($this->password) {
                    $user->update(['password' => Hash::make($this->password)]);
                }
            } else {
                $user = User::create([
                    'name' => $this->nama_lengkap,
                    'username' => $this->nip,
                    'password' => Hash::make($this->password),
                    'role' => 'guru',
                ]);
            }

            // 2. Kelola Profil Guru
            Guru::updateOrCreate(
                ['id' => $this->editingGuruId],
                [
                    'user_id' => $user->id,
                    'nip' => $this->nip,
                    'nama_lengkap' => $this->nama_lengkap,
                    'no_hp' => $this->no_hp,
                    'jenis_kelamin' => $this->jenis_kelamin,
                    'alamat' => $this->alamat,
                ]
            );
        });

        $this->isOpen = false;
        $this->dispatch('notify', message: $this->editingGuruId ? 'Data guru diperbarui!' : 'Guru baru ditambahkan!');
        $this->resetFields();
    }

    public function delete($id)
    {
        $guru = Guru::findOrFail($id);
        // Hapus User-nya juga karena cascade
        $guru->user->delete(); 
        $this->dispatch('notify', message: 'Data guru dan akun login dihapus.');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.admin.guru-index');
    }
}