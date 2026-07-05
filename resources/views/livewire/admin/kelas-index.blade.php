<div class="space-y-6">
    <!-- Notifikasi Toast -->
    <div x-data="{ show: false, message: '' }"
        x-on:notify.window="show = true; message = $event.detail.message; setTimeout(() => show = false, 3000)"
        x-show="show" x-transition
        class="fixed top-6 right-6 z-[100] p-4 bg-emerald-600 text-white rounded-2xl shadow-2xl font-bold flex items-center gap-3 border border-emerald-400"
        style="display: none;">
        <i class="fas fa-check-circle text-xl"></i>
        <span x-text="message"></span>
    </div>

    <!-- Header Control -->
    <div class="flex flex-col md:flex-row justify-between items-center gap-4">
        <div class="relative w-full md:w-96">
            <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400">
                <i class="fas fa-search text-xs"></i>
            </span>
            <input wire:model.live.debounce.300ms="search" type="text"
                class="w-full pl-12 pr-4 py-3 text-sm rounded-2xl border-gray-100 bg-white shadow-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all outline-none"
                placeholder="Cari kelas...">
        </div>

        {{-- LOGIC: Tombol Tambah Kelas disembunyikan jika role adalah kepsek --}}
        @if(Auth::user()->role !== 'kepsek')
            <button wire:click="openModal()"
                class="w-full md:w-auto bg-emerald-600 text-white px-6 py-3 rounded-2xl text-xs font-black uppercase tracking-widest flex items-center justify-center gap-2 hover:bg-emerald-700 shadow-lg shadow-emerald-100 transition-all active:scale-95">
                <i class="fas fa-plus-circle"></i> TAMBAH KELAS
            </button>
        @endif
    </div>

    <!-- Tabel Data -->
    <div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-sm overflow-hidden relative">
        <div wire:loading
            class="absolute inset-0 bg-white/60 backdrop-blur-[1px] z-10 flex items-center justify-center">
            <div class="w-10 h-10 border-4 border-emerald-500 border-t-transparent rounded-full animate-spin"></div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr
                        class="bg-gray-50/50 text-gray-400 text-[10px] uppercase font-black tracking-[0.2em] border-b border-gray-50">
                        <th class="p-6">Struktur</th>
                        <th class="p-6">Nama Kelas</th>
                        <th class="p-6">Wali Kelas</th>
                        <th class="p-6">Tahun Ajaran</th>
                        
                        {{-- LOGIC: Kolom Aksi disembunyikan jika role adalah kepsek --}}
                        @if(Auth::user()->role !== 'kepsek')
                            <th class="p-6 text-center">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($daftarKelas as $kelas)
                        <tr class="hover:bg-emerald-50/30 transition-colors group">
                            <td class="p-6">
                                <span class="text-gray-400 font-bold mr-1">{{ $kelas->tingkat }}</span>
                                <span class="text-gray-200 font-light">|</span>
                                <span class="text-gray-800 font-black ml-1 uppercase">{{ $kelas->lokal }}</span>
                            </td>
                            <td class="p-6">
                                <span
                                    class="px-4 py-1.5 bg-emerald-100 text-emerald-700 rounded-xl font-black text-[11px]">
                                    {{ $kelas->nama_kelas }}
                                </span>
                            </td>
                            <td class="p-6">
                                @if ($kelas->waliKelas)
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-9 h-9 rounded-full bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 font-black text-xs">
                                            {{ strtoupper(substr($kelas->waliKelas->nama_lengkap, 0, 1)) }}
                                        </div>
                                        <div class="flex flex-col text-xs">
                                            <span
                                                class="font-bold text-gray-800">{{ $kelas->waliKelas->nama_lengkap }}</span>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-gray-300 italic text-[10px] font-bold">BELUM DISET</span>
                                @endif
                            </td>
                            <td class="p-6 font-mono text-[10px] text-gray-500 font-bold">{{ $kelas->tahun_ajaran }}
                            </td>

                            {{-- LOGIC: Tombol Edit & Hapus disembunyikan jika role adalah kepsek --}}
                            @if(Auth::user()->role !== 'kepsek')
                                <td class="p-6">
                                    <div class="flex justify-center gap-2">
                                        <button wire:click="openModal({{ $kelas->id }})"
                                            class="p-2 text-blue-600 hover:bg-blue-50 rounded-xl transition">
                                            <i class="fas fa-pen text-xs"></i>
                                        </button>
                                        <!-- Pakai wire:confirm biar gak ribet pake JS manual -->
                                        <button wire:click="delete({{ $kelas->id }})"
                                            wire:confirm="Hapus kelas ini secara permanen?"
                                            class="p-2 text-red-400 hover:bg-red-50 hover:text-red-600 rounded-xl transition">
                                            <i class="fas fa-trash-alt text-xs"></i>
                                        </button>
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ Auth::user()->role !== 'kepsek' ? '5' : '4' }}"
                                class="p-20 text-center text-gray-300 font-bold uppercase text-xs tracking-widest">
                                Database Kosong</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-8 py-4 bg-gray-50/50">
            {{ $daftarKelas->links() }}
        </div>
    </div>

    <!-- Modal Form (Hidden triggernya buat Kepsek) -->
    @if ($isOpen)
        <div class="fixed inset-0 z-[70] flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-md" wire:click="$set('isOpen', false)"></div>
            <div
                class="relative bg-white w-full max-w-lg rounded-[3rem] shadow-2xl overflow-hidden p-10 border border-emerald-100">
                <div class="flex justify-between items-center mb-10">
                    <div>
                        <h3 class="text-2xl font-black text-gray-800 tracking-tighter uppercase">
                            {{ $editingKelasId ? 'Ubah' : 'Tambah' }} Kelas</h3>
                        <p class="text-[10px] font-bold text-emerald-500 uppercase tracking-[0.3em]">E-AbsensiManagement
                        </p>
                    </div>
                    <button wire:click="$set('isOpen', false)" class="text-gray-400 hover:text-red-500"><i
                            class="fas fa-times text-xl"></i></button>
                </div>

                <div class="space-y-6">
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase mb-2">Tingkat</label>
                            <select wire:model="tingkat"
                                class="w-full px-5 py-4 rounded-2xl border-gray-100 bg-gray-50 focus:bg-white focus:ring-4 focus:ring-emerald-50 focus:border-emerald-500 text-sm font-bold transition-all outline-none">
                                <option value="">Pilih</option>
                                <option value="VII">VII</option>
                                <option value="VIII">VIII</option>
                                <option value="IX">IX</option>
                            </select>
                            @error('tingkat')
                                <span
                                    class="text-red-500 text-[10px] font-black mt-2 block italic">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase mb-2">Lokal</label>
                            <input wire:model="lokal" type="text" placeholder="Contoh: 1"
                                class="w-full px-5 py-4 rounded-2xl border-gray-100 bg-gray-50 focus:bg-white focus:ring-4 focus:ring-emerald-50 text-sm font-bold outline-none transition-all">
                            @error('lokal')
                                <span
                                    class="text-red-500 text-[10px] font-black mt-2 block italic">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase mb-2">Wali Kelas <span class="text-red-500">*</span></label>
                        <select wire:model="wali_kelas_id"
                            class="w-full px-5 py-4 rounded-2xl border-gray-100 bg-gray-50 focus:bg-white text-sm font-bold outline-none transition-all">
                            <option value="">-- Pilih Wali Kelas --</option>
                            @foreach ($daftarGuru as $guru)
                                <option value="{{ $guru->id }}">{{ $guru->nama_lengkap }}</option>
                            @endforeach
                        </select>
                        @error('wali_kelas_id')
                            <span class="text-red-500 text-[10px] font-black mt-2 block italic">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="bg-blue-50/50 p-4 rounded-3xl border border-blue-100 flex justify-between items-center">
                        <span class="text-[10px] font-black text-blue-400 uppercase">Periode Aktif</span>
                        <span class="text-xs font-black text-blue-900">{{ $tahun_ajaran }}</span>
                    </div>

                    <button wire:click="save"
                        class="w-full bg-emerald-600 text-white font-black py-5 rounded-3xl text-xs uppercase tracking-[0.2em] shadow-xl shadow-emerald-100 hover:bg-emerald-700 transition-all active:scale-95">
                        SIMPAN PERUBAHAN
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>