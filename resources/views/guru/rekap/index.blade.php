<x-app-layout>
    <!-- Header Page (Opsional, karena sudah ada Info Guru di komponen) -->
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-emerald-600 rounded-xl flex items-center justify-center shadow-lg shadow-emerald-100">
                <i class="fas fa-calendar-day text-white text-sm"></i>
            </div>
            <h2 class="font-black text-2xl text-gray-800 tracking-tighter leading-tight">
                {{ __('Rekap Kelas') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Panggil Komponen Livewire Jadwal Guru -->
            <livewire:guru.rekap-kelas-index />
        </div>
    </div>
</x-app-layout>