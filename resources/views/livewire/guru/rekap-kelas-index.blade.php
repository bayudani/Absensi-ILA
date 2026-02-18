<div class="space-y-6">
    <!-- Filter Card -->
    <div class="bg-white p-6 rounded-[2.5rem] border border-emerald-100 shadow-sm">
        <div class="flex flex-col lg:flex-row justify-between items-center gap-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 w-full lg:w-auto">
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
                <button onclick="window.print()" class="flex-1 lg:flex-none bg-white border border-gray-200 text-gray-600 px-6 py-3 rounded-2xl text-xs font-black uppercase tracking-widest hover:bg-gray-50 transition-all shadow-sm">
                    <i class="fas fa-print mr-2"></i> Cetak PDF
                </button>
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-[3rem] border border-gray-100 shadow-sm overflow-hidden relative min-h-[400px]">
        <div wire:loading class="absolute inset-0 bg-white/60 backdrop-blur-[1px] z-10 flex items-center justify-center">
            <div class="flex flex-col items-center">
                <div class="w-10 h-10 border-4 border-emerald-500 border-t-transparent rounded-full animate-spin"></div>
                <p class="text-[10px] font-black text-emerald-600 mt-2 uppercase tracking-widest">Mengkalkulasi...</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 text-gray-400 text-[10px] uppercase font-black tracking-[0.2em] border-b border-gray-50">
                        <th class="p-6">Siswa</th>
                        <th class="p-6 text-center bg-green-50/30 text-green-600">H</th>
                        <th class="p-6 text-center bg-blue-50/30 text-blue-600">I</th>
                        <th class="p-6 text-center bg-yellow-50/30 text-yellow-600">S</th>
                        <th class="p-6 text-center bg-red-50/30 text-red-600">A</th>
                        <th class="p-6 text-center">Persentase</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 font-sans">
                    @forelse($daftarRekap as $rekap)
                        <tr class="hover:bg-emerald-50/20 transition-colors group">
                            <td class="p-6">
                                <div class="flex flex-col">
                                    <span class="font-black text-gray-800 text-sm group-hover:text-emerald-700 transition-colors">{{ $rekap['nama'] }}</span>
                                    <span class="text-[10px] text-gray-400 font-mono mt-1 uppercase">NISN: {{ $rekap['nisn'] }}</span>
                                </div>
                            </td>
                            <td class="p-6 text-center font-black text-gray-700">{{ $rekap['h'] }}</td>
                            <td class="p-6 text-center font-black text-gray-700">{{ $rekap['i'] }}</td>
                            <td class="p-6 text-center font-black text-gray-700">{{ $rekap['s'] }}</td>
                            <td class="p-6 text-center font-black {{ $rekap['a'] > 0 ? 'text-red-600' : 'text-gray-700' }}">
                                {{ $rekap['a'] }}
                            </td>
                            <td class="p-6 text-center">
                                <span class="px-3 py-1 rounded-xl font-black text-xs 
                                    {{ $rekap['persen'] >= 90 ? 'bg-green-100 text-green-700' : ($rekap['persen'] >= 75 ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                                    {{ $rekap['persen'] }}%
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-32 text-center">
                                <div class="flex flex-col items-center opacity-20">
                                    <i class="fas fa-clipboard-list text-6xl mb-4"></i>
                                    <p class="font-black text-xs uppercase tracking-widest text-gray-500">
                                        {{ $filter_kelas ? 'Belum ada data absensi di bulan ini.' : 'Pilih kelas terlebih dahulu.' }}
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>