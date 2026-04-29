<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use Livewire\Attributes\Title;
use App\Models\Mapel;

#[Title('Manajemen Mata Pelajaran - SiAbsen')]
class MapelIndex extends Component
{
    use WithPagination;

    // --- FORM STATE ---
    public $kode_mapel, $nama_mapel;

    // --- UI STATE ---
    #[Url(history: true)]
    public $search = '';
    
    public $editingMapelId = null;
    public $isOpen = false;

    /**
     * Data untuk View
     */
    public function with(): array
    {
        return [
            'daftarMapel' => Mapel::where('nama_mapel', 'like', '%' . $this->search . '%')
                ->orWhere('kode_mapel', 'like', '%' . $this->search . '%')
                ->latest()
                ->paginate(10),
        ];
    }

    public function resetFields()
    {
        $this->reset(['kode_mapel', 'nama_mapel', 'kkm', 'editingMapelId']);
        $this->resetValidation();
    }

    public function openModal($id = null)
    {
        $this->resetFields();
        $this->editingMapelId = $id;

        if ($id) {
            $mapel = Mapel::findOrFail($id);
            $this->kode_mapel = $mapel->kode_mapel;
            $this->nama_mapel = $mapel->nama_mapel;
            // $this->kkm = $mapel->kkm;
        }

        $this->isOpen = true;
    }

    public function save()
    {
        $this->validate([
            'kode_mapel' => 'required|string|max:20|unique:mapel,kode_mapel,' . $this->editingMapelId,
            'nama_mapel' => 'required|string|max:255',
            'kkm' => 'required|numeric|min:0|max:100',
        ]);

        Mapel::updateOrCreate(
            ['id' => $this->editingMapelId],
            [
                'kode_mapel' => strtoupper($this->kode_mapel),
                'nama_mapel' => $this->nama_mapel,
                'kkm' => $this->kkm,
            ]
        );

        $this->isOpen = false;
        $this->dispatch('notify', message: $this->editingMapelId ? 'Mata pelajaran diperbarui!' : 'Mata pelajaran baru ditambahkan!');
        $this->resetFields();
    }

    public function delete($id)
    {
        Mapel::findOrFail($id)->delete();
        $this->dispatch('notify', message: 'Mata pelajaran berhasil dihapus.');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.admin.mapel-index');
    }
}