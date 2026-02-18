<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'E-absensi') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        
        <!-- FontAwesome (Wajib buat icon sidebar) -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <!-- Livewire Styles -->
        @livewireStyles
    </head>
    <body class="font-sans antialiased bg-gray-100">
        <div class="min-h-screen flex">
            
            <!-- SIDEBAR (Kiri) -->
            <!-- Fixed position biar sidebar tetep diem pas discroll -->
            <aside class="w-64 bg-white border-r border-gray-200 hidden md:block fixed h-full overflow-y-auto z-10">
                <div class="p-4 border-b border-gray-200 flex items-center h-16 px-6">
                    <h2 class="font-bold text-xl tracking-tighter text-gray-800">
                        <i class="fas fa-check-double mr-2 text-indigo-600"></i>E-Absensi
                    </h2>
                </div>
                
                <!-- Panggil Component Sidebar Menu yang udah kita buat -->
                <nav class="p-4">
                    <x-sidebar-menu />
                </nav>
            </aside>

            <!-- KONTEN UTAMA (Kanan) -->
            <!-- Kasih margin-left 64 (sesuai lebar sidebar) biar gak ketutupan -->
            <div class="flex-1 flex flex-col md:ml-64 transition-all duration-300">
                
                <!-- Top Navbar (Bawaan Breeze - Berisi Profil & Logout) -->
                @include('layouts.navigation')

                <!-- Page Heading (Header Dashboard) -->
                @if (isset($header))
                    <header class="bg-white shadow">
                        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endif

                <!-- Page Content (Isi Halaman) -->
                <main class="p-6">
                    {{ $slot }}
                </main>
            </div>
        </div>

        <!-- Livewire Scripts -->
        @livewireScripts
    </body>
</html>