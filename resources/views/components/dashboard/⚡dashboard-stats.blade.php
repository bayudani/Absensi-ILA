<?php

use Livewire\Component;
use App\Models\Siswa;
use App\Models\Guru;
use App\Models\Absensi;
use App\Models\User;
use Carbon\Carbon;

new class extends Component {
    /**
     * Logika utama untuk menghitung statistik sekolah.
     * Menggunakan method with() untuk melempar data ke template.
     */
    public function with(): array
    {
        $today = Carbon::today()->toDateString();
        
        $totalSiswa = Siswa::count();
        // Menghitung siswa unik yang hadir hari ini
        $hadirHariIni = Absensi::whereDate('tanggal', $today)
            ->where('status', 'H')
            ->distinct('siswa_id')
            ->count();
            
        // Menghitung total Alpha (Tanpa Keterangan) hari ini
        $alpaHariIni = Absensi::whereDate('tanggal', $today)
            ->where('status', 'A')
            ->count();

        return [
            'stats' => [
                'total_siswa' => $totalSiswa,
                'total_guru' => Guru::count(),
                'hadir_hari_ini' => $hadirHariIni,
                'alpa_hari_ini' => $alpaHariIni,
                'persen_hadir' => $totalSiswa > 0 ? round(($hadirHariIni / $totalSiswa) * 100, 1) : 0,
            ],
            'recentLogs' => Absensi::with(['siswa', 'jadwal.mapel'])
                ->whereDate('tanggal', $today)
                ->latest()
                ->take(5)
                ->get()
        ];
    }
}; ?>

