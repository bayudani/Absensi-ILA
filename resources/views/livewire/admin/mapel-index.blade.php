<div class="space-y-6">
    <!-- Notifikasi Toast -->
    <div x-data="{ show: false, message: '' }"
         x-on:notify.window="show = true; message = $event.detail.message; setTimeout(() => show = false, 3000)"
         x-show="show"
         x-transition
         class="fixed top-6 right-6 z-[100] p-4 bg-emerald-600 text-white rounded-2xl shadow-2xl font-bold flex items-center gap-3 border border-emerald-400"
         style="display: none;">
        <i class="fas fa-check-circle text-xl text-emerald-200"></i>
        <span x-text="message"></span>
    </div>

    <!-- Header Control -->
    <div class="flex flex-col md:flex-row justify-between items-center gap-4">
        <div class="relative w-full md:w-96">
            <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400">
                <i class="fas fa-search text-xs"></i>
            </span>
            <input wire:model.live.debounce.400ms="search" type="text" 
                class="w-full pl-12 pr-4 py-3 text-sm rounded-2xl border-gray-100 bg-white shadow-sm focus:ring-2 focus:ring-emerald-500 transition-all outline-none" 
                placeholder="Cari Kode / Nama Mapel...">
        </div>

        {{-- LOGIC: Tombol Tambah Mapel disembunyikan jika role adalah kepsek --}}
        @if(Auth::user()->role !== 'kepsek')
            <button wire:click="openModal()" class="w-full md:w-auto bg-emerald-600 text-white px-6 py-3 rounded-2xl text-xs font-black uppercase tracking-widest flex items-center justify-center gap-2 hover:bg-emerald-700 shadow-lg shadow-emerald-100 transition-all active:scale-95">
                <i class="fas fa-book"></i> TAMBAH MAPEL
            </button>
        @endif
    </div>

    <!-- Tabel Data Mapel -->
    <div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-sm overflow-hidden relative">
        <div wire:loading class="absolute inset-0 bg-white/60 backdrop-blur-[1px] z-10 flex items-center justify-center">
            <div class="w-10 h-10 border-4 border-emerald-500 border-t-transparent rounded-full animate-spin"></div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-gray-50/50 text-gray-400 text-[10px] uppercase font-black tracking-[0.2em] border-b border-gray-50">
                        <th class="p-6">Kode Mapel</th>
                        <th class="p-6">Nama Mata Pelajaran</th>
                        {{-- <th class="p-6 text-center">KKM</th> --}}
                        
                        {{-- LOGIC: Kolom Aksi disembunyikan jika role adalah kepsek --}}
                        @if(Auth::user()->role !== 'kepsek')
                            <th class="p-6 text-center">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($daftarMapel as $mapel)
                        <tr class="hover:bg-emerald-50/30 transition-colors group">
                            <td class="p-6">
                                <span class="px-3 py-1 bg-gray-100 text-gray-600 rounded-lg font-mono font-bold text-xs border border-gray-200 uppercase">
                                    {{ $mapel->kode_mapel }}
                                </span>
                            </td>
                            <td class="p-6 font-bold text-gray-800 text-sm">
                                {{ $mapel->nama_mapel }}
                            </td>
                            {{-- <td class="p-6 text-center font-black text-emerald-600 bg-emerald-50/20">
                                {{ $mapel->kkm }}
                            </td> --}}
                            
                            {{-- LOGIC: Tombol Edit & Hapus disembunyikan jika role adalah kepsek --}}
                            @if(Auth::user()->role !== 'kepsek')
                                <td class="p-6 text-center">
                                    <div class="flex justify-center gap-2">
                                        <button wire:click="openModal({{ $mapel->id }})" class="w-9 h-9 flex items-center justify-center text-blue-500 bg-blue-50 hover:bg-blue-600 hover:text-white rounded-xl transition-all shadow-sm">
                                            <i class="fas fa-pen text-xs"></i>
                                        </button>
                                        <button wire:click="delete({{ $mapel->id }})" wire:confirm="Hapus mata pelajaran ini?" class="w-9 h-9 flex items-center justify-center text-red-400 bg-red-50 hover:bg-red-500 hover:text-white rounded-xl transition-all shadow-sm">
                                            <i class="fas fa-trash-alt text-xs"></i>
                                        </button>
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ Auth::user()->role !== 'kepsek' ? '4' : '3' }}" class="p-32 text-center text-gray-300 font-bold uppercase text-xs tracking-widest">Database Mapel Kosong</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-8 py-5 border-t border-gray-50 bg-gray-50/30">
            {{ $daftarMapel->links() }}
        </div>
    </div>

    <!-- MODAL POPUP FORM (Trigger hidden for Kepsek) -->
    @if($isOpen)
        <div class="fixed inset-0 z-[70] flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-md" wire:click="$set('isOpen', false)"></div>

            <div class="relative bg-white w-full max-w-lg rounded-[3rem] shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-300 border border-emerald-100">
                <div class="p-10">
                    <div class="flex justify-between items-center mb-8">
                        <div>
                            <h3 class="text-2xl font-black text-gray-800 tracking-tighter leading-none uppercase">
                                {{ $editingMapelId ? 'Ubah Data' : 'Tambah' }} Mapel
                            </h3>
                            <p class="text-[10px] font-bold text-emerald-500 uppercase mt-2 tracking-[0.3em]">Manajemen Mata Pelajaran</p>
                        </div>
                        <button wire:click="$set('isOpen', false)" class="w-12 h-12 flex items-center justify-center rounded-2xl bg-gray-50 text-gray-400 hover:bg-red-50 hover:text-red-500 transition-all">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>

                    <div class="space-y-6">
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase mb-2 tracking-widest pl-1">Kode Mapel (Cth: MTK-W)</label>
                            <input wire:model="kode_mapel" type="text" class="w-full px-5 py-4 rounded-2xl border-gray-100 bg-gray-50 focus:bg-white focus:ring-4 focus:ring-emerald-50 text-sm font-bold transition-all outline-none uppercase">
                            @error('kode_mapel') <span class="text-red-500 text-[10px] font-black mt-1 block italic">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase mb-2 tracking-widest pl-1">Nama Mata Pelajaran</label>
                            <input wire:model="nama_mapel" type="text" class="w-full px-5 py-4 rounded-2xl border-gray-100 bg-gray-50 focus:bg-white focus:ring-4 focus:ring-emerald-50 text-sm font-bold transition-all outline-none">
                            @error('nama_mapel') <span class="text-red-500 text-[10px] font-black mt-1 block italic">{{ $message }}</span> @enderror
                        </div>

                        {{-- <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase mb-2 tracking-widest pl-1">KKM (Kriteria Ketuntasan Minimal)</label>
                            <input wire:model="kkm" type="number" class="w-full px-5 py-4 rounded-2xl border-gray-100 bg-gray-50 focus:bg-white focus:ring-4 focus:ring-emerald-50 text-sm font-bold transition-all outline-none">
                            @error('kkm') <span class="text-red-500 text-[10px] font-black mt-1 block italic">{{ $message }}</span> @enderror
                        </div> --}}
                    </div>

                    <div class="mt-10 flex gap-4">
                        <button wire:click="save" class="flex-1 bg-emerald-600 text-white font-black py-5 rounded-3xl text-xs uppercase tracking-[0.2em] shadow-xl shadow-emerald-100 hover:bg-emerald-700 transition-all active:scale-95 flex items-center justify-center gap-3">
                            <i class="fas fa-save"></i> SIMPAN PERUBAHAN
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>