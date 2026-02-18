<x-guest-layout>
    <div class="min-h-screen flex flex-col items-center justify-center p-4">
        <!-- Card Container -->
        <div class="w-full max-w-md bg-white p-10 rounded-[3rem] border border-emerald-100 shadow-2xl shadow-emerald-100/50 relative overflow-hidden">
            
            <!-- Aksen Dekoratif -->
            <div class="absolute -top-10 -right-10 w-32 h-32 bg-emerald-50 rounded-full opacity-50"></div>
            <div class="absolute -bottom-10 -left-10 w-24 h-24 bg-emerald-50 rounded-full opacity-50"></div>

            <!-- Logo & Title -->
            <div class="text-center mb-10 relative z-10">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-emerald-600 rounded-2xl shadow-lg shadow-emerald-200 mb-4 transform -rotate-6">
                    <i class="fas fa-check-double text-white text-2xl"></i>
                </div>
                <h1 class="text-3xl font-black text-gray-800 tracking-tighter uppercase leading-none">E-Absensi</h1>
                <p class="text-[10px] font-bold text-emerald-600 uppercase tracking-[0.3em] mt-2">SMPN 3 Siak Kecil</p>
            </div>

            <!-- Session Status -->
            <x-auth-session-status class="mb-6 text-center font-bold text-sm" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}" class="space-y-6 relative z-10">
                @csrf

                <!-- Input Username / NIP -->
                <div>
                    <label for="username" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 pl-1">NIP / Username</label>
                    <div class="relative group">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-5 text-gray-300 group-focus-within:text-emerald-500 transition-colors">
                            <i class="fas fa-user-circle"></i>
                        </span>
                        <input id="username" name="username" type="text" :value="old('username')" required autofocus 
                            class="w-full pl-12 pr-5 py-4 rounded-2xl border-gray-100 bg-gray-50 text-sm font-bold text-gray-700 placeholder-gray-300 focus:bg-white focus:ring-4 focus:ring-emerald-50 focus:border-emerald-500 transition-all outline-none"
                            placeholder="Masukkan NIP Anda">
                    </div>
                    <x-input-error :messages="$errors->get('username')" class="mt-2 text-[10px] font-bold italic" />
                </div>

                <!-- Input Password -->
                <div>
                    <div class="flex justify-between items-center mb-2 px-1">
                        <label for="password" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest">Kata Sandi</label>
                        @if (Route::has('password.request'))
                            <a class="text-[10px] font-black text-emerald-600 uppercase tracking-widest hover:underline" href="{{ route('password.request') }}">
                                Lupa?
                            </a>
                        @endif
                    </div>
                    <div class="relative group">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-5 text-gray-300 group-focus-within:text-emerald-500 transition-colors">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input id="password" name="password" type="password" required autocomplete="current-password"
                            class="w-full pl-12 pr-5 py-4 rounded-2xl border-gray-100 bg-gray-50 text-sm font-bold text-gray-700 placeholder-gray-300 focus:bg-white focus:ring-4 focus:ring-emerald-50 focus:border-emerald-500 transition-all outline-none"
                            placeholder="••••••••">
                    </div>
                    <x-input-error :messages="$errors->get('password')" class="mt-2 text-[10px] font-bold italic" />
                </div>

                <!-- Remember Me -->
                <div class="flex items-center px-1">
                    <label for="remember_me" class="inline-flex items-center cursor-pointer group">
                        <input id="remember_me" type="checkbox" name="remember" 
                            class="rounded-lg border-gray-200 text-emerald-600 shadow-sm focus:ring-emerald-500 w-5 h-5 transition-all">
                        <span class="ms-3 text-xs font-bold text-gray-500 group-hover:text-emerald-600 transition-colors uppercase tracking-tighter">{{ __('Ingat saya di perangkat ini') }}</span>
                    </label>
                </div>

                <!-- Login Button -->
                <div class="pt-4">
                    <button type="submit" class="w-full bg-emerald-600 text-white font-black py-5 rounded-3xl text-xs uppercase tracking-[0.2em] shadow-xl shadow-emerald-100 hover:bg-emerald-700 hover:shadow-emerald-200 transition-all active:scale-95 flex items-center justify-center gap-3">
                        MASUK KE SISTEM <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </form>

            <!-- Footer -->
            <div class="mt-10 text-center relative z-10 border-t border-gray-50 pt-6">
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                    &copy; {{ date('Y') }} Tim IT SMPN 3 Siak Kecil
                </p>
            </div>
        </div>
        
        <!-- Support Info -->
        <div class="mt-8 text-center">
            <p class="text-xs font-bold text-gray-400">Kesulitan masuk? Hubungi <a href="#" class="text-emerald-600 hover:underline">Admin TU</a></p>
        </div>
    </div>
</x-guest-layout>