<div wire:poll.10s> {{-- Refresh otomatis tiap 10 detik secara cerdas --}}
    
    <!-- ROW STATISTIK UTAMA (Emerald Theme) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        
        <!-- Card: Total Siswa -->
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Total Siswa</p>
                    <p class="text-3xl font-black text-gray-800 mt-1">{{ number_format($stats['total_siswa']) }}</p>
                </div>
                <div class="p-4 bg-emerald-50 text-emerald-600 rounded-xl">
                    <i class="fas fa-user-graduate text-xl"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center text-[10px] text-gray-500 font-bold uppercase tracking-tight">
                <span class="text-emerald-500 mr-1"><i class="fas fa-check-circle"></i></span> Data Siswa Aktif
            </div>
        </div>

        <!-- Card: Persentase Kehadiran -->
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Presensi Hari Ini</p>
                    <p class="text-3xl font-black text-emerald-600 mt-1">{{ $stats['persen_hadir'] }}%</p>
                </div>
                <div class="p-4 bg-emerald-600 text-white rounded-xl shadow-lg shadow-emerald-100">
                    <i class="fas fa-calendar-check text-xl"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center text-[10px] text-gray-500 font-bold uppercase tracking-tight">
                <span class="text-emerald-600 mr-1">{{ $stats['hadir_hari_ini'] }}</span> Siswa Sudah Absen
            </div>
        </div>

        <!-- Card: Guru Aktif -->
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Guru & Staff</p>
                    <p class="text-3xl font-black text-gray-800 mt-1">{{ $stats['total_guru'] }}</p>
                </div>
                <div class="p-4 bg-blue-50 text-blue-600 rounded-xl">
                    <i class="fas fa-chalkboard-teacher text-xl"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center text-[10px] text-gray-500 font-bold uppercase tracking-tight">
                Data Pengajar SMPN 3
            </div>
        </div>

        <!-- Card: Alpha (Warning Status) -->
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-all duration-300 {{ $stats['alpa_hari_ini'] > 0 ? 'ring-2 ring-red-100 bg-red-50/10' : '' }}">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Alpha</p>
                    <p class="text-3xl font-black text-red-600 mt-1">{{ $stats['alpa_hari_ini'] }}</p>
                </div>
                <div class="p-4 bg-red-50 text-red-600 rounded-xl">
                    <i class="fas fa-user-times text-xl"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center text-[10px] {{ $stats['alpa_hari_ini'] > 0 ? 'text-red-600 animate-pulse font-black' : 'text-gray-500 font-bold' }} uppercase tracking-tight">
                <i class="fas fa-exclamation-triangle mr-1"></i> {{ $stats['alpa_hari_ini'] > 0 ? 'Butuh Tindak Lanjut' : 'Kehadiran Terpantau' }}
            </div>
        </div>
    </div>

    <!-- GRID DUA KOLOM: LOG AKTIVITAS & INFORMASI SISTEM -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- KOLOM KIRI: TABEL AKTIVITAS TERBARU (lg:col-span-2) -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden h-fit">
            <div class="p-5 border-b border-gray-50 bg-gray-50/50 flex justify-between items-center">
                <h3 class="text-xs font-bold text-gray-700 uppercase tracking-widest">
                    <i class="fas fa-bolt mr-2 text-emerald-500"></i>Aktivitas Absensi Terbaru
                </h3>
                <div class="flex items-center gap-2">
                    <span class="text-[9px] font-bold text-gray-400 uppercase">Live Update</span>
                    <div class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-ping"></div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="text-gray-400 text-[10px] uppercase font-bold border-b border-gray-50">
                            <th class="p-5">Waktu</th>
                            <th class="p-5">Siswa</th>
                            <th class="p-5">Mata Pelajaran</th>
                            <th class="p-5 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($recentLogs as $log)
                        <tr class="hover:bg-emerald-50/20 transition-colors duration-150 group">
                            <td class="p-5 font-mono text-xs text-gray-400 italic group-hover:text-emerald-600 transition-colors">
                                {{ $log->created_at->format('H:i') }}
                            </td>
                            <td class="p-5">
                                <div class="font-bold text-gray-700 leading-tight">{{ $log->siswa->nama_lengkap }}</div>
                                <div class="text-[10px] text-gray-400 font-mono">{{ $log->siswa->nisn }}</div>
                            </td>
                            <td class="p-5">
                                <span class="text-gray-600 font-medium">{{ $log->jadwal->mapel->nama_mapel }}</span>
                            </td>
                            <td class="p-5 text-center">
                                <span class="px-2.5 py-1 rounded-lg text-[9px] font-black uppercase tracking-tighter 
                                    {{ $log->status == 'H' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                    {{ $log->status == 'H' ? 'Hadir' : 'Alpha' }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="p-16 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-400">
                                    <i class="fas fa-inbox text-5xl mb-4 opacity-10"></i>
                                    <p class="text-sm italic tracking-wide">Belum ada aktivitas absensi hari ini.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- KOLOM KANAN: INFORMASI SISTEM & PENGUMUMAN (lg:col-span-1) -->
        <div class="space-y-6">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <h4 class="font-bold text-gray-700 text-[10px] uppercase tracking-widest mb-4 border-b pb-2 flex items-center">
                    <i class="fas fa-info-circle mr-2 text-emerald-500"></i> Informasi Sistem
                </h4>
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-xs text-gray-500 font-bold uppercase tracking-tighter">Tanggal</span>
                        <span class="text-sm text-gray-800 font-bold">{{ now()->translatedFormat('d F Y') }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-xs text-gray-500 font-bold uppercase tracking-tighter">Jam Lokal</span>
                        <span class="text-sm text-emerald-600 font-bold bg-emerald-50 px-3 py-1 rounded-lg border border-emerald-100" id="live-clock">
                            {{ now()->format('H:i:s') }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-xs text-gray-500 font-bold uppercase tracking-tighter">Semester</span>
                        <span class="text-sm text-gray-800 font-bold">Genap 2025/2026</span>
                    </div>
                </div>
                
                <!-- Box Pengumuman Emerald Style -->
                <div class="mt-8 p-4 bg-yellow-50 border border-yellow-100 rounded-xl relative overflow-hidden">
                    <div class="absolute -right-2 -top-2 opacity-10 transform rotate-12">
                        <i class="fas fa-bullhorn text-4xl"></i>
                    </div>
                    <p class="text-[9px] font-black text-yellow-800 leading-tight uppercase mb-2 tracking-widest flex items-center">
                        <span class="w-1.5 h-1.5 bg-yellow-500 rounded-full mr-2"></span> Pengumuman
                    </p>
                    <p class="text-xs text-yellow-700 font-medium leading-relaxed">
                        Pastikan seluruh absensi jam pertama diinput sebelum pukul <span class="font-bold underline">09:00 WIB</span>.
                    </p>
                </div>
            </div>
        </div>

    </div>

    <!-- Script Jam Digital Real-time -->
    <script>
        function updateClock() {
            const now = new Date();
            const timeString = now.getHours().toString().padStart(2, '0') + ':' + 
                               now.getMinutes().toString().padStart(2, '0') + ':' + 
                               now.getSeconds().toString().padStart(2, '0');
            const clockElement = document.getElementById('live-clock');
            if (clockElement) {
                clockElement.innerText = timeString;
            }
        }
        setInterval(updateClock, 1000);
    </script>
</div>