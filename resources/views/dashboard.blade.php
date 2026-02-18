<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2">
            <i class="fas fa-chart-line text-emerald-600"></i>
            <h2 class="font-bold text-xl text-gray-800 leading-tight">
                {{ __('Ringkasan Akademik') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Welcome Card -->
            <div class="bg-white p-8 rounded-2xl border border-emerald-100 shadow-sm mb-8 relative overflow-hidden">
                <div class="relative z-10">
                    <h3 class="text-2xl font-black text-gray-800 tracking-tight">Halo, {{ Auth::user()->name }}! 👋</h3>
                    <p class="text-sm text-gray-500 mt-1 font-medium">
                        Selamat datang di <span class="italic text-emerald-600 font-bold">E-AbsensiSMPN 3 Siak
                            Kecil</span>. Anda masuk sebagai
                        <span
                            class="px-2 py-1 bg-emerald-100 text-emerald-700 rounded-lg font-bold uppercase text-[9px] ml-1">
                            {{ Auth::user()->role }}
                        </span>
                    </p>
                </div>
                <i
                    class="fas fa-leaf absolute -bottom-6 -right-6 text-emerald-50 text-9xl opacity-40 transform -rotate-12"></i>
            </div>

            <!-- PANGGIL KOMPONEN SFC v4 -->
            @livewire('dashboard.dashboard-stats')

        </div>
    </div>
</x-app-layout>
