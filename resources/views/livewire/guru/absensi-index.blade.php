<div class="space-y-6 relative">
    <!-- Overlay Loading Global (Muncul saat tombol simpan ditekan) -->
    <div wire:loading wire:target="save" class="fixed inset-0 z-[200] flex items-center justify-center bg-emerald-900/20 backdrop-blur-[2px]">
        <div class="bg-white p-8 rounded-[2.5rem] shadow-2xl flex flex-col items-center border border-emerald-100 animate-in zoom-in duration-200">
            <div class="w-16 h-16 border-8 border-emerald-100 border-t-emerald-600 rounded-full animate-spin mb-4"></div>
            <p class="font-black text-emerald-800 uppercase tracking-[0.2em] text-xs">Menyimpan & Mengirim WA...</p>
            <p class="text-[10px] text-gray-400 mt-2 font-bold italic">Mohon tunggu sebentar, jangan tutup halaman.</p>
        </div>
    </div>

    <!-- Komponen Toast Notifikasi -->
    <div x-data="{ show: false, message: '' }"
         x-on:notify.window="show = true; message = $event.detail.message; setTimeout(() => show = false, 4000)"
         x-show="show"
         x-transition
         class="fixed top-6 right-6 z-[201] p-5 bg-emerald-600 text-white rounded-2xl shadow-2xl font-bold flex items-center gap-3 border border-emerald-400"
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
                    <p class="text-sm text-gray-500 mt-1 font-medium">
                        Mengabsen Kelas <span class="text-emerald-600 font-bold uppercase">{{ $jadwal->kelas->nama_kelas }}</span> 
                        - Mapel: <span class="text-emerald-600 font-bold">{{ $jadwal->mapel->nama_mapel }}</span>.
                    </p>
                @else
                    <p class="text-sm text-red-500 mt-1 font-bold italic">Pilih jadwal mengajar di kolom kanan untuk memunculkan daftar siswa.</p>
                @endif
            </div>
            <i class="fas fa-user-check absolute -bottom-4 -right-4 text-emerald-50 text-8xl opacity-60 transform -rotate-12"></i>
        </div>

        <div class="bg-white p-6 rounded-[2rem] border border-gray-100 shadow-sm flex flex-col justify-center">
            <label class="text-[10px] font-black text-gray-400 uppercase mb-2 block tracking-widest pl-1">Pilih Kelas / Mapel</label>
            <select wire:model.live="jadwal_id" class="w-full px-5 py-3 rounded-2xl border-gray-100 bg-gray-50 focus:bg-white focus:ring-4 focus:ring-emerald-50 text-sm font-bold transition-all outline-none cursor-pointer">
                <option value="">-- Pilih Jadwal --</option>
                @foreach($daftarJadwalHariIni as $dj)
                    <option value="{{ $dj->id }}">[{{ $dj->hari }}] {{ substr($dj->jam_mulai, 0, 5) }} - {{ $dj->kelas->nama_kelas }} ({{ $dj->mapel->nama_mapel }})</option>
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
            
            <button wire:click="save" 
                wire:loading.attr="disabled"
                class="w-full md:w-auto bg-emerald-600 text-white px-8 py-3 rounded-2xl text-xs font-black uppercase tracking-[0.2em] shadow-lg shadow-emerald-100 hover:bg-emerald-700 transition-all active:scale-95 disabled:opacity-50">
                <i class="fas fa-save mr-2" wire:loading.remove wire:target="save"></i>
                <i class="fas fa-spinner fa-spin mr-2" wire:loading wire:target="save"></i>
                SIMPAN ABSENSI
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm border-collapse">
                <thead>
                    <tr class="bg-white text-gray-400 text-[10px] uppercase font-black tracking-widest border-b">
                        <th class="p-6 w-16 text-center">No</th>
                        <th class="p-6">Identitas Siswa</th>
                        <th class="p-6 text-center">Kehadiran (H/S/I/A)</th>
                        <th class="p-6">Catatan / Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 font-sans">
                    @forelse($daftarSiswa as $index => $s)
                    <tr wire:key="siswa-{{ $s->id }}" class="hover:bg-emerald-50/20 transition-colors group">
                        <td class="p-6 text-center font-mono text-xs text-gray-400">{{ $index + 1 }}</td>
                        <td class="p-6 font-black text-gray-800 text-sm">{{ $s->nama_lengkap }}</td>
                        <td class="p-6">
                            {{-- LOGIC WARNA FIX: Ditulis manual agar Tailwind mendeteksi class-nya --}}
                            <div class="flex items-center justify-center gap-3">
                                
                                <!-- HADIR (H) - Emerald -->
                                <label class="cursor-pointer relative group">
                                    <input type="radio" wire:model="absensiData.{{ $s->id }}" name="status_{{ $s->id }}" value="H" class="hidden peer">
                                    <div class="w-10 h-10 flex items-center justify-center rounded-xl border-2 border-gray-100 bg-gray-50 font-black text-xs text-gray-400 transition-all duration-200 
                                        peer-checked:border-emerald-500 peer-checked:bg-emerald-500 peer-checked:text-white peer-checked:shadow-lg peer-checked:shadow-emerald-200 peer-checked:scale-110 hover:bg-emerald-50">
                                        H
                                    </div>
                                    <span class="absolute -top-8 left-1/2 -translate-x-1/2 bg-gray-800 text-white text-[9px] px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">Hadir</span>
                                </label>

                                <!-- SAKIT (S) - Yellow/Orange -->
                                <label class="cursor-pointer relative group">
                                    <input type="radio" wire:model="absensiData.{{ $s->id }}" name="status_{{ $s->id }}" value="S" class="hidden peer">
                                    <div class="w-10 h-10 flex items-center justify-center rounded-xl border-2 border-gray-100 bg-gray-50 font-black text-xs text-gray-400 transition-all duration-200 
                                        peer-checked:border-yellow-400 peer-checked:bg-yellow-400 peer-checked:text-white peer-checked:shadow-lg peer-checked:shadow-yellow-200 peer-checked:scale-110 hover:bg-yellow-50">
                                        S
                                    </div>
                                    <span class="absolute -top-8 left-1/2 -translate-x-1/2 bg-gray-800 text-white text-[9px] px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">Sakit</span>
                                </label>

                                <!-- IZIN (I) - Blue -->
                                <label class="cursor-pointer relative group">
                                    <input type="radio" wire:model="absensiData.{{ $s->id }}" name="status_{{ $s->id }}" value="I" class="hidden peer">
                                    <div class="w-10 h-10 flex items-center justify-center rounded-xl border-2 border-gray-100 bg-gray-50 font-black text-xs text-gray-400 transition-all duration-200 
                                        peer-checked:border-blue-500 peer-checked:bg-blue-500 peer-checked:text-white peer-checked:shadow-lg peer-checked:shadow-blue-200 peer-checked:scale-110 hover:bg-blue-50">
                                        I
                                    </div>
                                    <span class="absolute -top-8 left-1/2 -translate-x-1/2 bg-gray-800 text-white text-[9px] px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">Izin</span>
                                </label>

                                <!-- ALPHA (A) - Red -->
                                <label class="cursor-pointer relative group">
                                    <input type="radio" wire:model="absensiData.{{ $s->id }}" name="status_{{ $s->id }}" value="A" class="hidden peer">
                                    <div class="w-10 h-10 flex items-center justify-center rounded-xl border-2 border-gray-100 bg-gray-50 font-black text-xs text-gray-400 transition-all duration-200 
                                        peer-checked:border-red-500 peer-checked:bg-red-500 peer-checked:text-white peer-checked:shadow-lg peer-checked:shadow-red-200 peer-checked:scale-110 hover:bg-red-50">
                                        A
                                    </div>
                                    <span class="absolute -top-8 left-1/2 -translate-x-1/2 bg-gray-800 text-white text-[9px] px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">Alpha</span>
                                </label>

                            </div>
                        </td>
                        <td class="p-6">
                            <input type="text" wire:model="catatanData.{{ $s->id }}" 
                                class="w-full px-4 py-2 text-xs rounded-xl border-gray-100 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-emerald-50 text-sm font-bold outline-none transition-all placeholder:font-normal"
                                placeholder="Ketik catatan (opsional)...">
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="p-20 text-center text-gray-400 italic font-bold uppercase tracking-widest opacity-30">Siswa tidak ditemukan</td></tr>
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