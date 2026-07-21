<div class="space-y-6">
    <style>
        @media print {
            body * { visibility: hidden; }
            #printable-area, #printable-area * { visibility: visible; }
            #printable-area {
                position: absolute; left: 0; top: 0; width: 100%;
                box-shadow: none !important; border: none !important;
                border-radius: 0 !important; padding: 0 !important; margin: 0 !important;
            }
            .no-print { display: none !important; }
            .print-only { display: block !important; }
            .print-only-flex { display: flex !important; }
            .print-text-black { color: black !important; }
            .print-text-black th { color: black !important; }
            tr[x-show] { display: table-row !important; }
            [x-cloak] { display: table-row !important; }
            body { background-color: white !important; }
            #printable-area table thead tr {
                background-color: #e5e7eb !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            #printable-area table thead th {
                color: black !important;
                font-weight: 900 !important;
                border-bottom-width: 2px !important;
                border-bottom-color: #374151 !important;
            }
            #printable-area table thead th .print-text-black {
                color: black !important;
            }
            #printable-area .print-header-bg {
                background-color: #e5e7eb !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color: black !important;
            }
        }
        [x-cloak] { display: none !important; }
    </style>

    @if(!$kelas)
        <div class="bg-red-50 border border-red-100 p-10 rounded-[3rem] text-center no-print">
            <i class="fas fa-user-slash text-red-400 text-4xl mb-4"></i>
            <h3 class="font-black text-red-800 uppercase tracking-tighter">Akses Terbatas</h3>
            <p class="text-sm text-red-600">Sistem tidak menemukan kelas perwalian atas nama Anda.</p>
        </div>
    @else
        <!-- Filter & Tab Switcher -->
        <div class="bg-white p-6 rounded-[2.5rem] border border-emerald-100 shadow-sm no-print">
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
                <div class="flex flex-wrap gap-4 w-full lg:w-auto justify-center lg:justify-end items-center">
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
                    
                    <div class="flex gap-2">
                        <button onclick="window.print()" class="bg-white border border-gray-200 text-gray-600 px-6 py-3 rounded-2xl text-xs font-black uppercase tracking-widest hover:bg-gray-50 transition-all shadow-sm">
                            <i class="fas fa-print lg:mr-2"></i> <span class="hidden lg:inline">Cetak PDF</span>
                        </button>
                        <button wire:click="exportExcel" wire:loading.attr="disabled" class="bg-emerald-600 text-white px-6 py-3 rounded-2xl text-xs font-black uppercase tracking-widest hover:bg-emerald-700 shadow-lg shadow-emerald-100 transition-all active:scale-95 disabled:opacity-50 flex items-center justify-center">
                            <span wire:loading.remove wire:target="exportExcel">
                                <i class="fas fa-file-excel lg:mr-2"></i> <span class="hidden lg:inline">Export Excel</span>
                            </span>
                            <span wire:loading wire:target="exportExcel">
                                <i class="fas fa-spinner fa-spin lg:mr-2"></i> <span class="hidden lg:inline">...</span>
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- LOGIKA SISTEM (Sembunyi pas diprint) -->
        <div class="bg-amber-50 border border-amber-200 p-6 rounded-[2rem] flex items-start gap-4 no-print">
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
        <div id="printable-area" class="bg-white rounded-[3rem] border border-gray-100 shadow-sm overflow-hidden relative min-h-[400px] p-0 lg:p-4">
            
            <!-- 📄 1. KOP SURAT  -->
            <x-kop-surat />

            <!-- 📄 2. JUDUL LAPORAN DINAMIS -->
            <div class="hidden print-only text-center mb-8 px-8">
                @if($view_type === 'harian')
                    <h3 class="font-bold text-xl uppercase underline print-text-black">Laporan Absensi Harian Kelas</h3>
                    <p class="font-medium mt-1 print-text-black">Tanggal: {{ \Carbon\Carbon::parse($filter_tanggal)->locale('id')->translatedFormat('d F Y') }}</p>
                @else
                    <h3 class="font-bold text-xl uppercase underline print-text-black">Laporan Rekapitulasi Bulanan Kelas</h3>
                    <p class="font-medium mt-1 print-text-black">Periode: {{ $listBulan[$filter_bulan] ?? '-' }} {{ $filter_tahun }}</p>
                @endif
                <p class="font-medium print-text-black mt-1">Kelas: {{ $kelas->nama_kelas }}</p>
                <p class="font-medium print-text-black">Wali Kelas: {{ Auth::user()->name }}</p>
            </div>

            <div wire:loading class="absolute inset-0 bg-white/60 backdrop-blur-[1px] z-10 flex items-center justify-center no-print">
                <div class="w-10 h-10 border-4 border-emerald-500 border-t-transparent rounded-full animate-spin"></div>
            </div>

            <div class="overflow-x-auto px-0 lg:px-4">
                @if($view_type === 'harian')
                    <!-- VIEW 1: MATRIX HARIAN -->
                    <table class="w-full text-left text-sm border-collapse print-text-black">
                        <thead>
                            <tr class="bg-gray-50/50 text-gray-400 text-[10px] uppercase font-black tracking-[0.2em] border-b-2 border-gray-200 print-header-bg">
                                <th class="p-4 lg:p-6 print-text-black">Siswa</th>
                                @foreach($jamPelajaran as $jam)
                                    <th class="p-4 lg:p-6 text-center border-l border-gray-100 print-text-black">{{ $jam->jam_mulai }}<br><span class="text-[8px] font-bold text-emerald-500 print-text-black">{{ $jam->mapel->kode_mapel }}</span></th>
                                @endforeach
                                <th class="p-4 lg:p-6 text-center bg-gray-50/80 print-text-black">Kesimpulan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 font-sans">
                            @forelse($rekapHarian as $row)
                                <tr class="hover:bg-emerald-50/20 transition-colors group {{ $row['status_akhir'] === 'BOLOS' ? 'bg-red-50/50' : '' }}">
                                    <td class="p-4 lg:p-6">
                                        <div class="flex flex-col leading-none">
                                            <span class="font-black text-gray-800 text-sm group-hover:text-emerald-700 transition-colors print-text-black">{{ $row['nama'] }}</span>
                                            <span class="text-[9px] text-gray-400 font-mono mt-1 uppercase print-text-black">NISN: {{ $row['nisn'] }}</span>
                                        </div>
                                    </td>
                                    @foreach($jamPelajaran as $jam)
                                        <td class="p-4 lg:p-6 text-center border-l border-gray-100 print-text-black">
                                            @php $status = $row['logs'][$jam->id] ?? 'X'; @endphp
                                            @if($status === 'H')
                                                <i class="fas fa-check-circle text-emerald-500 print-text-black"></i>
                                            @elseif($status === 'A')
                                                <span class="bg-red-100 text-red-600 px-2 py-0.5 rounded text-[9px] font-black print-text-black">ALPHA</span>
                                            @else
                                                <span class="text-gray-200 print-text-black">-</span>
                                            @endif
                                        </td>
                                    @endforeach
                                    <td class="p-4 lg:p-6 text-center">
                                        @if($row['status_akhir'] === 'HADIR')
                                            <span class="px-3 py-1 bg-green-100 text-green-700 rounded-lg text-[10px] font-black uppercase tracking-wider print-text-black">Hadir Penuh</span>
                                        @elseif($row['status_akhir'] === 'BOLOS')
                                            <div class="flex flex-col items-center">
                                                <span class="px-3 py-1 bg-red-600 text-white rounded-lg text-[10px] font-black uppercase tracking-wider animate-pulse shadow-lg shadow-red-100 print-text-black">Indikasi Bolos</span>
                                            </div>
                                        @else
                                            <span class="px-3 py-1 bg-gray-100 text-gray-400 rounded-lg text-[10px] font-black uppercase tracking-wider print-text-black">Tanpa Data</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="10" class="p-32 text-center text-gray-300 font-black uppercase text-xs tracking-widest opacity-30 no-print">Tidak ada jadwal/data di tanggal ini</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                @else
                    <!-- VIEW 2: REKAP BULANAN -->
                    <table class="w-full text-left text-sm border-collapse print-text-black">
                        <thead>
                            <tr class="bg-gray-50/50 text-gray-400 text-[10px] uppercase font-black tracking-[0.2em] border-b-2 border-gray-200 print-header-bg">
                                <th class="p-4 lg:p-6 print-text-black w-10 no-print"></th>
                                <th class="p-4 lg:p-6 print-text-black">Siswa</th>
                                <th class="p-4 lg:p-6 text-center text-green-600 bg-green-50/30 print-text-black">H</th>
                                <th class="p-4 lg:p-6 text-center text-blue-600 bg-blue-50/30 print-text-black">I</th>
                                <th class="p-4 lg:p-6 text-center text-yellow-600 bg-yellow-50/30 print-text-black">S</th>
                                <th class="p-4 lg:p-6 text-center text-red-600 bg-red-50/30 print-text-black">A</th>
                                <th class="p-4 lg:p-6 text-center print-text-black">Persentase</th>
                            </tr>
                        </thead>
                        @if($rekapData->count() > 0)
                            @foreach($rekapData as $data)
                            <tbody class="border-b border-gray-100 last:border-0 font-sans" x-data="{ open: false }">
                                <tr class="hover:bg-emerald-50/20 transition-colors group cursor-pointer" @click="open = !open">
                                    <td class="p-4 lg:p-6 text-center no-print">
                                        <i class="fas text-gray-400 transition-transform" :class="open ? 'fa-chevron-down' : 'fa-chevron-right'"></i>
                                    </td>
                                    <td class="p-4 lg:p-6">
                                        <div class="flex flex-col leading-none">
                                            <span class="font-black text-gray-800 text-sm group-hover:text-emerald-700 transition-colors print-text-black">{{ $data['nama'] }}</span>
                                            <span class="text-[10px] text-gray-400 font-mono mt-1.5 uppercase print-text-black">NISN: {{ $data['nisn'] }}</span>
                                        </div>
                                    </td>
                                    <td class="p-4 lg:p-6 text-center font-black text-gray-700 print-text-black">{{ $data['h'] }}</td>
                                    <td class="p-4 lg:p-6 text-center font-black text-gray-700 print-text-black">{{ $data['i'] }}</td>
                                    <td class="p-4 lg:p-6 text-center font-black text-gray-700 print-text-black">{{ $data['s'] }}</td>
                                    <td class="p-4 lg:p-6 text-center font-black {{ $data['a'] > 0 ? 'text-red-600' : 'text-gray-700' }} print-text-black">{{ $data['a'] }}</td>
                                    <td class="p-4 lg:p-6 text-center">
                                        <span class="px-3 py-1 rounded-xl font-black text-xs print-text-black
                                            {{ $data['persen'] >= 90 ? 'bg-green-100 text-green-700' : ($data['persen'] >= 75 ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                                            {{ $data['persen'] }}%
                                        </span>
                                    </td>
                                </tr>
                                <tr x-show="open" x-cloak>
                                    <td colspan="7" class="p-0">
                                        <div class="px-6 lg:px-10 py-4 border-t border-gray-100">
                                            <table class="w-full text-xs">
                                                <thead>
                                                    <tr class="text-gray-500 font-black uppercase tracking-widest text-[9px] border-b border-gray-200">
                                                        <th class="py-2 pr-4 text-left print-text-black">Mata Pelajaran</th>
                                                        <th class="py-2 px-3 text-center text-green-600 print-text-black">H</th>
                                                        <th class="py-2 px-3 text-center text-blue-600 print-text-black">I</th>
                                                        <th class="py-2 px-3 text-center text-yellow-600 print-text-black">S</th>
                                                        <th class="py-2 px-3 text-center text-red-600 print-text-black">A</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($data['perMapel'] as $mapel => $stat)
                                                    <tr class="border-b border-gray-50 last:border-0">
                                                        <td class="py-2.5 pr-4 font-bold text-gray-700 print-text-black">{{ $mapel }}</td>
                                                        <td class="py-2.5 px-3 text-center font-black text-green-700 print-text-black">{{ $stat['h'] }}</td>
                                                        <td class="py-2.5 px-3 text-center font-black text-blue-700 print-text-black">{{ $stat['i'] }}</td>
                                                        <td class="py-2.5 px-3 text-center font-black text-yellow-700 print-text-black">{{ $stat['s'] }}</td>
                                                        <td class="py-2.5 px-3 text-center font-black {{ $stat['a'] > 0 ? 'text-red-600' : 'text-gray-400' }} print-text-black">{{ $stat['a'] }}</td>
                                                    </tr>
                                                    @empty
                                                    <tr>
                                                        <td colspan="5" class="py-4 text-center text-gray-400 italic text-[11px] print-text-black">Belum ada data absensi untuk siswa ini pada periode tersebut</td>
                                                    </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                            @endforeach
                        @else
                            <tbody class="font-sans">
                                <tr><td colspan="8" class="p-32 text-center text-gray-300 font-black uppercase text-xs tracking-widest opacity-30 no-print">Belum ada data absensi di periode ini</td></tr>
                            </tbody>
                        @endif
                    </table>
                @endif
            </div>

            <!-- 📄 3. TANDA TANGAN KEPSEK (Hanya Muncul Pas Print) -->
            <div class="hidden print-only mt-16 w-full px-8 pb-12">
                <div class="flex justify-end pr-12">
                    <div class="text-center print-text-black">
                        <p class="mb-1">Siak Kecil, {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y') }}</p>
                        <p class="font-bold">Kepala Sekolah,</p>
                        <br><br><br><br>
                        <p class="font-bold underline text-lg">{{ $kepsek->nama_lengkap ?? 'Rusiono, S.Pd' }}</p>
                        <p class="font-medium">NIP. {{ $kepsek->nip ?? '197306041999031010' }}</p>
                    </div>
                </div>
            </div>

        </div>
    @endif
</div>