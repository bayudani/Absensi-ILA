<ul class="space-y-2 font-medium">

    <!-- DASHBOARD UTAMA (Semua Role Punya) -->
    <li>
        <a href="{{ route('dashboard') }}"
            class="flex items-center p-3 text-sm transition-all duration-200 group rounded-xl
           {{ request()->routeIs('dashboard')
               ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-200'
               : 'text-gray-600 hover:bg-emerald-50 hover:text-emerald-600' }}">
            <i
                class="fas fa-home w-5 h-5 transition duration-75 {{ request()->routeIs('dashboard') ? 'text-white' : 'text-gray-400 group-hover:text-emerald-600' }}"></i>
            <span class="ms-3 font-bold">Dashboard</span>
        </a>
    </li>

    <!-- MENU UTAMA (ADMIN & KEPSEK) -->
    {{-- LOGIC: Di sini kuncinya, kita izinkan Admin DAN Kepsek melihat menu ini --}}
    @if (in_array(Auth::user()->role, ['admin', 'kepsek']))
        <div class="pt-6 pb-2 px-3">
            <span class="text-[10px] font-bold tracking-[0.2em] text-gray-300 uppercase">Master Data</span>
        </div>
        <li>
            <a href="{{ route('admin.kelas.index') }}"
                class="flex items-center p-3 text-sm text-gray-600 transition-all duration-200 hover:bg-emerald-50 hover:text-emerald-600 group rounded-xl {{ request()->routeIs('admin.kelas.*') ? 'bg-emerald-600 text-white shadow-lg' : '' }}">
                <i
                    class="fas fa-school w-5 h-5 {{ request()->routeIs('admin.kelas.*') ? 'text-white' : 'text-gray-400 group-hover:text-emerald-600' }}"></i>
                <span class="ms-3 font-semibold">Data Kelas</span>
            </a>
        </li>
        <li>
            <a href="{{ route('admin.siswa.index') }}"
                class="flex items-center p-3 text-sm text-gray-600 transition-all duration-200 hover:bg-emerald-50 hover:text-emerald-600 group rounded-xl {{ request()->routeIs('admin.siswa.*') ? 'bg-emerald-600 text-white shadow-lg' : '' }}">
                <i
                    class="fas fa-users w-5 h-5 {{ request()->routeIs('admin.siswa.*') ? 'text-white' : 'text-gray-400 group-hover:text-emerald-600' }}"></i>
                <span class="ms-3 font-semibold">Data Siswa</span>
            </a>
        </li>
        <li>
            <a href="{{ route('admin.guru.index') }}"
                class="flex items-center p-3 text-sm text-gray-600 transition-all duration-200 hover:bg-emerald-50 hover:text-emerald-600 group rounded-xl {{ request()->routeIs('admin.guru.*') ? 'bg-emerald-600 text-white shadow-lg' : '' }}">
                <i
                    class="fas fa-chalkboard-teacher w-5 h-5 {{ request()->routeIs('admin.guru.*') ? 'text-white' : 'text-gray-400 group-hover:text-emerald-600' }}"></i>
                <span class="ms-3 font-semibold">Data Guru</span>
            </a>
        </li>
        <li>
            <a href="{{ route('admin.mapel.index') }}"
                class="flex items-center p-3 text-sm text-gray-600 transition-all duration-200 hover:bg-emerald-50 hover:text-emerald-600 group rounded-xl {{ request()->routeIs('admin.mapel.*') ? 'bg-emerald-600 text-white shadow-lg' : '' }}">
                <i
                    class="fas fa-book w-5 h-5 {{ request()->routeIs('admin.mapel.*') ? 'text-white' : 'text-gray-400 group-hover:text-emerald-600' }}"></i>
                <span class="ms-3 font-semibold">Mata Pelajaran</span>
            </a>
        </li>
        <li>
            <a href="{{ route('admin.jadwal.index') }}"
                class="flex items-center p-3 text-sm text-gray-600 transition-all duration-200 hover:bg-emerald-50 hover:text-emerald-600 group rounded-xl {{ request()->routeIs('admin.jadwal.*') ? 'bg-emerald-600 text-white shadow-lg' : '' }}">
                <i
                    class="fas fa-calendar-alt w-5 h-5 {{ request()->routeIs('admin.jadwal.*') ? 'text-white' : 'text-gray-400 group-hover:text-emerald-600' }}"></i>
                <span class="ms-3 font-semibold">Kelola Jadwal</span>
            </a>
        </li>
        {{-- <li>
            <a href="{{ route('admin.user.index') }}"
                class="flex items-center p-3 text-sm text-gray-600 transition-all duration-200 hover:bg-emerald-50 hover:text-emerald-600 group rounded-xl {{ request()->routeIs('admin.user.*') ? 'bg-emerald-600 text-white shadow-lg' : '' }}">
                <i
                    class="fas fa-user-cog w-5 h-5 {{ request()->routeIs('admin.user.*') ? 'text-white' : 'text-gray-400 group-hover:text-emerald-600' }}"></i>
                <span class="ms-3 font-semibold">User Management</span>
            </a>
        </li> --}}
        <li>
            <a href="{{ route('admin.rekap') }}"
                class="flex items-center p-3 text-sm text-gray-600 transition-all duration-200 hover:bg-emerald-50 hover:text-emerald-600 group rounded-xl {{ request()->routeIs('admin.rekap*') ? 'bg-emerald-600 text-white shadow-lg' : '' }}">
                <i
                    class="fas fa-clipboard-list w-5 h-5 {{ request()->routeIs('admin.rekap*') ? 'text-white' : 'text-gray-400 group-hover:text-emerald-600' }}"></i>
                <span class="ms-3 font-semibold">Rekap Absensi Total</span>
            </a>
        </li>
    @endif

    <!-- MENU GURU (Muncul buat Guru DAN Walas) -->
    @if (in_array(Auth::user()->role, ['guru', 'walas']))
        <div class="pt-6 pb-2 px-3">
            <span class="text-[10px] font-bold tracking-[0.2em] text-gray-300 uppercase">Kegiatan Belajar</span>
        </div>
        <li>
            <a href="{{ route('guru.absensi.create') }}"
                class="flex items-center p-3 text-sm transition-all duration-200 group rounded-xl
               {{ request()->routeIs('guru.absensi.*')
                   ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-200'
                   : 'text-gray-600 hover:bg-emerald-50 hover:text-emerald-600' }}">
                <i
                    class="fas fa-check-circle w-5 h-5 transition duration-75 {{ request()->routeIs('guru.absensi.*') ? 'text-white' : 'text-gray-400 group-hover:text-emerald-600' }}"></i>
                <span class="ms-3 font-semibold">Input Absensi</span>
            </a>
        </li>
        <li>
            <a href="{{ route('guru.jadwal') }}"
                class="flex items-center p-3 text-sm transition-all duration-200 group rounded-xl
               {{ request()->routeIs('guru.jadwal*')
                   ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-200'
                   : 'text-gray-600 hover:bg-emerald-50 hover:text-emerald-600' }}">
                <i
                    class="fas fa-calendar w-5 h-5 transition duration-75 {{ request()->routeIs('guru.jadwal*') ? 'text-white' : 'text-gray-400 group-hover:text-emerald-600' }}"></i>
                <span class="ms-3 font-semibold">Jadwal Mengajar</span>
            </a>
        </li>
        <li>
            <a href="{{ route('guru.rekap') }}"
                class="flex items-center p-3 text-sm transition-all duration-200 group rounded-xl
               {{ request()->routeIs('guru.rekap*')
                   ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-200'
                   : 'text-gray-600 hover:bg-emerald-50 hover:text-emerald-600' }}">
                <i
                    class="fas fa-chart-bar w-5 h-5 transition duration-75 {{ request()->routeIs('guru.rekap*') ? 'text-white' : 'text-gray-400 group-hover:text-emerald-600' }}"></i>
                <span class="ms-3 font-semibold">Rekap Kelas</span>
            </a>
        </li>
    @endif

    <!-- MENU KHUSUS WALI KELAS (Hanya Walas) -->
    @if (Auth::user()->role === 'walas')
        <div class="pt-6 pb-2 px-3">
            <span class="text-[10px] font-bold tracking-[0.2em] text-gray-300 uppercase">Wali Kelas</span>
        </div>
        <li>
            <a href="{{ route('walas.siswa') }}"
                class="flex items-center p-3 text-sm transition-all duration-200 group rounded-xl
               {{ request()->routeIs('walas.siswa*')
                   ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-200'
                   : 'text-gray-600 hover:bg-emerald-50 hover:text-emerald-600' }}">
                <i
                    class="fas fa-users w-5 h-5 transition duration-75 {{ request()->routeIs('walas.siswa*') ? 'text-white' : 'text-gray-400 group-hover:text-emerald-600' }}"></i>
                <span class="ms-3 font-semibold">Data Siswa Kelas</span>
            </a>
        </li>
        <li>
            <a href="{{ route('walas.laporan') }}"
                class="flex items-center p-3 text-sm transition-all duration-200 group rounded-xl
               {{ request()->routeIs('walas.laporan*')
                   ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-200'
                   : 'text-gray-600 hover:bg-emerald-50 hover:text-emerald-600' }}">
                <i
                    class="fas fa-file-alt w-5 h-5 transition duration-75 {{ request()->routeIs('walas.laporan*') ? 'text-white' : 'text-gray-400 group-hover:text-emerald-600' }}"></i>
                <span class="ms-3 font-semibold">Laporan Absensi</span>
            </a>
        </li>
    @endif

    <!-- MENU KHUSUS KEPSEK (Dashboard/Monitoring Tambahan) -->
    @if (Auth::user()->role === 'kepsek')
        <div class="pt-6 pb-2 px-3">
            <span class="text-[10px] font-bold tracking-[0.2em] text-gray-300 uppercase">Pimpinan</span>
        </div>
        <li>
            <a href="{{ route('kepsek.monitoring') }}"
                class="flex items-center p-3 text-sm transition-all duration-200 group rounded-xl
               {{ request()->routeIs('kepsek.monitoring*')
                   ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-200'
                   : 'text-gray-600 hover:bg-emerald-50 hover:text-emerald-600' }}">
                <i
                    class="fas fa-chart-line w-5 h-5 transition duration-75 {{ request()->routeIs('kepsek.monitoring*') ? 'text-white' : 'text-gray-400 group-hover:text-emerald-600' }}"></i>
                <span class="ms-3 font-semibold">Monitoring Sekolah</span>
            </a>
        </li>
    @endif
</ul>
