<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'E-absensi') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

        @vite(['resources/css/app.css'])
        
        @livewireStyles
    </head>
    <body class="font-sans antialiased bg-gray-100">
        <div class="min-h-screen flex" x-data="{ sidebarOpen: false }">
            
            <div x-show="sidebarOpen" 
                 @click="sidebarOpen = false" 
                 x-transition.opacity.duration.300ms
                 style="display: none;"
                 class="fixed inset-0 z-40 bg-gray-900/50 backdrop-blur-sm md:hidden">
            </div>

            <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" 
                   class="w-64 bg-white border-r border-gray-200 fixed inset-y-0 left-0 h-full overflow-y-auto z-50 transition-transform duration-300 ease-in-out md:translate-x-0">
                
                <div class="p-4 border-b border-gray-200 flex items-center justify-between h-16 px-6">
                    <h2 class="font-bold text-xl tracking-tighter text-gray-800">
                        <i class="fas fa-check-double mr-2 text-indigo-600"></i>E-Absensi
                    </h2>
                    <button @click="sidebarOpen = false" class="md:hidden text-gray-400 hover:text-red-500 focus:outline-none">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <nav class="p-4">
                    <x-sidebar-menu />
                </nav>
            </aside>

            <div class="flex-1 flex flex-col md:ml-64 min-w-0 w-full">
                
                <div class="md:hidden bg-white border-b border-gray-100 px-4 py-3 flex items-center gap-4 sticky top-0 z-30 shadow-sm">
                    <button @click="sidebarOpen = true" class="text-gray-600 hover:text-emerald-600 focus:outline-none p-1">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    <h1 class="font-bold text-lg text-gray-800 tracking-tighter">E-Absensi</h1>
                </div>

                @include('layouts.navigation')

                @if (isset($header))
                    <header class="bg-white shadow relative z-10">
                        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endif

                <main class="p-6 flex-1">
                    {{ $slot }}
                </main>
            </div>
        </div>

        @livewireScripts
    </body>
</html>