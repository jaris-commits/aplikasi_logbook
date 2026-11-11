<x-guest-layout>
    <div class="mb-6">
        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-700">Akun sekolah</p>
        <h1 class="mt-2 text-2xl font-bold text-slate-900">Masuk ke akun Anda</h1>
        <p class="mt-1 text-sm leading-6 text-slate-500">Gunakan akun yang terdaftar untuk membuka dashboard logbook.</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div>
            <x-input-label for="email" value="Email" class="font-medium text-slate-700" />
            <x-text-input id="email" class="mt-1.5 block w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-600 focus:ring-blue-600" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="nama@sekolah.sch.id" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-5">
            <x-input-label for="password" value="Password" class="font-medium text-slate-700" />

            <x-text-input id="password" class="mt-1.5 block w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-600 focus:ring-blue-600"
                            type="password"
                            name="password"
                            required autocomplete="current-password" placeholder="Masukkan password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-5 flex items-center justify-between gap-3">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-slate-300 text-blue-700 shadow-sm focus:ring-blue-600" name="remember">
                <span class="ms-2 text-sm text-slate-600">Ingat saya</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm font-medium text-blue-700 hover:text-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2" href="{{ route('password.request') }}">
                    Lupa password?
                </a>
            @endif
        </div>

        <div class="mt-7">
            <x-primary-button class="w-full justify-center rounded-lg bg-blue-700 py-3 text-sm font-semibold tracking-normal hover:bg-blue-800 focus:bg-blue-800 active:bg-blue-900">
                Masuk
            </x-primary-button>
        </div>
    </form>

    @if (Route::has('register'))
        <div class="mt-6 border-t border-slate-100 pt-5 text-center text-sm text-slate-600">
            Belum memiliki akun?
            <a href="{{ route('register') }}" class="font-semibold text-blue-700 hover:text-blue-900">Daftar</a>
        </div>
    @endif
</x-guest-layout>
