<nav x-data="{ open: false }" class="bg-white border-b border-gray-100 h-16 flex items-center shadow-sm sticky top-0 z-30">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
        <div class="flex justify-between items-center h-16">
            
            <!-- Sisi Kiri: Breadcrumb Sederhana atau Info -->
            <div class="flex items-center">
                <div class="hidden sm:flex flex-col">
                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] leading-none">E-Absensi SMPN 3</span>
                    <span class="text-xs font-bold text-emerald-600 mt-1 leading-none uppercase">{{ Auth::user()->role }} PANEL</span>
                </div>
            </div>

            <!-- Sisi Kanan: Action Buttons (Profile & Logout) -->
            <div class="flex items-center gap-3">
                
                <!-- Tombol Profile (Muncul di semua halaman) -->
                <a href="{{ route('profile.edit') }}" 
                   class="flex items-center gap-2 px-4 py-2 bg-white border border-gray-100 rounded-xl text-sm font-bold text-gray-600 hover:text-emerald-600 hover:border-emerald-200 transition-all shadow-sm group">
                    <i class="fas fa-user-circle text-lg text-emerald-500 group-hover:scale-110 transition-transform"></i>
                    <span class="hidden md:inline">{{ Auth::user()->name }}</span>
                </a>

                <!-- Tombol Logout (Muncul di semua halaman) -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" 
                            class="flex items-center gap-2 px-4 py-2 bg-red-50 border border-red-50 rounded-xl text-sm font-bold text-red-600 hover:bg-red-600 hover:text-white transition-all shadow-sm group">
                        <i class="fas fa-sign-out-alt text-red-400 group-hover:text-white transition-colors"></i>
                        <span class="hidden md:inline font-black uppercase text-[11px] tracking-wider">Keluar</span>
                    </button>
                </form>

                <!-- Hamburger Mobile (Optional) -->
                {{-- <div class="-me-2 flex items-center sm:hidden">
                    <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                        <i class="fas fa-bars"></i>
                    </button>
                </div> --}}
            </div>
        </div>
    </div>
</nav>