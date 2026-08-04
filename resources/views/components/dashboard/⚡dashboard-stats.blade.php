<?php

use Livewire\Component;
use App\Models\Siswa;
use App\Models\Guru;
use App\Models\Absensi;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    /**
     * Logika Statistik Berdasarkan Role
     * Komponen ini menghitung data secara otomatis setiap 15 detik.
     */
    public function with(): array
    {
        $user = Auth::user();
        
        $today = Carbon::today()->toDateString();
        $hariIni = Carbon::now()->translatedFormat('l');
        
        $stats = [];
        $recentLogs = collect();

        // --- 1. LOGIKA UNTUK ADMIN / TATA USAHA ---
        if ($user->role === 'admin') {
            $stats = [
                'card1_label' => 'Total Siswa',
                'card1_val'   => Siswa::count(),
                'card1_icon'  => 'fas fa-user-graduate',
                'card1_color' => 'emerald',

                'card2_label' => 'Total Guru',
                'card2_val'   => Guru::count(),
                'card2_icon'  => 'fas fa-chalkboard-teacher',
                'card2_color' => 'blue',

                'card3_label' => 'Jumlah Kelas',
                'card3_val'   => Kelas::count(),
                'card3_icon'  => 'fas fa-school',
                'card3_color' => 'orange',

                'card4_label' => 'Presensi Hari Ini',
                'card4_val'   => Siswa::count() > 0 ? round((Absensi::whereDate('tanggal', $today)->where('status', 'H')->distinct('siswa_id')->count() / Siswa::count()) * 100, 1) . '%' : '0%',
                'card4_icon'  => 'fas fa-chart-pie',
                'card4_color' => 'indigo',
            ];
            $recentLogs = Absensi::with(['siswa', 'jadwal.mapel'])->whereDate('tanggal', $today)->latest()->take(5)->get();
        } 
        
        // --- 2. LOGIKA UNTUK GURU MAPEL ---
        elseif ($user->role === 'guru') {
            $guruId = $user->guru?->id;
            $jadwalIds = Jadwal::where('guru_id', $guruId)->pluck('id');
            
            $stats = [
                'card1_label' => 'Jadwal Hari Ini',
                'card1_val'   => Jadwal::where('guru_id', $guruId)->where('hari', $hariIni)->count() . ' Jam',
                'card1_icon'  => 'fas fa-calendar-day',
                'card1_color' => 'emerald',

                'card2_label' => 'Kelas Sudah Diabsen',
                'card2_val'   => Absensi::whereIn('jadwal_id', $jadwalIds)->whereDate('tanggal', $today)->distinct('jadwal_id')->count(),
                'card2_icon'  => 'fas fa-check-double',
                'card2_color' => 'green',

                'card3_label' => 'Mata Pelajaran',
                'card3_val'   => Jadwal::where('guru_id', $guruId)->distinct('mapel_id')->count(),
                'card3_icon'  => 'fas fa-book',
                'card3_color' => 'blue',

                'card4_label' => 'Status Pengajar',
                'card4_val'   => 'AKTIF',
                'card4_icon'  => 'fas fa-user-clock',
                'card4_color' => 'orange',
            ];
            $recentLogs = Absensi::with(['siswa', 'jadwal.mapel'])->whereIn('jadwal_id', $jadwalIds)->whereDate('tanggal', $today)->latest()->take(5)->get();
        } 
        
        // --- 3. LOGIKA UNTUK WALI KELAS ---
        elseif ($user->role === 'walas') {
            $kelas = Kelas::where('wali_kelas_id', $user->guru?->id)->first();
            $siswaIds = $kelas ? Siswa::where('kelas_id', $kelas->id)->pluck('id') : collect();
            
            $stats = [
                'card1_label' => 'Siswa Binaan',
                'card1_val'   => $siswaIds->count(),
                'card1_icon'  => 'fas fa-users',
                'card1_color' => 'emerald',

                'card2_label' => 'Siswa Hadir',
                'card2_val'   => Absensi::whereIn('siswa_id', $siswaIds)->whereDate('tanggal', $today)->where('status', 'H')->distinct('siswa_id')->count(),
                'card2_icon'  => 'fas fa-user-check',
                'card2_color' => 'green',

                'card3_label' => 'Sakit / Izin',
                'card3_val'   => Absensi::whereIn('siswa_id', $siswaIds)->whereDate('tanggal', $today)->whereIn('status', ['I', 'S'])->count(),
                'card3_icon'  => 'fas fa-hand-holding-heart',
                'card3_color' => 'blue',

                'card4_label' => 'Alpha (Tanpa Ket.)',
                'card4_val'   => Absensi::whereIn('siswa_id', $siswaIds)->whereDate('tanggal', $today)->where('status', 'A')->count(),
                'card4_icon'  => 'fas fa-user-times',
                'card4_color' => 'red',
            ];
            $recentLogs = Absensi::with(['siswa', 'jadwal.mapel'])->whereIn('siswa_id', $siswaIds)->whereDate('tanggal', $today)->latest()->take(5)->get();
        } 
        
        // --- 4. LOGIKA UNTUK KEPALA SEKOLAH ---
        elseif ($user->role === 'kepsek') {
            $stats = [
                'card1_label' => 'Guru Mengajar',
                'card1_val'   => Jadwal::where('hari', $hariIni)->distinct('guru_id')->count(),
                'card1_icon'  => 'fas fa-user-tie',
                'card1_color' => 'emerald',

                'card2_label' => 'Siswa Hadir',
                'card2_val'   => Absensi::whereDate('tanggal', $today)->where('status', 'H')->distinct('siswa_id')->count(),
                'card2_icon'  => 'fas fa-walking',
                'card2_color' => 'green',

                'card3_label' => 'Total Alpha Sekolah',
                'card3_val'   => Absensi::whereDate('tanggal', $today)->where('status', 'A')->count(),
                'card3_icon'  => 'fas fa-exclamation-triangle',
                'card3_color' => 'red',

                'card4_label' => 'Rata-rata Sekolah',
                'card4_val'   => Siswa::count() > 0 ? round((Absensi::whereDate('tanggal', $today)->where('status', 'H')->distinct('siswa_id')->count() / Siswa::count()) * 100, 1) . '%' : '0%',
                'card4_icon'  => 'fas fa-percentage',
                'card4_color' => 'blue',
            ];
            $recentLogs = Absensi::with(['siswa', 'jadwal.mapel'])->whereDate('tanggal', $today)->latest()->take(5)->get();
        }

        return [
            'stats' => $stats,
            'recentLogs' => $recentLogs,
            'userRole' => $user->role
        ];
    }
}; ?>

