<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-emerald-600 rounded-xl flex items-center justify-center shadow-lg shadow-emerald-100">
                <i class="fas fa-clipboard-list text-white text-sm"></i>
            </div>
            <h2 class="font-black text-2xl text-gray-800 tracking-tighter leading-tight">
                {{ __('Rekapitulasi Kehadiran') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Memanggil Komponen Livewire Rekap -->
            <livewire:admin.rekap-index />
        </div>
    </div>
</x-app-layout>