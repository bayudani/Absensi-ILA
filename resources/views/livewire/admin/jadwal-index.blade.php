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
        <div class="flex flex-row gap-4 w-full lg:w-auto">
            <div class="w-1/2 sm:w-64">
                <select wire:model.live="filter_kelas" class="w-full px-5 py-3 rounded-2xl border-gray-100 bg-white shadow-sm focus:ring-2 focus:ring-emerald-500 font-bold text-sm outline-none transition-all">
                    <option value="">Semua Kelas (Filter)</option>
                    @foreach($daftarKelas as $k)
                        <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="w-1/2 sm:w-48">
                <select wire:model.live="filter_hari" class="w-full px-5 py-3 rounded-2xl border-gray-100 bg-white shadow-sm focus:ring-2 focus:ring-emerald-500 font-bold text-sm outline-none transition-all">
                    <option value="">Semua Hari</option>
                    @foreach($listHari as $h)
                        <option value="{{ $h }}">{{ $h }}</option>
                    @endforeach
                </select>
            </div>
        </div>


        {{-- LOGIC: Tombol Tambah Jadwal disembunyikan jika role adalah kepsek --}}
        @if(Auth::user()->role !== 'kepsek')
            <button wire:click="openModal()" class="w-full md:w-auto bg-emerald-600 text-white px-8 py-3 rounded-2xl text-xs font-black uppercase tracking-widest flex items-center justify-center gap-2 hover:bg-emerald-700 shadow-lg shadow-emerald-100 transition-all active:scale-95">
                <i class="fas fa-plus-circle"></i> TAMBAH JADWAL
            </button>
        @endif
    </div>

    <!-- Tabel Data Jadwal -->
    <div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-sm overflow-hidden relative">
        <div wire:loading class="absolute inset-0 bg-white/60 backdrop-blur-[1px] z-10 flex items-center justify-center">
            <div class="w-10 h-10 border-4 border-emerald-500 border-t-transparent rounded-full animate-spin"></div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-gray-50/50 text-gray-400 text-[10px] uppercase font-black tracking-[0.2em] border-b border-gray-50">
                        <th class="p-6">Hari & Waktu</th>
                        <th class="p-6">Mata Pelajaran</th>
                        <th class="p-6">Guru Pengampu</th>
                        <th class="p-6">Kelas</th>
                        
                        @if(Auth::user()->role !== 'kepsek')
                            <th class="p-6 text-center">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($daftarJadwal as $j)
                        <tr class="hover:bg-emerald-50/30 transition-colors group">
                            <td class="p-6">
                                <div class="flex flex-col">
                                    <span class="font-black text-gray-800 uppercase tracking-tighter">{{ $j->hari }}</span>
                                    <span class="text-[10px] text-emerald-600 font-bold font-mono mt-1">
                                        <i class="far fa-clock mr-1"></i>{{ substr($j->jam_mulai, 0, 5) }} - {{ substr($j->jam_selesai, 0, 5) }}
                                    </span>
                                </div>
                            </td>
                            <td class="p-6">
                                <div class="flex flex-col">
                                    <span class="font-bold text-gray-800 text-sm leading-none">{{ $j->mapel->nama_mapel }}</span>
                                    <span class="text-[10px] text-gray-400 font-mono mt-1.5">{{ $j->mapel->kode_mapel }}</span>
                                </div>
                            </td>
                            <td class="p-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center font-black text-[10px]">
                                        {{ strtoupper(substr($j->guru->nama_lengkap, 0, 1)) }}
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="font-medium text-gray-700 text-xs">{{ $j->guru->nama_lengkap }}</span>
                                        @if($j->guru->spesialisasi)
                                            <span class="text-[9px] text-gray-400 font-bold mt-0.5">{{ $j->guru->spesialisasi }}</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="p-6">
                                <span class="px-3 py-1 bg-emerald-100 text-emerald-700 rounded-lg font-black text-[10px]">
                                    {{ $j->kelas->nama_kelas }}
                                </span>
                            </td>

                            {{-- LOGIC: Tombol Edit & Hapus disembunyikan jika role adalah kepsek --}}
                            @if(Auth::user()->role !== 'kepsek')
                                <td class="p-6 text-center">
                                    <div class="flex justify-center gap-2">
                                        <button wire:click="openModal({{ $j->id }})" class="w-9 h-9 flex items-center justify-center text-blue-500 bg-blue-50 hover:bg-blue-600 hover:text-white rounded-xl transition-all shadow-sm">
                                            <i class="fas fa-pen text-xs"></i>
                                        </button>
                                        <button wire:click="delete({{ $j->id }})" wire:confirm="Hapus jadwal ini?" class="w-9 h-9 flex items-center justify-center text-red-400 bg-red-50 hover:bg-red-500 hover:text-white rounded-xl transition-all shadow-sm">
                                            <i class="fas fa-trash-alt text-xs"></i>
                                        </button>
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ Auth::user()->role !== 'kepsek' ? '5' : '4' }}" class="p-32 text-center text-gray-300 font-bold uppercase text-xs tracking-widest">Database Jadwal Kosong</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-8 py-5 border-t border-gray-50 bg-gray-50/30">
            {{ $daftarJadwal->links() }}
        </div>
    </div>

    <!-- MODAL POPUP FORM (Tetap dirender agar code tidak error, tapi tombol triggernya hilang buat Kepsek) -->
    @if($isOpen)
        <div class="fixed inset-0 z-[70] flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-md" wire:click="$set('isOpen', false)"></div>

            <div class="relative bg-white w-full max-w-xl rounded-[3rem] shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-300 border border-emerald-100">
                <div class="p-10">
                    <div class="flex justify-between items-center mb-8">
                        <div>
                            <h3 class="text-2xl font-black text-gray-800 tracking-tighter leading-none uppercase">
                                {{ $editingJadwalId ? 'Ubah' : 'Input' }} Jadwal
                            </h3>
                            <p class="text-[10px] font-bold text-emerald-500 uppercase mt-2 tracking-[0.3em]">Pengaturan Moving Class</p>
                        </div>
                        <button wire:click="$set('isOpen', false)" class="w-12 h-12 flex items-center justify-center rounded-2xl bg-gray-50 text-gray-400 hover:bg-red-50 hover:text-red-500 transition-all">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>

                    <div class="space-y-6">
                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase mb-2 tracking-widest pl-1">Hari</label>
                                <select wire:model="hari" class="w-full px-5 py-4 rounded-2xl border-gray-100 bg-gray-50 focus:bg-white focus:ring-4 focus:ring-emerald-50 text-sm font-bold outline-none transition-all">
                                    <option value="">-- Pilih --</option>
                                    @foreach($listHari as $h) <option value="{{ $h }}">{{ $h }}</option> @endforeach
                                </select>
                                @error('hari') <span class="text-red-500 text-[10px] font-bold mt-1 block italic">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase mb-2 tracking-widest pl-1">Kelas</label>
                                <select wire:model="kelas_id" class="w-full px-5 py-4 rounded-2xl border-gray-100 bg-gray-50 focus:bg-white focus:ring-4 focus:ring-emerald-50 text-sm font-bold outline-none transition-all">
                                    <option value="">-- Pilih --</option>
                                    @foreach($daftarKelas as $k) <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option> @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase mb-2 tracking-widest pl-1">Jam Mulai</label>
                                <input wire:model="jam_mulai" type="time" class="w-full px-5 py-4 rounded-2xl border-gray-100 bg-gray-50 focus:bg-white text-sm font-bold outline-none">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase mb-2 tracking-widest pl-1">Jam Selesai</label>
                                <input wire:model="jam_selesai" type="time" class="w-full px-5 py-4 rounded-2xl border-gray-100 bg-gray-50 focus:bg-white text-sm font-bold outline-none">
                            </div>
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase mb-2 tracking-widest pl-1">Mata Pelajaran</label>
                            <select wire:model="mapel_id" class="w-full px-5 py-4 rounded-2xl border-gray-100 bg-gray-50 focus:bg-white focus:ring-4 focus:ring-emerald-50 text-sm font-bold outline-none">
                                <option value="">-- Pilih --</option>
                                @foreach($daftarMapel as $m) <option value="{{ $m->id }}">{{ $m->nama_mapel }}</option> @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase mb-2 tracking-widest pl-1">Guru Pengajar</label>
                            <select wire:model="guru_id" class="w-full px-5 py-4 rounded-2xl border-gray-100 bg-gray-50 focus:bg-white focus:ring-4 focus:ring-emerald-50 text-sm font-bold outline-none">
                                <option value="">-- Pilih --</option>
                                @foreach($daftarGuru as $g) <option value="{{ $g->id }}">{{ $g->nama_lengkap }} {{ $g->spesialisasi ? '- ' . $g->spesialisasi : '' }}</option> @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mt-10 flex gap-4">
                        <button wire:click="save" class="flex-1 bg-emerald-600 text-white font-black py-5 rounded-3xl text-xs uppercase tracking-[0.2em] shadow-xl shadow-emerald-100 hover:bg-emerald-700 transition-all active:scale-95 flex items-center justify-center gap-3">
                            <i class="fas fa-save"></i> SIMPAN JADWAL
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>