<div wire:poll.15s>
    <!-- GRID STATISTIK DINAMIS -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        @if(!empty($stats))
            <!-- Card 1 -->
            <div class="bg-white p-6 rounded-[2rem] border border-gray-100 shadow-sm hover:shadow-md transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">{{ $stats['card1_label'] }}</p>
                        <p class="text-2xl font-black text-gray-800 mt-1">{{ $stats['card1_val'] }}</p>
                    </div>
                    <div class="p-4 bg-{{ $stats['card1_color'] }}-50 text-{{ $stats['card1_color'] }}-600 rounded-2xl">
                        <i class="{{ $stats['card1_icon'] }} text-xl"></i>
                    </div>
                </div>
            </div>
            <!-- Card 2 -->
            <div class="bg-white p-6 rounded-[2rem] border border-gray-100 shadow-sm hover:shadow-md transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">{{ $stats['card2_label'] }}</p>
                        <p class="text-2xl font-black text-gray-800 mt-1">{{ $stats['card2_val'] }}</p>
                    </div>
                    <div class="p-4 bg-{{ $stats['card2_color'] }}-50 text-{{ $stats['card2_color'] }}-600 rounded-2xl">
                        <i class="{{ $stats['card2_icon'] }} text-xl"></i>
                    </div>
                </div>
            </div>
            <!-- Card 3 -->
            <div class="bg-white p-6 rounded-[2rem] border border-gray-100 shadow-sm hover:shadow-md transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">{{ $stats['card3_label'] }}</p>
                        <p class="text-2xl font-black text-gray-800 mt-1">{{ $stats['card3_val'] }}</p>
                    </div>
                    <div class="p-4 bg-{{ $stats['card3_color'] }}-50 text-{{ $stats['card3_color'] }}-600 rounded-2xl">
                        <i class="{{ $stats['card3_icon'] }} text-xl"></i>
                    </div>
                </div>
            </div>
            <!-- Card 4 -->
            <div class="bg-white p-6 rounded-[2rem] border border-gray-100 shadow-sm hover:shadow-md transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">{{ $stats['card4_label'] }}</p>
                        <p class="text-2xl font-black text-gray-800 mt-1">{{ $stats['card4_val'] }}</p>
                    </div>
                    <div class="p-4 bg-{{ $stats['card4_color'] }}-50 text-{{ $stats['card4_color'] }}-600 rounded-2xl">
                        <i class="{{ $stats['card4_icon'] }} text-xl"></i>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- AKTIVITAS TERBARU & INFO SISTEM -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden h-fit">
            <div class="p-6 border-b border-gray-50 bg-gray-50/50 flex justify-between items-center">
                <h3 class="text-xs font-black text-gray-700 uppercase tracking-[0.2em]">
                    <i class="fas fa-bolt mr-2 text-emerald-500"></i>Aktivitas {{ $userRole === 'admin' ? 'Sekolah' : 'Kelas' }} Terbaru
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="text-gray-400 text-[10px] uppercase font-black border-b border-gray-50 bg-white">
                            <th class="p-6">Siswa</th>
                            <th class="p-6">Pelajaran</th>
                            <th class="p-6 text-center">Status</th>
                            <th class="p-6 text-right">Waktu</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($recentLogs as $log)
                        <tr class="hover:bg-emerald-50/20 transition-all">
                            <td class="p-6">
                                <div class="font-bold text-gray-800 leading-none">{{ $log->siswa->nama_lengkap }}</div>
                                <div class="text-[10px] text-gray-400 font-mono mt-1">NISN: {{ $log->siswa->nisn }}</div>
                            </td>
                            <td class="p-6 font-medium text-gray-600">
                                {{ $log->jadwal->mapel->nama_mapel }}
                            </td>
                            <td class="p-6 text-center">
                                <span class="px-3 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest 
                                    {{ $log->status == 'H' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                    {{ $log->status == 'H' ? 'Hadir' : 'Alpha' }}
                                </span>
                            </td>
                            <td class="p-6 text-right text-gray-400 font-mono text-[10px]">
                                {{ $log->created_at->format('H:i') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="p-20 text-center text-gray-300 font-bold uppercase text-xs tracking-widest opacity-30">Belum ada aktivitas hari ini.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-gray-100">
                <h4 class="font-black text-gray-700 text-[10px] uppercase tracking-widest mb-6 border-b pb-4 flex items-center">
                    <i class="fas fa-info-circle mr-2 text-emerald-500"></i> Status Sistem
                </h4>
                <div class="space-y-5">
                    <div class="flex justify-between items-center">
                        <span class="text-xs text-gray-400 font-bold uppercase">Tanggal</span>
                        <span class="text-sm text-gray-800 font-black">{{ now()->translatedFormat('d F Y') }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-xs text-gray-400 font-bold uppercase">Waktu</span>
                        <span class="text-sm text-emerald-600 font-black bg-emerald-50 px-3 py-1 rounded-xl" id="live-clock">{{ now()->format('H:i:s') }}</span>
                    </div>
                    <div class="pt-4 border-t border-dashed">
                        <div class="p-4 bg-yellow-50 border border-yellow-100 rounded-2xl">
                             <p class="text-[9px] font-black text-yellow-800 uppercase mb-1">Pengumuman</p>
                             <p class="text-xs text-yellow-700 font-bold leading-relaxed">Input absensi harian disarankan selesai maksimal pukul 14:00 WIB.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        setInterval(() => {
            const clock = document.getElementById('live-clock');
            if(clock) clock.innerText = new Date().toLocaleTimeString('id-ID');
        }, 1000);
    </script>
</div>