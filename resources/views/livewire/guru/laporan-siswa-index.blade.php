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
            .print-text-black { color: black !important; }
            body { background-color: white !important; }
        }
    </style>

    <!-- Filter Card -->
    <div class="bg-white p-4 sm:p-6 rounded-[2.5rem] border border-emerald-100 shadow-sm no-print">
        <div class="flex flex-col gap-4">
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4 items-end">
                <div class="col-span-2 sm:col-span-1">
                    <label class="text-[10px] font-black text-gray-400 uppercase mb-1 block pl-1">Kelas</label>
                    <select wire:model.live="kelas_id" class="w-full px-4 py-2.5 rounded-xl border-gray-100 bg-gray-50 focus:bg-white focus:ring-4 focus:ring-emerald-50 text-sm font-bold transition-all outline-none">
                        <option value="">-- Pilih --</option>
                        @foreach($daftarKelas as $k)
                            <option value="{{ $k->id }}">Kelas {{ $k->nama_kelas }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-[10px] font-black text-gray-400 uppercase mb-1 block pl-1">Mapel</label>
                    <select wire:model.live="mapel_id" class="w-full px-4 py-2.5 rounded-xl border-gray-100 bg-gray-50 focus:bg-white focus:ring-4 focus:ring-emerald-50 text-sm font-bold transition-all outline-none">
                        <option value="">-- Pilih --</option>
                        @foreach($daftarMapel as $m)
                            <option value="{{ $m->id }}">{{ $m->nama_mapel }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-[10px] font-black text-gray-400 uppercase mb-1 block pl-1">Siswa</label>
                    <select wire:model.live="siswa_id" class="w-full px-4 py-2.5 rounded-xl border-gray-100 bg-gray-50 focus:bg-white focus:ring-4 focus:ring-emerald-50 text-sm font-bold transition-all outline-none">
                        <option value="">-- Pilih --</option>
                        @foreach($daftarSiswa as $s)
                            <option value="{{ $s->id }}">{{ $s->nama_lengkap }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-[10px] font-black text-gray-400 uppercase mb-1 block pl-1">Semester</label>
                    <select wire:model.live="semester" class="w-full px-4 py-2.5 rounded-xl border-gray-100 bg-gray-50 focus:bg-white focus:ring-4 focus:ring-emerald-50 text-sm font-bold transition-all outline-none">
                        @foreach($listSemester as $val => $label)
                            <option value="{{ $val }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-[10px] font-black text-gray-400 uppercase mb-1 block pl-1">Tahun Ajaran</label>
                    <input type="text" wire:model.live="tahun_ajaran" class="w-full px-4 py-2.5 rounded-xl border-gray-100 bg-gray-50 focus:bg-white focus:ring-4 focus:ring-emerald-50 text-sm font-bold transition-all outline-none" placeholder="2024/2025">
                </div>
            </div>
            <div class="flex flex-col sm:flex-row gap-2 w-full">
                <button wire:click="lihatLaporan" wire:loading.attr="disabled" class="w-full sm:w-auto bg-emerald-600 text-white px-6 py-2.5 rounded-xl text-xs font-black uppercase tracking-[0.2em] shadow-lg shadow-emerald-100 hover:bg-emerald-700 transition-all active:scale-95 disabled:opacity-50">
                    <span wire:loading.remove wire:target="lihatLaporan"><i class="fas fa-search mr-2"></i> Lihat Laporan</span>
                    <span wire:loading wire:target="lihatLaporan"><i class="fas fa-spinner fa-spin mr-2"></i> Memuat...</span>
                </button>
                @if(count($dataAbsensi) > 0)
                <button onclick="window.print()" class="w-full sm:w-auto bg-white border border-gray-200 text-gray-600 px-5 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-gray-50 transition-all shadow-sm">
                    <i class="fas fa-print mr-2"></i> Cetak PDF
                </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Hasil Laporan -->
    <div id="printable-area" class="bg-white rounded-[3rem] border border-gray-100 shadow-sm overflow-hidden relative min-h-[400px] p-0 lg:p-4">
        <x-kop-surat />

        @if(count($dataAbsensi) > 0 && $selectedSiswa && $selectedMapel)
        <div class="hidden print-only text-center mb-8 px-8">
            <h3 class="font-bold text-xl uppercase underline print-text-black">Laporan Rekap Absensi Siswa</h3>
            <p class="font-medium mt-1 print-text-black">Nama: {{ $selectedSiswa->nama_lengkap }}</p>
            <p class="font-medium mt-1 print-text-black">NISN: {{ $selectedSiswa->nisn }}</p>
            <p class="font-medium mt-1 print-text-black">Kelas: {{ $selectedSiswa->kelas->nama_kelas ?? '-' }}</p>
            <p class="font-medium mt-1 print-text-black">Mata Pelajaran: {{ $selectedMapel->nama_mapel }}</p>
            <p class="font-medium mt-1 print-text-black">Periode: Semester {{ $listSemester[$semester] }} {{ $tahun_ajaran }}</p>
            <p class="font-medium mt-1 print-text-black">Guru: {{ Auth::user()->name }}</p>
        </div>

        <div class="overflow-x-auto px-0 lg:px-4">
            <table class="w-full text-left text-sm border-collapse print-text-black">
                <thead>
                    <tr class="bg-gray-50/50 text-gray-400 text-[10px] uppercase font-black tracking-[0.2em] border-b-2 border-gray-200">
                        <th class="p-4 lg:p-6 print-text-black">No</th>
                        <th class="p-4 lg:p-6 print-text-black">Tanggal</th>
                        <th class="p-4 lg:p-6 text-center print-text-black">Status</th>
                        <th class="p-4 lg:p-6 print-text-black">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 font-sans">
                    @forelse($dataAbsensi['records'] as $idx => $absen)
                    <tr class="hover:bg-emerald-50/20 transition-colors group">
                        <td class="p-4 lg:p-6 font-mono text-xs text-gray-400 print-text-black">{{ $idx + 1 }}</td>
                        <td class="p-4 lg:p-6 font-bold text-gray-800 print-text-black">{{ \Carbon\Carbon::parse($absen->tanggal)->locale('id')->translatedFormat('d F Y') }}</td>
                        <td class="p-4 lg:p-6 text-center">
                            @if($absen->status === 'H')
                                <span class="px-3 py-1 bg-green-100 text-green-700 rounded-lg text-[10px] font-black print-text-black">HADIR</span>
                            @elseif($absen->status === 'S')
                                <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-lg text-[10px] font-black print-text-black">SAKIT</span>
                            @elseif($absen->status === 'I')
                                <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-lg text-[10px] font-black print-text-black">IZIN</span>
                            @elseif($absen->status === 'A')
                                <span class="px-3 py-1 bg-red-100 text-red-700 rounded-lg text-[10px] font-black print-text-black">ALPHA</span>
                            @else
                                <span class="text-gray-400 print-text-black">-</span>
                            @endif
                        </td>
                        <td class="p-4 lg:p-6 text-sm text-gray-600 print-text-black">{{ $absen->keterangan ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="p-20 text-center no-print">
                            <div class="flex flex-col items-center opacity-20">
                                <i class="fas fa-clipboard-list text-6xl mb-4"></i>
                                <p class="font-black text-xs uppercase tracking-widest text-gray-500">Belum ada data absensi di periode ini</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-0 lg:px-4 mt-6">
            <table class="w-full text-left text-sm border-collapse print-text-black">
                <thead>
                    <tr class="bg-gray-50/50 text-gray-400 text-[10px] uppercase font-black tracking-[0.2em] border-b-2 border-gray-200">
                        <th colspan="6" class="p-4 lg:p-6 print-text-black">Rekapitulasi Kehadiran</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 font-sans">
                    <tr>
                        <td class="p-4 lg:p-6 font-black print-text-black">Total Pertemuan</td>
                        <td class="p-4 lg:p-6 font-black text-gray-800 print-text-black">{{ $dataAbsensi['total'] }} kali</td>
                        <td class="p-4 lg:p-6 font-black text-green-600 print-text-black">Hadir (H)</td>
                        <td class="p-4 lg:p-6 font-black text-green-700 print-text-black">{{ $dataAbsensi['h'] }}</td>
                        <td class="p-4 lg:p-6 font-black text-yellow-600 print-text-black">Sakit (S)</td>
                        <td class="p-4 lg:p-6 font-black text-yellow-700 print-text-black">{{ $dataAbsensi['s'] }}</td>
                    </tr>
                    <tr>
                        <td class="p-4 lg:p-6 font-black print-text-black">Persentase Kehadiran</td>
                        <td class="p-4 lg:p-6 font-black text-gray-800 print-text-black">{{ $dataAbsensi['persen'] }}%</td>
                        <td class="p-4 lg:p-6 font-black text-blue-600 print-text-black">Izin (I)</td>
                        <td class="p-4 lg:p-6 font-black text-blue-700 print-text-black">{{ $dataAbsensi['i'] }}</td>
                        <td class="p-4 lg:p-6 font-black text-red-600 print-text-black">Alpha (A)</td>
                        <td class="p-4 lg:p-6 font-black text-red-700 print-text-black">{{ $dataAbsensi['a'] }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="hidden print-only mt-16 w-full px-8 pb-12">
            <div class="flex justify-between items-end">
                <div class="text-center print-text-black">
                    <p class="mb-1">Mengetahui,</p>
                    <p class="font-bold">Kepala Sekolah,</p>
                    <br><br><br><br>
                    <p class="font-bold underline text-lg">{{ $kepsek->nama_lengkap ?? 'Rusiono, S.Pd' }}</p>
                    <p class="font-medium">NIP. {{ $kepsek->nip ?? '197306041999031010' }}</p>
                </div>
                @php
                    $today = \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y');
                @endphp
                <div class="text-center print-text-black">
                    <p class="mb-1">Siak Kecil, {{ $today }}</p>
                    <p class="font-bold">Guru Mapel,</p>
                    <br><br><br><br>
                    <p class="font-bold underline text-lg">{{ Auth::user()->name }}</p>
                    <p class="font-medium">NIP. {{ Auth::user()->guru->nip ?? '-' }}</p>
                </div>
            </div>
        </div>
        @else
        <div class="flex flex-col items-center justify-center py-32 opacity-20 no-print">
            <i class="fas fa-file-alt text-7xl mb-6 text-gray-400"></i>
            <p class="font-black text-sm uppercase tracking-widest text-gray-500">Pilih filter dan klik "Lihat Laporan"</p>
        </div>
        @endif

        <div wire:loading wire:target="lihatLaporan" class="absolute inset-0 bg-white/60 backdrop-blur-[1px] z-10 flex items-center justify-center no-print">
            <div class="w-10 h-10 border-4 border-emerald-500 border-t-transparent rounded-full animate-spin"></div>
        </div>
    </div>
</div>
