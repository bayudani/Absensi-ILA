<div class="space-y-6">
    
    <style>
        @media print {
            body * {
                visibility: hidden;
            }
            #printable-area, #printable-area * {
                visibility: visible;
            }
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
            .no-print {
                display: none !important;
            }
            .print-only {
                display: block !important;
            }
            .print-text-black { color: black !important; }
            body { background-color: white !important; }
        }
    </style>

    <!-- Filter Card -->
    <div class="bg-white p-6 rounded-[2.5rem] border border-emerald-100 shadow-sm no-print">
        <div class="flex flex-col lg:flex-row justify-between items-center gap-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 w-full lg:w-auto">
                <!-- Pilih Kelas -->
                <div>
                    <label class="text-[10px] font-black text-gray-400 uppercase mb-2 block pl-1">Kelas Anda</label>
                    <select wire:model.live="filter_kelas" class="w-full px-5 py-3 rounded-2xl border-gray-100 bg-gray-50 focus:bg-white focus:ring-4 focus:ring-emerald-50 text-sm font-bold transition-all outline-none">
                        <option value="">-- Pilih Kelas --</option>
                        @foreach($daftarKelas as $k)
                            <option value="{{ $k->id }}">Kelas {{ $k->nama_kelas }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Pilih Mapel -->
                <div>
                    <label class="text-[10px] font-black text-gray-400 uppercase mb-2 block pl-1">Mata Pelajaran</label>
                    <select wire:model.live="filter_mapel" class="w-full px-5 py-3 rounded-2xl border-gray-100 bg-gray-50 focus:bg-white focus:ring-4 focus:ring-emerald-50 text-sm font-bold transition-all outline-none">
                        <option value="">-- Pilih Mapel --</option>
                        @foreach($daftarMapel as $m)
                            <option value="{{ $m->id }}">{{ $m->nama_mapel }}</option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Pilih Bulan -->
                <div>
                    <label class="text-[10px] font-black text-gray-400 uppercase mb-2 block pl-1">Bulan</label>
                    <select wire:model.live="filter_bulan" class="w-full px-5 py-3 rounded-2xl border-gray-100 bg-gray-50 focus:bg-white focus:ring-4 focus:ring-emerald-50 text-sm font-bold transition-all outline-none">
                        @foreach($listBulan as $val => $label)
                            <option value="{{ $val }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Pilih Tahun -->
                <div>
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
                    <i class="fas fa-print mr-2"></i> Cetak PDF
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

    <!-- Table Card (Yang bakal dikasih ID printable-area) -->
    <div id="printable-area" class="bg-white rounded-[3rem] border border-gray-100 shadow-sm overflow-hidden relative min-h-[400px] p-0 lg:p-4">
        
        <!-- 📄 1. KOP SURAT (Hanya Muncul Pas Print) -->
        <x-kop-surat />

        <!-- 📄 2. JUDUL LAPORAN (Hanya Muncul Pas Print) -->
        <div class="hidden print-only text-center mb-8 px-8">
            <h3 class="font-bold text-xl uppercase underline print-text-black">Laporan Absensi Mata Pelajaran</h3>
            <p class="font-medium mt-1 print-text-black">Guru: {{ Auth::user()->name }}</p>
            <p class="font-medium mt-1 print-text-black">Periode: {{ $listBulan[$filter_bulan] ?? '-' }} {{ $filter_tahun }}</p>
            @php
                $namaKelas = '';
                if($filter_kelas) {
                    $kls = collect($daftarKelas)->firstWhere('id', $filter_kelas);
                    $namaKelas = $kls ? $kls->nama_kelas : '';
                }
                $namaMapel = '';
                if($filter_mapel) {
                    $mpl = collect($daftarMapel)->firstWhere('id', $filter_mapel);
                    $namaMapel = $mpl ? $mpl->nama_mapel : '';
                }
            @endphp
            @if($namaKelas)
                <p class="font-medium print-text-black">Kelas: {{ $namaKelas }}</p>
            @endif
            @if($namaMapel)
                <p class="font-medium print-text-black">Mata Pelajaran: {{ $namaMapel }}</p>
            @endif
        </div>

        <!-- Loading -->
        <div wire:loading class="absolute inset-0 bg-white/60 backdrop-blur-[1px] z-10 flex items-center justify-center no-print">
            <div class="flex flex-col items-center">
                <div class="w-10 h-10 border-4 border-emerald-500 border-t-transparent rounded-full animate-spin"></div>
                <p class="text-[10px] font-black text-emerald-600 mt-2 uppercase tracking-widest">Mengkalkulasi...</p>
            </div>
        </div>

        <div class="overflow-x-auto px-0 lg:px-4">
            <table class="w-full text-left text-sm border-collapse print-text-black">
                <thead>
                    <tr class="bg-gray-50/50 text-gray-400 text-[10px] uppercase font-black tracking-[0.2em] border-b-2 border-gray-200">
                        <th class="p-4 lg:p-6 print-text-black">Siswa</th>
                        <th class="p-4 lg:p-6 text-center bg-green-50/30 text-green-600 print-text-black">H</th>
                        <th class="p-4 lg:p-6 text-center bg-blue-50/30 text-blue-600 print-text-black">I</th>
                        <th class="p-4 lg:p-6 text-center bg-yellow-50/30 text-yellow-600 print-text-black">S</th>
                        <th class="p-4 lg:p-6 text-center bg-red-50/30 text-red-600 print-text-black">A</th>
                        <th class="p-4 lg:p-6 text-center print-text-black">Persentase</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 font-sans">
                    @forelse($daftarRekap as $rekap)
                        <tr class="hover:bg-emerald-50/20 transition-colors group">
                            <td class="p-4 lg:p-6">
                                <div class="flex flex-col">
                                    <span class="font-black text-gray-800 text-sm group-hover:text-emerald-700 transition-colors print-text-black">{{ $rekap['nama'] }}</span>
                                    <span class="text-[10px] text-gray-400 font-mono mt-1 uppercase print-text-black">NISN: {{ $rekap['nisn'] }}</span>
                                </div>
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
                    @empty
                        <tr>
                            <td colspan="6" class="p-32 text-center no-print">
                                <div class="flex flex-col items-center opacity-20">
                                    <i class="fas fa-clipboard-list text-6xl mb-4"></i>
                                    <p class="font-black text-xs uppercase tracking-widest text-gray-500">
                                        {{ $filter_kelas && $filter_mapel ? 'Belum ada data absensi di bulan ini.' : ($filter_kelas ? 'Pilih mata pelajaran terlebih dahulu.' : 'Pilih kelas terlebih dahulu.') }}
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
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
</div>