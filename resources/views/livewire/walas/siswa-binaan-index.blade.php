<div class="space-y-6">
    <!-- Toast Notification -->
    <div x-data="{ show: false, message: '' }"
         x-on:notify.window="show = true; message = $event.detail.message; setTimeout(() => show = false, 3000)"
         x-show="show"
         x-transition
         class="fixed top-6 right-6 z-[100] p-4 bg-emerald-600 text-white rounded-2xl shadow-2xl font-bold flex items-center gap-3 border border-emerald-400"
         style="display: none;">
        <i class="fas fa-check-circle text-xl"></i>
        <span x-text="message"></span>
    </div>

    @if(!$kelas)
        <div class="bg-red-50 border border-red-100 p-10 rounded-[3rem] text-center">
            <i class="fas fa-exclamation-triangle text-red-400 text-4xl mb-4"></i>
            <h3 class="font-black text-red-800 uppercase tracking-tighter">Akses Dibatasi</h3>
            <p class="text-sm text-red-600">Anda belum terdaftar sebagai Wali Kelas di rombel manapun.</p>
        </div>
    @else
        <!-- Stats Row -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white p-6 rounded-[2.5rem] border border-emerald-100 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 bg-emerald-600 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-emerald-100">
                    <i class="fas fa-users"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-none mb-1">Total Siswa</p>
                    <h4 class="text-2xl font-black text-gray-800 leading-none">{{ $stats['total'] }}</h4>
                </div>
            </div>
            <div class="bg-white p-6 rounded-[2.5rem] border border-blue-100 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 bg-blue-500 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-blue-100">
                    <i class="fas fa-mars"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-none mb-1">Laki-Laki</p>
                    <h4 class="text-2xl font-black text-gray-800 leading-none">{{ $stats['L'] }}</h4>
                </div>
            </div>
            <div class="bg-white p-6 rounded-[2.5rem] border border-pink-100 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 bg-pink-500 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-pink-100">
                    <i class="fas fa-venus"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-none mb-1">Perempuan</p>
                    <h4 class="text-2xl font-black text-gray-800 leading-none">{{ $stats['P'] }}</h4>
                </div>
            </div>
        </div>

        <!-- Header & Search -->
        <div class="flex flex-col lg:flex-row justify-between items-center gap-4">
            <h3 class="text-xl font-black text-gray-800 tracking-tighter uppercase">Daftar Siswa Kelas {{ $kelas->nama_kelas }}</h3>
            <div class="relative w-full lg:w-96">
                <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400">
                    <i class="fas fa-search text-xs"></i>
                </span>
                <input wire:model.live.debounce.300ms="search" type="text" 
                    class="w-full pl-11 pr-4 py-3 text-sm rounded-2xl border-gray-100 bg-white shadow-sm focus:ring-4 focus:ring-emerald-50 transition-all outline-none" 
                    placeholder="Cari nama atau NISN...">
            </div>
        </div>

        <!-- Table Card -->
        <div class="bg-white rounded-[3rem] border border-gray-100 shadow-sm overflow-hidden relative">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="bg-gray-50/50 text-gray-400 text-[10px] uppercase font-black tracking-[0.2em] border-b border-gray-50">
                            <th class="p-6 w-16 text-center">No</th>
                            <th class="p-6">Data Siswa</th>
                            <th class="p-6 text-center">JK</th>
                            <th class="p-6">Orang Tua / Wali</th>
                            <th class="p-6 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 font-sans">
                        @forelse($daftarSiswa as $index => $s)
                            <tr class="hover:bg-emerald-50/20 transition-colors group">
                                <td class="p-6 text-center font-mono text-xs text-gray-400">
                                    {{ ($daftarSiswa->currentpage()-1) * $daftarSiswa->perpage() + $index + 1 }}
                                </td>
                                <td class="p-6">
                                    <div class="flex flex-col">
                                        <span class="font-black text-gray-800 text-sm leading-none group-hover:text-emerald-700 transition-colors">{{ $s->nama_lengkap }}</span>
                                        <span class="text-[10px] text-gray-400 font-mono mt-1.5 uppercase tracking-tighter">NISN: {{ $s->nisn }}</span>
                                    </div>
                                </td>
                                <td class="p-6 text-center">
                                    <span class="px-2 py-1 {{ $s->jenis_kelamin == 'L' ? 'bg-blue-50 text-blue-600' : 'bg-pink-50 text-pink-600' }} rounded-lg font-black text-[10px]">
                                        {{ $s->jenis_kelamin }}
                                    </span>
                                </td>
                                <td class="p-6">
                                    <div class="flex flex-col">
                                        <span class="text-xs font-bold text-gray-700 leading-none">{{ $s->ortu->nama_ayah ?? 'Data Ortu Kosong' }}</span>
                                        @if($s->ortu && $s->ortu->no_hp_wa)
                                            <a href="https://wa.me/{{ $s->ortu->no_hp_wa }}" target="_blank" class="text-[10px] text-emerald-600 font-bold mt-1.5 hover:underline">
                                                <i class="fab fa-whatsapp mr-1"></i> {{ $s->ortu->no_hp_wa }}
                                            </a>
                                        @endif
                                    </div>
                                </td>
                                <td class="p-6 text-center">
                                    <button wire:click="openModal({{ $s->id }})" class="px-5 py-2 bg-white border border-emerald-200 rounded-xl text-[10px] font-black uppercase tracking-widest text-emerald-600 hover:bg-emerald-600 hover:text-white transition-all shadow-sm active:scale-95">
                                        <i class="fas fa-edit mr-1"></i> Edit
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-32 text-center text-gray-300 font-black uppercase text-xs tracking-widest opacity-30">Tidak ada data siswa ditemukan</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-8 py-5 border-t border-gray-50 bg-gray-50/30">
                {{ $daftarSiswa->links() }}
            </div>
        </div>

        <!-- MODAL EDIT SISWA -->
        @if($isOpen)
            <div class="fixed inset-0 z-[70] flex items-center justify-center p-4">
                <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-md" wire:click="$set('isOpen', false)"></div>
                <div class="relative bg-white w-full max-w-2xl rounded-[3.5rem] shadow-2xl overflow-hidden p-10 border border-emerald-100 animate-in zoom-in duration-200">
                    <div class="flex justify-between items-center mb-8 border-b pb-4">
                        <div>
                            <h3 class="text-2xl font-black text-gray-800 tracking-tighter uppercase">Update Data Anak Didik</h3>
                            <p class="text-[10px] font-bold text-emerald-500 uppercase tracking-widest">Kelas {{ $kelas->nama_kelas }}</p>
                        </div>
                        <button wire:click="$set('isOpen', false)" class="w-10 h-10 flex items-center justify-center rounded-2xl bg-gray-50 text-gray-400 hover:bg-red-50 hover:text-red-500 transition-colors">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="space-y-6 max-h-[70vh] overflow-y-auto custom-scrollbar pr-2">
                        <!-- Section Biodata -->
                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase mb-2 ml-1">NISN</label>
                                <input wire:model="nisn" type="text" class="w-full px-5 py-4 rounded-2xl border-gray-100 bg-gray-50 focus:bg-white focus:ring-4 focus:ring-emerald-50 text-sm font-bold transition-all outline-none">
                                @error('nisn') <span class="text-red-500 text-[10px] font-bold mt-1 block italic">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase mb-2 ml-1">Nama Lengkap</label>
                                <input wire:model="nama_lengkap" type="text" class="w-full px-5 py-4 rounded-2xl border-gray-100 bg-gray-50 focus:bg-white text-sm font-bold outline-none">
                                @error('nama_lengkap') <span class="text-red-500 text-[10px] font-bold mt-1 block italic">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase mb-2 ml-1">Jenis Kelamin</label>
                                <select wire:model="jenis_kelamin" class="w-full px-5 py-4 rounded-2xl border-gray-100 bg-gray-50 focus:bg-white text-sm font-bold outline-none">
                                    <option value="L">Laki-laki</option>
                                    <option value="P">Perempuan</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase mb-2 ml-1">Alamat Domisili</label>
                                <input wire:model="alamat" type="text" class="w-full px-5 py-4 rounded-2xl border-gray-100 bg-gray-50 focus:bg-white text-sm font-bold outline-none">
                            </div>
                        </div>

                        <!-- Section Orang Tua -->
                        <div class="pt-6 border-t border-gray-50">
                            <p class="text-[11px] font-black text-blue-500 uppercase tracking-widest mb-4 flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span> Informasi Orang Tua / Wali
                            </p>
                            <div class="grid grid-cols-2 gap-6 mb-4">
                                <input wire:model="nama_ayah" type="text" placeholder="Nama Ayah" class="w-full px-5 py-4 rounded-2xl border-gray-100 bg-gray-50 text-sm font-bold outline-none focus:bg-white">
                                <input wire:model="nama_ibu" type="text" placeholder="Nama Ibu" class="w-full px-5 py-4 rounded-2xl border-gray-100 bg-gray-50 text-sm font-bold outline-none focus:bg-white">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase mb-2 ml-1">No. WhatsApp Notifikasi</label>
                                <input wire:model="no_hp_wa" type="text" placeholder="Cth: 08123456789" class="w-full px-5 py-4 rounded-2xl border-gray-100 bg-gray-50 text-sm font-bold outline-none focus:bg-white">
                                @error('no_hp_wa') <span class="text-red-500 text-[10px] font-bold mt-1 block italic">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <button wire:click="save" class="w-full bg-emerald-600 text-white font-black py-5 rounded-[2rem] text-xs uppercase tracking-[0.2em] shadow-xl shadow-emerald-100 hover:bg-emerald-700 transition-all active:scale-95">
                            SIMPAN PERUBAHAN DATA
                        </button>
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>