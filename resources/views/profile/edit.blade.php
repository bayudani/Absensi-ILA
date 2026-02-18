<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-emerald-600 rounded-xl flex items-center justify-center shadow-lg shadow-emerald-100">
                <i class="fas fa-user-cog text-white text-sm"></i>
            </div>
            <div>
                <h2 class="font-black text-2xl text-gray-800 tracking-tighter leading-tight">
                    {{ __('Pengaturan Akun') }}
                </h2>
                <p class="text-[10px] font-bold text-emerald-600 uppercase tracking-widest">Informasi Profil & Keamanan</p>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            <!-- Card Identitas Pengguna (User Identity) -->
            <div class="bg-white p-8 rounded-[3.5rem] border border-emerald-100 shadow-sm relative overflow-hidden flex flex-col md:flex-row items-center gap-8">
                <!-- Aksen Dekoratif -->
                <div class="absolute -top-10 -right-10 w-40 h-40 bg-emerald-50 rounded-full opacity-50"></div>
                <div class="absolute -bottom-10 -left-10 w-24 h-24 bg-emerald-50 rounded-full opacity-50"></div>

                <!-- Avatar Inisial -->
                <div class="relative z-10 w-28 h-28 rounded-[2.5rem] bg-emerald-600 flex items-center justify-center text-white text-4xl font-black shadow-2xl shadow-emerald-100 transform rotate-3">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>

                <div class="relative z-10 text-center md:text-left flex-grow">
                    <div class="inline-flex items-center px-3 py-1 rounded-lg bg-emerald-50 text-emerald-700 text-[10px] font-black uppercase tracking-widest border border-emerald-100 mb-3">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-2 animate-pulse"></span>
                        Status: {{ strtoupper(Auth::user()->role) }}
                    </div>
                    <h3 class="text-3xl font-black text-gray-800 tracking-tight leading-none">{{ Auth::user()->name }}</h3>
                    <div class="mt-2 flex flex-wrap items-center justify-center md:justify-start gap-4 text-gray-400 font-bold text-xs uppercase tracking-tighter">
                        <span class="flex items-center gap-1.5">
                            <i class="fas fa-id-card text-emerald-500"></i> NIP: {{ Auth::user()->username }}
                        </span>
                        @if(Auth::user()->email)
                        <span class="flex items-center gap-1.5">
                            <i class="fas fa-envelope text-emerald-500"></i> {{ Auth::user()->email }}
                        </span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Box: Informasi Profil -->
                <div class="p-8 sm:p-10 bg-white border border-gray-100 shadow-sm rounded-[3rem] hover:shadow-md transition-all group">
                    <div class="max-w-xl">
                        <div class="flex items-center gap-3 mb-8 border-b border-gray-50 pb-5">
                            <div class="w-8 h-8 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center text-xs group-hover:scale-110 transition-transform">
                                <i class="fas fa-id-badge"></i>
                            </div>
                            <h4 class="font-black text-gray-800 text-xs uppercase tracking-[0.2em]">Data Dasar Pengguna</h4>
                        </div>
                        
                        <div class="emerald-form-wrapper">
                            @include('profile.partials.update-profile-information-form')
                        </div>
                    </div>
                </div>

                <!-- Box: Update Password -->
                <div class="p-8 sm:p-10 bg-white border border-gray-100 shadow-sm rounded-[3rem] hover:shadow-md transition-all group">
                    <div class="max-w-xl">
                        <div class="flex items-center gap-3 mb-8 border-b border-gray-50 pb-5">
                            <div class="w-8 h-8 bg-orange-50 text-orange-600 rounded-lg flex items-center justify-center text-xs group-hover:scale-110 transition-transform">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <h4 class="font-black text-gray-800 text-xs uppercase tracking-[0.2em]">Keamanan & Sandi</h4>
                        </div>

                        <div class="emerald-form-wrapper">
                            @include('profile.partials.update-password-form')
                        </div>
                    </div>
                </div>
            </div>

            <!-- Danger Zone: Delete User -->
            <div class="p-8 sm:p-10 bg-red-50/30 border border-red-100 shadow-sm rounded-[3.5rem] overflow-hidden relative group">
                <div class="absolute -right-10 -bottom-10 opacity-5 group-hover:opacity-10 transition-opacity">
                    <i class="fas fa-trash text-9xl text-red-600"></i>
                </div>
                <div class="max-w-xl relative z-10">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-8 h-8 bg-red-100 text-red-600 rounded-lg flex items-center justify-center text-xs">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <h4 class="font-black text-red-600 text-xs uppercase tracking-[0.2em]">Zona Berbahaya</h4>
                    </div>
                    
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Sempurnakan style input di dalam form partials agar sinkron */
        .emerald-form-wrapper input[type="text"],
        .emerald-form-wrapper input[type="email"],
        .emerald-form-wrapper input[type="password"] {
            border-radius: 1rem !important;
            border-color: #f3f4f6 !important;
            background-color: #f9fafb !important;
            font-weight: 700 !important;
            font-size: 0.875rem !important;
            padding-top: 0.75rem !important;
            padding-bottom: 0.75rem !important;
        }
        .emerald-form-wrapper input:focus {
            background-color: #ffffff !important;
            --tw-ring-color: #ecfdf5 !important;
            border-color: #10b981 !important;
        }
    </style>
</x-app-layout>