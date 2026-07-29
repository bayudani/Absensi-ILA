<div class="space-y-6">
    
    <style>
        @media print {
            /* 1. Sembunyiin SEMUA elemen di body (termasuk sidebar & navbar) */
            body * {
                visibility: hidden;
            }
            /* 2. TAMPILIN cuma area yang dikasih id="printable-area" */
            #printable-area, #printable-area * {
                visibility: visible;
            }
            /* 3. Geser area print ke pojok kiri atas nutupin full layar kertas */
            #printable-area {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                box-shadow: none !important;
                border: none !important;
                border-radius: 0 !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            /* 4. Sembunyiin elemen di dalem tabel yang ga perlu diprint (kayak tombol) */
            .no-print {
                display: none !important;
            }
            /* 5. Tampilin Kop Surat & TTD di halaman print */
            .print-only {
                display: block !important;
            }
            .print-text-black { color: black !important; }
            body { background-color: white !important; }
            tr[x-show] { display: table-row !important; }
            [x-cloak] { display: table-row !important; }
        }
        [x-cloak] { display: none !important; }
    </style>
    <!-- Header & Filter Card (Dikasih class 'no-print' biar ga ikut ke-print) -->
    <div class="bg-white p-6 rounded-[2.5rem] border border-gray-100 shadow-sm no-print">
        <div class="flex flex-col lg:flex-row justify-between items-center gap-6">
            <div class="flex flex-col md:flex-row gap-4 w-full lg:w-auto">
                <!-- Filter Kelas -->
                <div class="flex-1">
                    <label class="text-[10px] font-black text-gray-400 uppercase mb-2 block pl-1">Pilih Kelas</label>
                    <select wire:model.live="filter_kelas" class="w-full px-5 py-3 rounded-2xl border-gray-100 bg-gray-50 focus:bg-white focus:ring-4 focus:ring-emerald-50 text-sm font-bold transition-all outline-none">
                        <option value="">Semua Kelas</option>
                        @foreach($daftarKelas as $k)
                            <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Filter Bulan -->
                <div class="flex-1">
                    <label class="text-[10px] font-black text-gray-400 uppercase mb-2 block pl-1">Bulan</label>
                    <select wire:model.live="filter_bulan" class="w-full px-5 py-3 rounded-2xl border-gray-100 bg-gray-50 focus:bg-white focus:ring-4 focus:ring-emerald-50 text-sm font-bold transition-all outline-none">
                        @foreach($listBulan as $val => $label)
                            <option value="{{ $val }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Filter Tahun -->
                <div class="flex-1">
                    <label class="text-[10px] font-black text-gray-400 uppercase mb-2 block pl-1">Tahun</label>
                    <select wire:model.live="filter_tahun" class="w-full px-5 py-3 rounded-2xl border-gray-100 bg-gray-50 focus:bg-white focus:ring-4 focus:ring-emerald-50 text-sm font-bold transition-all outline-none">
                        @for($y = date('Y'); $y >= 2024; $y--)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endfor
                    </select>
                </div>
            </div>

            <div class="flex gap-2 w-full lg:w-auto self-end lg:self-center">
                <!-- Tombol Cetak PDF -->
                <button onclick="window.print()" class="flex-1 lg:flex-none bg-white border border-gray-200 text-gray-600 px-6 py-3 rounded-2xl text-xs font-black uppercase tracking-widest hover:bg-gray-50 transition-all shadow-sm">
                    <i class="fas fa-print mr-2"></i> Cetak / PDF
                </button>
                
                <!-- Tombol Export Excel -->
                <button wire:click="exportExcel" wire:loading.attr="disabled" class="flex-1 lg:flex-none bg-emerald-600 text-white px-6 py-3 rounded-2xl text-xs font-black uppercase tracking-widest hover:bg-emerald-700 shadow-lg shadow-emerald-100 transition-all active:scale-95 disabled:opacity-50 flex items-center justify-center">
                    <span wire:loading.remove wire:target="exportExcel">
                        <i class="fas fa-file-excel mr-2"></i> Export Excel
                    </span>
                    <span wire:loading wire:target="exportExcel">
                        <i class="fas fa-spinner fa-spin mr-2"></i> ...
                    </span>
                </button>
            </div>
        </div>
    </div>

    <!-- MAIN TABLE CARD (Yang bakal dikasih ID printable-area) -->
    <div id="printable-area" class="bg-white rounded-[3rem] border border-gray-100 shadow-sm overflow-hidden relative p-0 lg:p-4">
        
        <!-- 📄 1. KOP SURAT (Hanya Muncul Pas Print) -->
        <x-kop-surat />

        <div class="hidden print-only text-center mb-8 px-8">
            <h3 class="font-bold text-xl uppercase underline print-text-black">Laporan Rekapitulasi Absensi Siswa</h3>
            <p class="font-medium mt-1 print-text-black">Periode: {{ $listBulan[$filter_bulan] ?? '-' }} {{ $filter_tahun }}</p>
            @php
                $namaKelas = '';
                if($filter_kelas) {
                    $kls = collect($daftarKelas)->firstWhere('id', $filter_kelas);
                    $namaKelas = $kls ? $kls->nama_kelas : '';
                }
            @endphp
            @if($namaKelas)
                <p class="font-medium print-text-black">Kelas: {{ $namaKelas }}</p>
            @endif
        </div>

        <!-- Loading Overlay (Sembunyi pas diprint) -->
        <div wire:loading class="absolute inset-0 bg-white/60 backdrop-blur-[1px] z-10 flex items-center justify-center no-print">
            <div class="flex flex-col items-center">
                <div class="w-10 h-10 border-4 border-emerald-500 border-t-transparent rounded-full animate-spin"></div>
                <p class="text-[10px] font-black text-emerald-600 mt-2 uppercase tracking-widest">Menghitung Data...</p>
            </div>
        </div>

        <!-- TABEL DATA -->
        <div class="overflow-x-auto px-0 lg:px-4">
            <table class="w-full text-left text-sm print-text-black">
                <thead>
                    <tr class="bg-gray-50/50 text-gray-400 text-[10px] uppercase font-black tracking-[0.2em] border-b-2 border-gray-200">
                        <th class="p-4 lg:p-6 w-10 no-print"></th>
                        <th class="p-4 lg:p-6 print-text-black">Data Siswa</th>
                        <th class="p-4 lg:p-6 print-text-black">Kelas</th>
                        <th class="p-4 lg:p-6 text-center text-green-600 bg-green-50/30 print-text-black">Hadir</th>
                        <th class="p-4 lg:p-6 text-center text-blue-600 bg-blue-50/30 print-text-black">Izin</th>
                        <th class="p-4 lg:p-6 text-center text-yellow-600 bg-yellow-50/30 print-text-black">Sakit</th>
                        <th class="p-4 lg:p-6 text-center text-red-600 bg-red-50/30 print-text-black">Alpa</th>
                        <th class="p-4 lg:p-6 text-center print-text-black">% Efektif</th>
                    </tr>
                </thead>
                @forelse($daftarRekap as $rekap)
                <tbody class="border-b border-gray-100 last:border-0 font-sans" x-data="{ open: false }">
                    <tr class="hover:bg-emerald-50/20 transition-colors group cursor-pointer" @click="open = !open">
                        <td class="p-4 lg:p-6 text-center no-print">
                            <i class="fas text-gray-400 transition-transform" :class="open ? 'fa-chevron-down' : 'fa-chevron-right'"></i>
                        </td>
                        <td class="p-4 lg:p-6">
                            <div class="flex flex-col">
                                <span class="font-black text-gray-800 text-sm leading-none print-text-black">{{ $rekap['nama'] }}</span>
                                <span class="text-[10px] text-gray-400 font-mono mt-1.5 uppercase print-text-black">NISN: {{ $rekap['nisn'] }}</span>
                            </div>
                        </td>
                        <td class="p-4 lg:p-6">
                            <span class="px-3 py-1 bg-emerald-100 text-emerald-700 rounded-lg font-black text-[10px] uppercase print-text-black">
                                {{ $rekap['kelas'] }}
                            </span>
                        </td>
                        <td class="p-4 lg:p-6 text-center font-black text-gray-700 print-text-black">{{ $rekap['h'] }}</td>
                        <td class="p-4 lg:p-6 text-center font-black text-gray-700 print-text-black">{{ $rekap['i'] }}</td>
                        <td class="p-4 lg:p-6 text-center font-black text-gray-700 print-text-black">{{ $rekap['s'] }}</td>
                        <td class="p-4 lg:p-6 text-center font-black {{ $rekap['a'] > 0 ? 'text-red-600' : 'text-gray-700' }} print-text-black">
                            {{ $rekap['a'] }}
                        </td>
                        <td class="p-4 lg:p-6 text-center">
                            <span class="px-3 py-1 rounded-xl font-black text-xs print-text-black
                                {{ $rekap['persen'] >= 90 ? 'bg-green-100 text-green-700' : ($rekap['persen'] >= 75 ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                                {{ $rekap['persen'] }}%
                            </span>
                        </td>
                    </tr>
                    <tr x-show="open" x-cloak>
                        <td colspan="8" class="p-0">
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
                                        @forelse($rekap['perMapel'] as $mapel => $stat)
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
                @empty
                <tbody class="font-sans">
                    <tr>
                        <td colspan="8" class="p-16 text-center text-gray-300 font-black uppercase text-xs tracking-widest no-print">Tidak ada data absensi untuk periode ini</td>
                    </tr>
                </tbody>
                @endforelse
            </table>
        </div>

        <!-- 📄 3. TANDA TANGAN KEPSEK (Hanya Muncul Pas Print) -->
        <div class="hidden print-only mt-16 w-full px-8 pb-12">
            <div class="flex justify-end pr-12">
                <div class="text-center print-text-black">
                    <p class="mb-1">Siak Kecil, {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y') }}</p>
                    <p class="font-bold">Kepala Sekolah,</p>
                    <br><br><br><br>
                    <!-- 👇 DATA KEPSEK DINAMIS DARI DATABASE -->
                    <p class="font-bold underline text-lg">{{ $kepsek->nama_lengkap ?? 'Rusiono, S.Pd' }}</p>
                    <p class="font-medium">NIP. {{ $kepsek->nip ?? '197306041999031010' }}</p>
                </div>
            </div>
        </div>

    </div>
</div>