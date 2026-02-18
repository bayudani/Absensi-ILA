<div class="space-y-6">
    <!-- Header Informasi -->
    <div class="bg-white p-8 rounded-[2.5rem] border border-emerald-100 shadow-sm relative overflow-hidden">
        <div class="relative z-10">
            <h3 class="text-2xl font-black text-gray-800 tracking-tight">Jadwal Mengajar Anda</h3>
            <p class="text-sm text-gray-500 mt-1 font-medium italic">
                Klik pada tombol <span class="text-emerald-600 font-bold">Absen</span> untuk mulai menginput kehadiran siswa.
            </p>
        </div>
        <i class="fas fa-calendar-check absolute -bottom-4 -right-4 text-emerald-50 text-8xl opacity-60 transform rotate-12"></i>
    </div>

    <!-- Tabel Matriks Jadwal -->
    <div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-center border-collapse text-sm">
                <thead>
                    <tr class="bg-gray-800 text-white font-black uppercase text-[11px] tracking-widest">
                        <th class="p-5 border border-gray-700 w-32">Jam / Waktu</th>
                        @foreach($listHari as $hari)
                            <th class="p-5 border border-gray-700">{{ $hari }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 font-sans">
                    @php
                        // Kita ambil semua jam mulai unik untuk jadi baris tabel
                        $timeSlots = collect();
                        foreach($daftarJadwal as $hari => $items) {
                            foreach($items as $j) {
                                $timeSlots->push(substr($j->jam_mulai, 0, 5) . ' - ' . substr($j->jam_selesai, 0, 5));
                            }
                        }
                        $timeSlots = $timeSlots->unique()->sort()->values();
                    @endphp

                    @forelse($timeSlots as $slot)
                        <tr class="hover:bg-emerald-50/20 transition-colors group">
                            <!-- Kolom Waktu -->
                            <td class="p-4 border border-gray-50 bg-gray-50/50 font-mono text-xs font-bold text-emerald-600">
                                {{ $slot }}
                            </td>

                            <!-- Kolom Per Hari -->
                            @foreach($listHari as $hari)
                                @php
                                    // Cari apakah ada jadwal di jam & hari ini
                                    $currentJadwal = $daftarJadwal->get($hari)?->first(function($item) use ($slot) {
                                        $itemSlot = substr($item->jam_mulai, 0, 5) . ' - ' . substr($item->jam_selesai, 0, 5);
                                        return $itemSlot === $slot;
                                    });
                                @endphp

                                <td class="p-3 border border-gray-50 relative min-w-[150px]">
                                    @if($currentJadwal)
                                        <div class="flex flex-col h-full justify-between gap-2">
                                            <div>
                                                <div class="text-[10px] font-black text-gray-400 uppercase tracking-tighter mb-1">
                                                    Kelas {{ $currentJadwal->kelas->nama_kelas }}
                                                </div>
                                                <div class="font-bold text-gray-800 leading-tight">
                                                    {{ $currentJadwal->mapel->nama_mapel }}
                                                </div>
                                            </div>
                                            
                                            <!-- Tombol Absen Mini -->
                                            <a href="{{ route('guru.absensi.create', ['jadwal_id' => $currentJadwal->id]) }}" 
                                               class="mt-2 inline-flex items-center justify-center gap-1.5 py-1.5 px-3 bg-emerald-600 text-white rounded-lg text-[9px] font-black uppercase tracking-wider shadow-sm hover:bg-emerald-700 transition-all active:scale-95 group-hover:shadow-md">
                                                <i class="fas fa-edit"></i> Absen
                                            </a>
                                        </div>
                                    @else
                                        <!-- Slot Kosong -->
                                        <div class="py-4 opacity-10">
                                            <i class="fas fa-minus text-gray-300"></i>
                                        </div>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-20 text-center">
                                <div class="flex flex-col items-center opacity-20">
                                    <i class="fas fa-calendar-times text-6xl mb-4 text-gray-400"></i>
                                    <p class="font-black text-sm uppercase tracking-widest text-gray-500">Belum Ada Data Jadwal</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Info Footer -->
    <div class="bg-blue-50 p-4 rounded-2xl border border-blue-100 flex items-start gap-3">
        <i class="fas fa-info-circle text-blue-500 mt-0.5"></i>
        <p class="text-xs text-blue-700 leading-relaxed font-medium">
            <strong>Catatan:</strong> Jika terdapat bentrok jam mengajar atau data mata pelajaran yang tidak sesuai, harap segera hubungi bagian Kurikulum/Admin TU untuk pembaruan data master.
        </p>
    </div>
</div>