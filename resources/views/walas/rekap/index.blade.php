<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-emerald-600 rounded-xl flex items-center justify-center shadow-lg shadow-emerald-100">
                <i class="fas fa-file-signature text-white text-sm"></i>
            </div>
            <div>
                <h2 class="font-black text-2xl text-gray-800 tracking-tighter leading-tight">
                    {{ __('Laporan Absensi Bulanan') }}
                </h2>
                <p class="text-[10px] font-bold text-emerald-600 uppercase tracking-widest">Kelas perwalian Wali Kelas</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Memanggil Komponen Livewire Laporan Absensi -->
            <livewire:walas.laporan-absensi-index />
        </div>
    </div>
</x-app-layout>