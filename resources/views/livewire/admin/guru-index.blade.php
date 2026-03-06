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
                placeholder="Cari Nama / NIP Guru...">
        </div>

        {{-- LOGIC: Tombol Tambah Guru disembunyikan jika role adalah kepsek --}}
        @if(Auth::user()->role !== 'kepsek')
            <button wire:click="openModal()" class="w-full md:w-auto bg-emerald-600 text-white px-6 py-3 rounded-2xl text-xs font-black uppercase tracking-widest flex items-center justify-center gap-2 hover:bg-emerald-700 shadow-lg shadow-emerald-100 transition-all active:scale-95">
                <i class="fas fa-chalkboard-teacher"></i> TAMBAH GURU
            </button>
        @endif
    </div>

    <!-- Tabel Data Guru -->
    <div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-sm overflow-hidden relative">
        <div wire:loading class="absolute inset-0 bg-white/60 backdrop-blur-[1px] z-10 flex items-center justify-center">
            <div class="w-10 h-10 border-4 border-emerald-500 border-t-transparent rounded-full animate-spin"></div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-gray-50/50 text-gray-400 text-[10px] uppercase font-black tracking-[0.2em] border-b border-gray-50">
                        <th class="p-6">Informasi Guru</th>
                        <th class="p-6">Spesialisasi</th>
                        <th class="p-6">Kontak</th>
                        <th class="p-6">Status Akun</th>
                        
                        {{-- LOGIC: Kolom Aksi disembunyikan jika role adalah kepsek --}}
                        @if(Auth::user()->role !== 'kepsek')
                            <th class="p-6 text-center">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($daftarGuru as $guru)
                        <tr class="hover:bg-emerald-50/30 transition-colors group">
                            <td class="p-6">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center font-black text-xs border border-emerald-100">
                                        {{ strtoupper(substr($guru->nama_lengkap, 0, 1)) }}
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="font-bold text-gray-800 text-sm leading-none">{{ $guru->nama_lengkap }}</span>
                                        <span class="text-[10px] text-gray-400 font-mono mt-1.5 uppercase">NIP: {{ $guru->nip }} | {{ $guru->jenis_kelamin }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="p-6">
                                <span class="px-3 py-1 bg-blue-50 text-blue-700 rounded-lg font-bold text-[10px] uppercase border border-blue-100">
                                    {{ $guru->spesialisasi ?? 'Belum Diset' }}
                                </span>
                            </td>
                            <td class="p-6">
                                <div class="flex flex-col text-xs">
                                    <span class="text-gray-700 font-medium"><i class="fas fa-phone-alt mr-2 text-gray-300"></i>{{ $guru->no_hp ?? '-' }}</span>
                                    <span class="text-gray-400 mt-1 truncate max-w-[150px]"><i class="fas fa-map-marker-alt mr-2 text-gray-300"></i>{{ $guru->alamat ?? '-' }}</span>
                                </div>
                            </td>
                            <td class="p-6">
                                <span class="inline-flex items-center gap-1.5 py-1 px-2.5 rounded-full text-xs font-bold bg-green-100 text-green-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-600"></span> Aktif
                                </span>
                            </td>
                            
                            {{-- LOGIC: Tombol Edit & Hapus disembunyikan jika role adalah kepsek --}}
                            @if(Auth::user()->role !== 'kepsek')
                                <td class="p-6 text-center">
                                    <div class="flex justify-center gap-2">
                                        <button wire:click="openModal({{ $guru->id }})" class="w-9 h-9 flex items-center justify-center text-blue-500 bg-blue-50 hover:bg-blue-600 hover:text-white rounded-xl transition-all shadow-sm">
                                            <i class="fas fa-pen text-xs"></i>
                                        </button>
                                        <button wire:click="delete({{ $guru->id }})" wire:confirm="Hapus guru ini? Akun login juga akan terhapus." class="w-9 h-9 flex items-center justify-center text-red-400 bg-red-50 hover:bg-red-500 hover:text-white rounded-xl transition-all shadow-sm">
                                            <i class="fas fa-trash-alt text-xs"></i>
                                        </button>
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ Auth::user()->role !== 'kepsek' ? '5' : '4' }}" class="p-32 text-center text-gray-300 font-bold uppercase text-xs tracking-widest">Database Guru Kosong</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-8 py-5 border-t border-gray-50 bg-gray-50/30">
            {{ $daftarGuru->links() }}
        </div>
    </div>

    <!-- MODAL POPUP FORM (Hanya bisa dibuka jika bukan kepsek, tapi tetap kita render untuk admin) -->
    @if($isOpen)
        <div class="fixed inset-0 z-[70] flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-md" wire:click="$set('isOpen', false)"></div>

            <div class="relative bg-white w-full max-w-xl rounded-[3rem] shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-300 border border-emerald-100">
                <div class="p-10">
                    <div class="flex justify-between items-center mb-8">
                        <div>
                            <h3 class="text-2xl font-black text-gray-800 tracking-tighter leading-none uppercase">
                                {{ $editingGuruId ? 'Update Data' : 'Registrasi' }} Guru
                            </h3>
                            <p class="text-[10px] font-bold text-emerald-500 uppercase mt-2 tracking-[0.3em]">Manajemen Tenaga Pendidik</p>
                        </div>
                        <button wire:click="$set('isOpen', false)" class="w-12 h-12 flex items-center justify-center rounded-2xl bg-gray-50 text-gray-400 hover:bg-red-50 hover:text-red-500 transition-all">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>

                    <div class="space-y-6">
                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase mb-2 tracking-widest pl-1">NIP (Username Login)</label>
                                <input wire:model="nip" type="text" class="w-full px-5 py-4 rounded-2xl border-gray-100 bg-gray-50 focus:bg-white focus:ring-4 focus:ring-emerald-50 text-sm font-bold transition-all outline-none">
                                @error('nip') <span class="text-red-500 text-[10px] font-black mt-1 block italic">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase mb-2 tracking-widest pl-1">Nama Lengkap & Gelar</label>
                                <input wire:model="nama_lengkap" type="text" class="w-full px-5 py-4 rounded-2xl border-gray-100 bg-gray-50 focus:bg-white focus:ring-4 focus:ring-emerald-50 text-sm font-bold transition-all outline-none">
                                @error('nama_lengkap') <span class="text-red-500 text-[10px] font-black mt-1 block italic">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase mb-2 tracking-widest pl-1">Jenis Kelamin</label>
                                <select wire:model="jenis_kelamin" class="w-full px-5 py-4 rounded-2xl border-gray-100 bg-gray-50 focus:bg-white text-sm font-bold outline-none">
                                    <option value="L">Laki-laki</option>
                                    <option value="P">Perempuan</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase mb-2 tracking-widest pl-1">Mata Pelajaran Utama</label>
                                <input wire:model="spesialisasi" type="text" placeholder="Cth: Matematika" class="w-full px-5 py-4 rounded-2xl border-gray-100 bg-gray-50 focus:bg-white text-sm font-bold outline-none">
                            </div>
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase mb-2 tracking-widest pl-1">No. Handphone (Aktif)</label>
                            <input wire:model="no_hp" type="text" class="w-full px-5 py-4 rounded-2xl border-gray-100 bg-gray-50 focus:bg-white text-sm font-bold outline-none">
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase mb-2 tracking-widest pl-1">Kata Sandi Login</label>
                            <input wire:model="password" type="password" placeholder="{{ $editingGuruId ? 'Kosongkan jika tidak ingin diubah' : 'Minimal 6 karakter' }}" class="w-full px-5 py-4 rounded-2xl border-gray-100 bg-gray-50 focus:bg-white focus:ring-4 focus:ring-emerald-50 text-sm font-bold transition-all outline-none">
                            @error('password') <span class="text-red-500 text-[10px] font-black mt-1 block italic">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="mt-10 flex gap-4">
                        <button wire:click="save" class="flex-1 bg-emerald-600 text-white font-black py-5 rounded-3xl text-xs uppercase tracking-[0.2em] shadow-xl shadow-emerald-100 hover:bg-emerald-700 transition-all active:scale-95 flex items-center justify-center gap-3">
                            <i class="fas fa-save"></i> SIMPAN DATA GURU
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>