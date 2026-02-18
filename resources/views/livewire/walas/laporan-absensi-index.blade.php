<div class="space-y-6">
    @if(!$kelas)
        <div class="bg-red-50 border border-red-100 p-10 rounded-[3rem] text-center">
            <i class="fas fa-user-slash text-red-400 text-4xl mb-4"></i>
            <h3 class="font-black text-red-800 uppercase tracking-tighter">Akses Terbatas</h3>
            <p class="text-sm text-red-600">Sistem tidak menemukan kelas binaan atas nama Anda.</p>
        </div>
    @else
        <!-- Filter & Tab Switcher -->
        <div class="bg-white p-6 rounded-[2.5rem] border border-emerald-100 shadow-sm">
            <div class="flex flex-col lg:flex-row justify-between items-center gap-6">
                <!-- Navigasi Tab -->
                <div class="flex bg-gray-100 p-1.5 rounded-2xl w-full lg:w-auto">
                    <button wire:click="$set('view_type', 'harian')" 
                        class="flex-1 lg:flex-none px-6 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest transition-all {{ $view_type === 'harian' ? 'bg-white text-emerald-700 shadow-sm' : 'text-gray-400 hover:text-gray-600' }}">
                        Detail Harian
                    </button>
                    <button wire:click="$set('view_type', 'bulanan')" 
                        class="flex-1 lg:flex-none px-6 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest transition-all {{ $view_type === 'bulanan' ? 'bg-white text-emerald-700 shadow-sm' : 'text-gray-400 hover:text-gray-600' }}">
                        Rekap Bulanan
                    </button>
                </div>

                <!-- Filter Dinamis -->
                <div class="flex flex-wrap gap-4 w-full lg:w-auto justify-center lg:justify-end">
                    @if($view_type === 'harian')
                        <input type="date" wire:model.live="filter_tanggal" class="px-5 py-3 rounded-2xl border-gray-100 bg-gray-50 focus:bg-white focus:ring-4 focus:ring-emerald-50 text-sm font-bold outline-none">
                    @else
                        <select wire:model.live="filter_bulan" class="px-5 py-3 rounded-2xl border-gray-100 bg-gray-50 focus:bg-white focus:ring-4 focus:ring-emerald-50 text-sm font-bold outline-none">
                            @foreach($listBulan as $val => $label) <option value="{{ $val }}">{{ $label }}</option> @endforeach
                        </select>
                        <select wire:model.live="filter_tahun" class="px-5 py-3 rounded-2xl border-gray-100 bg-gray-50 focus:bg-white focus:ring-4 focus:ring-emerald-50 text-sm font-bold outline-none">
                            @for($y = date('Y'); $y >= 2024; $y--) <option value="{{ $y }}">{{ $y }}</option> @endfor
                        </select>
                    @endif
                    <button onclick="window.print()" class="bg-emerald-600 text-white p-3 rounded-2xl hover:bg-emerald-700 shadow-lg shadow-emerald-100 transition-all active:scale-95">
                        <i class="fas fa-print"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- LOGIKA SISTEM (Penjelasan untuk Dosen/User) -->
        <div class="bg-amber-50 border border-amber-200 p-6 rounded-[2rem] flex items-start gap-4">
            <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center shrink-0 text-amber-600">
                <i class="fas fa-lightbulb"></i>
            </div>
            <div class="text-xs text-amber-900 leading-relaxed">
                <p class="font-black uppercase tracking-widest mb-1">Logika Penentuan Status:</p>
                <ul class="list-disc list-inside space-y-0.5 opacity-80 font-medium">
                    <li><span class="font-bold">Hadir:</span> Mengikuti seluruh jam pelajaran hari ini.</li>
                    <li><span class="font-bold text-red-600 underline">Indikasi Bolos:</span> Sistem mendeteksi ada jam Alpha di antara jam Hadir (Cabut).</li>
                    <li><span class="font-bold">Alpha:</span> Tidak ditemukan catatan kehadiran sama sekali pada hari tersebut.</li>
                </ul>
            </div>
        </div>

        <!-- Tabel Konten -->
        <div class="bg-white rounded-[3rem] border border-gray-100 shadow-sm overflow-hidden relative min-h-[400px]">
            <div wire:loading class="absolute inset-0 bg-white/60 backdrop-blur-[1px] z-10 flex items-center justify-center">
                <div class="w-10 h-10 border-4 border-emerald-500 border-t-transparent rounded-full animate-spin"></div>
            </div>

            <div class="overflow-x-auto">
                @if($view_type === 'harian')
                    <!-- VIEW 1: MATRIX HARIAN (DETEKSI CABUT) -->
                    <table class="w-full text-left text-sm border-collapse">
                        <thead>
                            <tr class="bg-gray-50/50 text-gray-400 text-[10px] uppercase font-black tracking-[0.2em] border-b border-gray-50">
                                <th class="p-6">Siswa</th>
                                @foreach($jamPelajaran as $jam)
                                    <th class="p-6 text-center border-l border-gray-50">{{ $jam->jam_mulai }}<br><span class="text-[8px] font-bold text-emerald-500">{{ $jam->mapel->kode_mapel }}</span></th>
                                @endforeach
                                <th class="p-6 text-center bg-gray-50/80">Kesimpulan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 font-sans">
                            @forelse($rekapHarian as $row)
                                <tr class="hover:bg-emerald-50/20 transition-colors group {{ $row['status_akhir'] === 'BOLOS' ? 'bg-red-50/50' : '' }}">
                                    <td class="p-6">
                                        <div class="flex flex-col leading-none">
                                            <span class="font-black text-gray-800 text-sm group-hover:text-emerald-700 transition-colors">{{ $row['nama'] }}</span>
                                            <span class="text-[9px] text-gray-400 font-mono mt-1 uppercase">NISN: {{ $row['nisn'] }}</span>
                                        </div>
                                    </td>
                                    @foreach($jamPelajaran as $jam)
                                        <td class="p-6 text-center border-l border-gray-50">
                                            @php $status = $row['logs'][$jam->id] ?? 'X'; @endphp
                                            @if($status === 'H')
                                                <i class="fas fa-check-circle text-emerald-500"></i>
                                            @elseif($status === 'A')
                                                <span class="bg-red-100 text-red-600 px-2 py-0.5 rounded text-[9px] font-black">ALPHA</span>
                                            @else
                                                <span class="text-gray-200">-</span>
                                            @endif
                                        </td>
                                    @endforeach
                                    <td class="p-6 text-center">
                                        @if($row['status_akhir'] === 'HADIR')
                                            <span class="px-3 py-1 bg-green-100 text-green-700 rounded-lg text-[10px] font-black uppercase tracking-wider">Hadir Penuh</span>
                                        @elseif($row['status_akhir'] === 'BOLOS')
                                            <div class="flex flex-col items-center">
                                                <span class="px-3 py-1 bg-red-600 text-white rounded-lg text-[10px] font-black uppercase tracking-wider animate-pulse shadow-lg shadow-red-100">Indikasi Bolos</span>
                                                <span class="text-[8px] font-bold text-red-400 mt-1 uppercase">Kabur di tengah jam</span>
                                            </div>
                                        @else
                                            <span class="px-3 py-1 bg-gray-100 text-gray-400 rounded-lg text-[10px] font-black uppercase tracking-wider">Tanpa Data</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="10" class="p-32 text-center text-gray-300 font-black uppercase text-xs tracking-widest opacity-30">Tidak ada jadwal/data di tanggal ini</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                @else
                    <!-- VIEW 2: REKAP BULANAN (AKUMULASI) -->
                    <table class="w-full text-left text-sm border-collapse">
                        <thead>
                            <tr class="bg-gray-50/50 text-gray-400 text-[10px] uppercase font-black tracking-[0.2em] border-b border-gray-50">
                                <th class="p-6">Siswa</th>
                                <th class="p-6 text-center text-green-600 bg-green-50/30">H</th>
                                <th class="p-6 text-center text-blue-600 bg-blue-50/30">I</th>
                                <th class="p-6 text-center text-yellow-600 bg-yellow-50/30">S</th>
                                <th class="p-6 text-center text-red-600 bg-red-50/30">A</th>
                                <th class="p-6 text-center">Persentase</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 font-sans">
                            @forelse($rekapData as $data)
                                <tr class="hover:bg-emerald-50/20 transition-colors group">
                                    <td class="p-6">
                                        <div class="flex flex-col leading-none">
                                            <span class="font-black text-gray-800 text-sm group-hover:text-emerald-700 transition-colors">{{ $data['nama'] }}</span>
                                            <span class="text-[10px] text-gray-400 font-mono mt-1.5 uppercase">NISN: {{ $data['nisn'] }}</span>
                                        </div>
                                    </td>
                                    <td class="p-6 text-center font-black text-gray-700">{{ $data['h'] }}</td>
                                    <td class="p-6 text-center font-black text-gray-700">{{ $data['i'] }}</td>
                                    <td class="p-6 text-center font-black text-gray-700">{{ $data['s'] }}</td>
                                    <td class="p-6 text-center font-black {{ $data['a'] > 0 ? 'text-red-600' : 'text-gray-700' }}">{{ $data['a'] }}</td>
                                    <td class="p-6 text-center">
                                        <div class="flex flex-col items-center gap-1">
                                            <span class="text-xs font-black {{ $data['persen'] >= 90 ? 'text-green-600' : ($data['persen'] >= 75 ? 'text-yellow-600' : 'text-red-600') }}">
                                                {{ $data['persen'] }}%
                                            </span>
                                            <div class="w-16 h-1 bg-gray-100 rounded-full overflow-hidden">
                                                <div class="h-full {{ $data['persen'] >= 90 ? 'bg-green-500' : ($data['persen'] >= 75 ? 'bg-yellow-500' : 'bg-red-500') }}" style="width: {{ $data['persen'] }}%"></div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="p-32 text-center text-gray-300 font-black uppercase text-xs tracking-widest opacity-30">Belum ada data absensi di periode ini</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    @endif
</div>