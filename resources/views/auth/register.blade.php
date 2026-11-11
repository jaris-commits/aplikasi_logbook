<x-guest-layout>
    <div class="mb-6">
        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-700">Akun sekolah</p>
        <h1 class="mt-2 text-2xl font-bold text-slate-900">Buat akun</h1>
        <p class="mt-1 text-sm leading-6 text-slate-500">Daftarkan akun untuk menggunakan Logbook Magang.</p>
    </div>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div>
            <x-input-label for="name" value="Nama lengkap" class="font-medium text-slate-700" />
            <x-text-input id="name" class="mt-1.5 block w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-600 focus:ring-blue-600" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" placeholder="Nama sesuai identitas" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="email" value="Email" class="font-medium text-slate-700" />
            <x-text-input id="email" class="mt-1.5 block w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-600 focus:ring-blue-600" type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="nama@sekolah.sch.id" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" value="Password" class="font-medium text-slate-700" />

            <x-text-input id="password" class="mt-1.5 block w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-600 focus:ring-blue-600"
                            type="password"
                            name="password"
                            required autocomplete="new-password" placeholder="Buat password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password_confirmation" value="Konfirmasi password" class="font-medium text-slate-700" />

            <x-text-input id="password_confirmation" class="mt-1.5 block w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-600 focus:ring-blue-600"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="mt-6">
            <x-primary-button class="w-full justify-center rounded-lg bg-blue-700 py-3 text-sm font-semibold tracking-normal hover:bg-blue-800 focus:bg-blue-800 active:bg-blue-900">
                Daftar
            </x-primary-button>
        </div>
    </form>

    <div class="mt-6 border-t border-slate-100 pt-5 text-center text-sm text-slate-600">
        Sudah memiliki akun?
        <a href="{{ route('login') }}" class="font-semibold text-blue-700 hover:text-blue-900">Masuk</a>
    </div>
</x-guest-layout>
