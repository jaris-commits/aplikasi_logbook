<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Tambah Akun Pengguna
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="bg-white p-6 rounded-xl shadow-sm border">

                <h3 class="text-lg font-bold text-gray-800 mb-6">
                    Form Tambah Akun
                </h3>

                <form action="{{ route('admin.users.store') }}"
                      method="POST"
                      class="space-y-5">

                    @csrf

                    {{-- NAMA --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Nama Lengkap
                        </label>

                        <input type="text"
                               name="name"
                               value="{{ old('name') }}"
                               required
                               class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">

                        @error('name')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- EMAIL --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Email
                        </label>

                        <input type="email"
                               name="email"
                               value="{{ old('email') }}"
                               required
                               class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">

                        @error('email')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- PASSWORD --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Password
                        </label>

                        <input type="password"
                               name="password"
                               required
                               minlength="8"
                               class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">

                        <p class="text-xs text-gray-500 mt-1">
                            Minimal 8 karakter.
                        </p>

                        @error('password')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- ROLE --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Role Pengguna
                        </label>

                        <select name="role"
                                id="role"
                                required
                                class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">

                            <option value="">Pilih Role</option>
                            <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>
                                Admin
                            </option>
                            <option value="guru" {{ old('role') === 'guru' ? 'selected' : '' }}>
                                Guru
                            </option>
                            <option value="siswa" {{ old('role') === 'siswa' ? 'selected' : '' }}>
                                Siswa
                            </option>

                        </select>

                        @error('role')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- GURU PEMBIMBING --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Guru Pembimbing
                        </label>

                        <select name="guru_id"
                                class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">

                            <option value="">Pilih Guru Pembimbing</option>

                            @foreach ($gurus as $guru)
                                <option value="{{ $guru->id }}"
                                    {{ old('guru_id') == $guru->id ? 'selected' : '' }}>
                                    {{ $guru->name }}
                                </option>
                            @endforeach

                        </select>

                        <p class="text-xs text-gray-500 mt-1">
                            Wajib dipilih jika role pengguna adalah Siswa.
                        </p>

                        @error('guru_id')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- TOMBOL --}}
                    <div class="flex flex-col sm:flex-row gap-3 pt-4">

                        <button type="submit"
                                class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-lg font-semibold">
                            Simpan Akun
                        </button>

                        <a href="{{ route('admin.users.index') }}"
                           class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-5 py-2.5 rounded-lg text-center font-semibold">
                            Kembali
                        </a>

                    </div>

                </form>

            </div>

        </div>
    </div>
</x-app-layout>
