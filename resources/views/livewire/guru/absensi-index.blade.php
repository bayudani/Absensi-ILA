<div class="space-y-6">
    <!-- Komponen Toast Notifikasi -->
    <div x-data="{ show: false, message: '' }"
         x-on:notify.window="show = true; message = $event.detail.message; setTimeout(() => show = false, 3000)"
         x-show="show"
         x-transition
         class="fixed top-6 right-6 z-[100] p-4 bg-emerald-600 text-white rounded-2xl shadow-2xl font-bold flex items-center gap-3 border border-emerald-400"
         style="display: none;">
        <i class="fas fa-check-circle text-xl"></i>
        <span x-text="message"></span>
    </div>

    <!-- Header & Pemilihan Jadwal -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white p-8 rounded-[2.5rem] border border-emerald-100 shadow-sm relative overflow-hidden">
            <div class="relative z-10">
                <h3 class="text-2xl font-black text-gray-800 tracking-tight text-emerald-900">Input Kehadiran Siswa</h3>
                @if($jadwal)
                    <p class="text-sm text-gray-500 mt-1">
                        Mengabsen Kelas <span class="text-emerald-600 font-bold uppercase">{{ $jadwal->kelas->nama_kelas }}</span> 
                        untuk mata pelajaran <span class="text-emerald-600 font-bold">{{ $jadwal->mapel->nama_mapel }}</span>.
                    </p>
                @else
                    <p class="text-sm text-red-500 mt-1 font-bold italic">Silakan pilih jadwal mengajar terlebih dahulu pada kolom di samping.</p>
                @endif
            </div>
            <i class="fas fa-user-check absolute -bottom-4 -right-4 text-emerald-50 text-8xl opacity-60 transform -rotate-12"></i>
        </div>

        <div class="bg-white p-6 rounded-[2rem] border border-gray-100 shadow-sm flex flex-col justify-center">
            <label class="text-[10px] font-black text-gray-400 uppercase mb-2 block tracking-widest pl-1">Ganti Jadwal / Kelas</label>
            <select wire:model.live="jadwal_id" class="w-full px-5 py-3 rounded-2xl border-gray-100 bg-gray-50 focus:bg-white focus:ring-4 focus:ring-emerald-50 text-sm font-bold transition-all outline-none cursor-pointer">
                <option value="">-- Pilih Jadwal --</option>
                @foreach($daftarJadwalHariIni as $dj)
                    <option value="{{ $dj->id }}">{{ substr($dj->jam_mulai, 0, 5) }} - Kelas {{ $dj->kelas->nama_kelas }} ({{ $dj->mapel->nama_mapel }})</option>
                @endforeach
            </select>
        </div>
    </div>

    @if($jadwal)
    <!-- Daftar Siswa Table -->
    <div class="bg-white rounded-[3rem] border border-gray-100 shadow-sm overflow-hidden relative">
        <div class="p-6 border-b border-gray-50 bg-gray-50/30 flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="flex items-center gap-4">
                <div class="px-4 py-2 bg-white border border-gray-200 rounded-xl shadow-sm text-xs font-bold text-gray-600">
                    <i class="far fa-calendar-alt mr-2 text-emerald-500"></i> {{ \Carbon\Carbon::parse($tanggal)->translatedFormat('d F Y') }}
                </div>
                <div class="text-xs font-bold text-gray-400 uppercase tracking-tighter">
                    Total: <span class="text-gray-800">{{ $daftarSiswa->count() }} Siswa</span>
                </div>
            </div>
            @if($daftarSiswa->count() > 0)
                <button wire:click="save" class="w-full md:w-auto bg-emerald-600 text-white px-8 py-3 rounded-2xl text-xs font-black uppercase tracking-[0.2em] shadow-lg shadow-emerald-100 hover:bg-emerald-700 transition-all active:scale-95">
                    <i class="fas fa-save mr-2"></i> SIMPAN ABSENSI
                </button>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm border-collapse">
                <thead>
                    <tr class="bg-white text-gray-400 text-[10px] uppercase font-black tracking-widest border-b">
                        <th class="p-6 w-16 text-center">No</th>
                        <th class="p-6">Identitas Siswa</th>
                        <th class="p-6 text-center">Kehadiran (H/S/I/A)</th>
                        <th class="p-6">Catatan Singkat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 font-sans">
                    @forelse($daftarSiswa as $index => $s)
                    <tr class="hover:bg-emerald-50/20 transition-colors group">
                        <td class="p-6 text-center font-mono text-xs text-gray-400">{{ $index + 1 }}</td>
                        <td class="p-6">
                            <div class="flex flex-col leading-none">
                                <span class="font-black text-gray-800 text-sm group-hover:text-emerald-700 transition-colors">{{ $s->nama_lengkap }}</span>
                                <span class="text-[10px] text-gray-400 font-mono mt-1.5 uppercase">NISN: {{ $s->nisn }}</span>
                            </div>
                        </td>
                        <td class="p-6">
                            <div class="flex items-center justify-center gap-3">
                                <!-- HADIR -->
                                <label class="cursor-pointer">
                                    <input type="radio" 
                                           wire:model="absensiData.{{ $s->id }}" 
                                           name="status_{{ $s->id }}" 
                                           value="H" 
                                           class="hidden peer">
                                    <div class="w-10 h-10 flex items-center justify-center rounded-xl border-2 border-gray-100 bg-gray-50 font-black text-xs text-gray-400 transition-all 
                                        peer-checked:border-emerald-500 peer-checked:bg-emerald-600 peer-checked:text-white peer-checked:shadow-lg peer-checked:shadow-emerald-100">H</div>
                                </label>
                                <!-- SAKIT -->
                                <label class="cursor-pointer">
                                    <input type="radio" 
                                           wire:model="absensiData.{{ $s->id }}" 
                                           name="status_{{ $s->id }}" 
                                           value="S" 
                                           class="hidden peer">
                                    <div class="w-10 h-10 flex items-center justify-center rounded-xl border-2 border-gray-100 bg-gray-50 font-black text-xs text-gray-400 transition-all 
                                        peer-checked:border-yellow-500 peer-checked:bg-yellow-500 peer-checked:text-white peer-checked:shadow-lg peer-checked:shadow-yellow-100">S</div>
                                </label>
                                <!-- IZIN -->
                                <label class="cursor-pointer">
                                    <input type="radio" 
                                           wire:model="absensiData.{{ $s->id }}" 
                                           name="status_{{ $s->id }}" 
                                           value="I" 
                                           class="hidden peer">
                                    <div class="w-10 h-10 flex items-center justify-center rounded-xl border-2 border-gray-100 bg-gray-50 font-black text-xs text-gray-400 transition-all 
                                        peer-checked:border-blue-500 peer-checked:bg-blue-600 peer-checked:text-white peer-checked:shadow-lg peer-checked:shadow-blue-100">I</div>
                                </label>
                                <!-- ALPHA -->
                                <label class="cursor-pointer">
                                    <input type="radio" 
                                           wire:model="absensiData.{{ $s->id }}" 
                                           name="status_{{ $s->id }}" 
                                           value="A" 
                                           class="hidden peer">
                                    <div class="w-10 h-10 flex items-center justify-center rounded-xl border-2 border-gray-100 bg-gray-50 font-black text-xs text-gray-400 transition-all 
                                        peer-checked:border-red-500 peer-checked:bg-red-600 peer-checked:text-white peer-checked:shadow-lg peer-checked:shadow-red-100">A</div>
                                </label>
                            </div>
                        </td>
                        <td class="p-6">
                            <input type="text" wire:model="catatanData.{{ $s->id }}" 
                                class="w-full px-4 py-2 text-xs rounded-xl border-gray-100 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 transition-all outline-none"
                                placeholder="Cth: Izin lomba...">
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="p-32 text-center">
                            <div class="flex flex-col items-center opacity-20">
                                <i class="fas fa-users-slash text-6xl mb-4"></i>
                                <p class="font-black text-xs uppercase tracking-widest text-gray-500">Data Siswa Di Kelas Ini Masih Kosong</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($daftarSiswa->count() > 0)
        <div class="p-8 bg-gray-50/50 border-t border-gray-100 flex justify-end">
            <button wire:click="save" class="bg-emerald-600 text-white px-10 py-4 rounded-[1.5rem] text-xs font-black uppercase tracking-[0.2em] shadow-xl shadow-emerald-100 hover:bg-emerald-700 transition-all active:scale-95">
                KIRIM ABSENSI HARI INI
            </button>
        </div>
        @endif
    </div>
    @endif
</div>