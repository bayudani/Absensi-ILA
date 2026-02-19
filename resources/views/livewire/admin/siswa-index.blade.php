<div class="space-y-6">
    <!-- Komponen Toast Notifikasi -->
    <div x-data="{ show: false, message: '' }"
        x-on:notify.window="show = true; message = $event.detail.message; setTimeout(() => show = false, 3000)"
        x-show="show" x-transition
        class="fixed top-6 right-6 z-[100] p-4 bg-emerald-600 text-white rounded-2xl shadow-2xl font-bold flex items-center gap-3 border border-emerald-400"
        style="display: none;">
        <i class="fas fa-check-circle text-xl"></i>
        <span x-text="message"></span>
    </div>

    <!-- Header, Search, & Filter -->
    <div class="flex flex-col lg:flex-row justify-between items-center gap-4">
        <div class="flex flex-col md:flex-row gap-3 w-full lg:w-auto">
            <div class="relative w-full md:w-80">
                <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400">
                    <i class="fas fa-search text-xs"></i>
                </span>
                <input wire:model.live.debounce.400ms="search" type="text"
                    class="w-full pl-11 pr-4 py-3 text-sm rounded-2xl border-gray-100 bg-white shadow-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all outline-none"
                    placeholder="Cari Nama / NISN...">
            </div>

            <select wire:model.live="filter_kelas"
                class="px-4 py-3 rounded-2xl border-gray-100 bg-white shadow-sm focus:ring-2 focus:ring-emerald-500 text-sm font-medium outline-none">
                <option value="">Semua Kelas</option>
                @foreach ($daftarKelas as $k)
                    <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                @endforeach
            </select>
        </div>

        {{-- LOGIC: Tombol Tambah Siswa disembunyikan jika role adalah kepsek --}}
        @if(Auth::user()->role !== 'kepsek')
            <button wire:click="openModal()"
                class="w-full md:w-auto bg-emerald-600 text-white px-6 py-3 rounded-2xl text-xs font-black uppercase tracking-widest flex items-center justify-center gap-2 hover:bg-emerald-700 shadow-lg shadow-emerald-100 transition-all active:scale-95">
                <i class="fas fa-user-plus"></i> TAMBAH SISWA
            </button>
        @endif
    </div>

    <!-- Container Tabel -->
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
                        <th class="p-6">Siswa</th>
                        <th class="p-6">Kelas</th>
                        <th class="p-6">Wali Murid</th>
                        <th class="p-6">Kontak WA</th>
                        
                        {{-- LOGIC: Kolom Aksi disembunyikan jika role adalah kepsek --}}
                        @if(Auth::user()->role !== 'kepsek')
                            <th class="p-6 text-center">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($daftarSiswa as $siswa)
                        <tr class="hover:bg-emerald-50/30 transition-colors group">
                            <td class="p-6">
                                <div class="flex items-center gap-4">
                                    <div
                                        class="w-10 h-10 rounded-full bg-gray-100 border-2 border-white shadow-sm flex items-center justify-center text-gray-400 font-black text-xs overflow-hidden">
                                        @if ($siswa->foto)
                                            <img src="{{ asset('storage/' . $siswa->foto) }}"
                                                class="w-full h-full object-cover">
                                        @else
                                            <i class="fas fa-user"></i>
                                        @endif
                                    </div>
                                    <div class="flex flex-col">
                                        <span
                                            class="font-bold text-gray-800 text-sm leading-none">{{ $siswa->nama_lengkap }}</span>
                                        <span
                                            class="text-[10px] text-gray-400 font-mono mt-1.5 uppercase">{{ $siswa->nisn }}
                                            | {{ $siswa->jenis_kelamin }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="p-6">
                                <span
                                    class="px-3 py-1 bg-emerald-100 text-emerald-700 rounded-lg font-black text-[10px] uppercase">
                                    {{ $siswa->kelas->nama_kelas }}
                                </span>
                            </td>
                            <td class="p-6">
                                <div class="flex flex-col">
                                    <span
                                        class="text-xs font-bold text-gray-700 leading-tight">{{ $siswa->ortu->nama_ayah ?? '-' }}</span>
                                    <span
                                        class="text-[9px] text-gray-400 font-medium uppercase tracking-tighter mt-1">Ayah
                                        / Wali</span>
                                </div>
                            </td>
                            <td class="p-6">
                                <a href="https://wa.me/{{ $siswa->ortu->no_hp_wa }}" target="_blank"
                                    class="flex items-center gap-2 text-emerald-600 hover:text-emerald-700 font-bold text-xs transition">
                                    <i class="fab fa-whatsapp text-lg"></i>
                                    <span class="font-mono">{{ $siswa->ortu->no_hp_wa ?? '-' }}</span>
                                </a>
                            </td>
                            
                            {{-- LOGIC: Tombol Edit & Hapus disembunyikan jika role adalah kepsek --}}
                            @if(Auth::user()->role !== 'kepsek')
                                <td class="p-6">
                                    <div class="flex justify-center gap-2">
                                        <button wire:click="openModal({{ $siswa->id }})"
                                            class="w-9 h-9 flex items-center justify-center text-blue-500 bg-blue-50 hover:bg-blue-600 hover:text-white rounded-xl transition-all shadow-sm">
                                            <i class="fas fa-pen text-xs"></i>
                                        </button>
                                        <button wire:click="delete({{ $siswa->id }})"
                                            wire:confirm="Hapus data siswa ini?"
                                            class="w-9 h-9 flex items-center justify-center text-red-400 bg-red-50 hover:bg-red-500 hover:text-white rounded-xl transition-all shadow-sm">
                                            <i class="fas fa-trash-alt text-xs"></i>
                                        </button>
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ Auth::user()->role !== 'kepsek' ? '5' : '4' }}" class="p-32 text-center">
                                <div class="flex flex-col items-center opacity-20">
                                    <i class="fas fa-users-slash text-6xl mb-4"></i>
                                    <p class="font-black text-sm uppercase tracking-widest text-gray-400">Siswa Tidak
                                        Ditemukan</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-8 py-5 border-t border-gray-50 bg-gray-50/30">
            {{ $daftarSiswa->links() }}
        </div>
    </div>

    <!-- MODAL POPUP FORM (Trigger hidden for Kepsek) -->
    @if ($isOpen)
        <div class="fixed inset-0 z-[70] flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-md" wire:click="$set('isOpen', false)"></div>

            <div
                class="relative bg-white w-full max-w-2xl rounded-[3rem] shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-300 border border-emerald-100">
                <div class="p-10 max-h-[90vh] overflow-y-auto custom-scrollbar">
                    <div class="flex justify-between items-center mb-8 border-b border-gray-50 pb-6">
                        <div>
                            <h3 class="text-2xl font-black text-gray-800 tracking-tighter uppercase leading-none">
                                {{ $editingSiswaId ? 'Ubah Profil' : 'Registrasi' }} Siswa
                            </h3>
                            <p class="text-[10px] font-bold text-emerald-500 uppercase mt-2 tracking-[0.3em]">
                                E-AbsensiDatabase v1.0</p>
                        </div>
                        <button wire:click="$set('isOpen', false)"
                            class="w-12 h-12 flex items-center justify-center rounded-2xl bg-gray-50 text-gray-400 hover:bg-red-50 hover:text-red-500 transition-all active:scale-90">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>

                    <div class="space-y-8">
                        <!-- SECTION: DATA SISWA -->
                        <div class="space-y-6">
                            <h4
                                class="text-[11px] font-black text-emerald-600 uppercase tracking-widest flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Informasi Personal Siswa
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label
                                        class="block text-[10px] font-black text-gray-400 uppercase mb-2 tracking-widest pl-1">NISN
                                        (10 Digit)</label>
                                    <input wire:model="nisn" type="text"
                                        class="w-full px-5 py-4 rounded-2xl border-gray-100 bg-gray-50 focus:bg-white focus:ring-4 focus:ring-emerald-50 text-sm font-bold transition-all outline-none">
                                    @error('nisn')
                                        <span
                                            class="text-red-500 text-[10px] font-black mt-2 block">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div>
                                    <label
                                        class="block text-[10px] font-black text-gray-400 uppercase mb-2 tracking-widest pl-1">Nama
                                        Lengkap</label>
                                    <input wire:model="nama_lengkap" type="text"
                                        class="w-full px-5 py-4 rounded-2xl border-gray-100 bg-gray-50 focus:bg-white focus:ring-4 focus:ring-emerald-50 text-sm font-bold transition-all outline-none">
                                    @error('nama_lengkap')
                                        <span
                                            class="text-red-500 text-[10px] font-black mt-2 block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label
                                        class="block text-[10px] font-black text-gray-400 uppercase mb-2 tracking-widest pl-1">Kelas</label>
                                    <select wire:model="kelas_id"
                                        class="w-full px-5 py-4 rounded-2xl border-gray-100 bg-gray-50 focus:bg-white focus:ring-4 focus:ring-emerald-50 text-sm font-bold outline-none">
                                        <option value="">-- Pilih Kelas --</option>
                                        @foreach ($daftarKelas as $k)
                                            <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                                        @endforeach
                                    </select>
                                    @error('kelas_id')
                                        <span
                                            class="text-red-500 text-[10px] font-black mt-2 block">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div>
                                    <label
                                        class="block text-[10px] font-black text-gray-400 uppercase mb-2 tracking-widest pl-1">Jenis
                                        Kelamin</label>
                                    <div class="flex gap-4">
                                        <label class="flex-1 cursor-pointer">
                                            <input type="radio" wire:model="jenis_kelamin" value="L"
                                                class="hidden peer">
                                            <div
                                                class="p-4 text-center rounded-2xl border-2 border-gray-100 bg-gray-50 peer-checked:bg-emerald-50 peer-checked:border-emerald-500 peer-checked:text-emerald-700 font-bold transition-all">
                                                Laki-laki</div>
                                        </label>
                                        <label class="flex-1 cursor-pointer">
                                            <input type="radio" wire:model="jenis_kelamin" value="P"
                                                class="hidden peer">
                                            <div
                                                class="p-4 text-center rounded-2xl border-2 border-gray-100 bg-gray-50 peer-checked:bg-emerald-50 peer-checked:border-emerald-500 peer-checked:text-emerald-700 font-bold transition-all">
                                                Perempuan</div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- SECTION: DATA ORANG TUA -->
                        <div class="space-y-6 pt-6 border-t border-gray-50">
                            <h4
                                class="text-[11px] font-black text-blue-600 uppercase tracking-widest flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-blue-500"></span> Informasi Orang Tua / Wali
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label
                                        class="block text-[10px] font-black text-gray-400 uppercase mb-2 tracking-widest pl-1">Nama
                                        Ayah</label>
                                    <input wire:model="nama_ayah" type="text"
                                        class="w-full px-5 py-4 rounded-2xl border-gray-100 bg-gray-50 focus:bg-white focus:ring-4 focus:ring-blue-50 text-sm font-bold transition-all outline-none">
                                </div>
                                <div>
                                    <label
                                        class="block text-[10px] font-black text-gray-400 uppercase mb-2 tracking-widest pl-1">Nama
                                        Ibu</label>
                                    <input wire:model="nama_ibu" type="text"
                                        class="w-full px-5 py-4 rounded-2xl border-gray-100 bg-gray-50 focus:bg-white focus:ring-4 focus:ring-blue-50 text-sm font-bold transition-all outline-none">
                                </div>
                            </div>
                            <div>
                                <label
                                    class="block text-[10px] font-black text-gray-400 uppercase mb-2 tracking-widest pl-1">Nomor
                                    WhatsApp Notifikasi (Aktif)</label>
                                <div class="relative">
                                    <span
                                        class="absolute inset-y-0 left-0 flex items-center pl-5 text-emerald-500 font-black">62</span>
                                    <input wire:model="no_hp_wa" type="text" placeholder="81234567xxx"
                                        class="w-full pl-12 pr-5 py-4 rounded-2xl border-gray-100 bg-gray-50 focus:bg-white focus:ring-4 focus:ring-emerald-50 text-sm font-bold transition-all outline-none">
                                </div>
                                @error('no_hp_wa')
                                    <span
                                        class="text-red-500 text-[10px] font-black mt-2 block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Tombol Aksi -->
                    <div class="mt-12 flex gap-4">
                        <button wire:click="save"
                            class="flex-1 bg-emerald-600 text-white font-black py-5 rounded-3xl text-xs uppercase tracking-[0.2em] shadow-xl shadow-emerald-100 hover:bg-emerald-700 hover:shadow-emerald-200 transition-all active:scale-95 flex items-center justify-center gap-3">
                            <i class="fas fa-save"></i> SIMPAN PERUBAHAN
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>