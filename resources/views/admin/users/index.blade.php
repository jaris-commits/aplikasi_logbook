<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium uppercase tracking-[0.18em] text-indigo-600">Admin Panel</p>
                <h2 class="mt-1 text-2xl font-bold text-gray-900">Manajemen Akun</h2>
            </div>
            <a href="{{ route('admin.users.create') }}"
               class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700">
                + Tambah Akun
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700 shadow-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700 shadow-sm">
                    {{ session('error') }}
                </div>
            @endif

            <div class="grid gap-4 sm:grid-cols-3">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-sm text-gray-500">Total Admin</p>
                    <h3 class="mt-3 text-3xl font-bold text-indigo-600">{{ $totalAdmin }}</h3>
                </div>

                <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-sm text-gray-500">Total Guru</p>
                    <h3 class="mt-3 text-3xl font-bold text-blue-600">{{ $totalGuru }}</h3>
                </div>

                <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-sm text-gray-500">Total Siswa</p>
                    <h3 class="mt-3 text-3xl font-bold text-green-600">{{ $totalSiswa }}</h3>
                </div>
            </div>

            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-200 px-5 py-4">
                    <h3 class="text-lg font-bold text-gray-800">Daftar Pengguna</h3>
                    <p class="mt-1 text-sm text-gray-500">Kelola akun Admin, Guru, dan Siswa.</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-gray-600 uppercase">
                            <tr>
                                <th class="px-6 py-4 text-left">No</th>
                                <th class="px-6 py-4 text-left">Nama</th>
                                <th class="px-6 py-4 text-left">Email</th>
                                <th class="px-6 py-4 text-left">Role</th>
                                <th class="px-6 py-4 text-left">Guru Pembimbing</th>
                                <th class="px-6 py-4 text-center">Aksi</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-200">
                            @forelse ($users as $user)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4">{{ $loop->iteration }}</td>
                                    <td class="px-6 py-4 font-semibold text-gray-800">{{ $user->name }}</td>
                                    <td class="px-6 py-4 text-gray-600">{{ $user->email }}</td>
                                    <td class="px-6 py-4">
                                        @if ($user->role === 'admin')
                                            <span class="rounded-full bg-purple-100 px-3 py-1 text-xs font-semibold text-purple-700">Admin</span>
                                        @elseif ($user->role === 'guru')
                                            <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700">Guru</span>
                                        @else
                                            <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">Siswa</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-gray-600">
                                        @if ($user->role === 'siswa')
                                            {{ $user->guruPembimbing?->name ?? 'Belum ditentukan' }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center justify-center gap-3">
                                            <a href="{{ route('admin.users.edit', $user->id) }}" class="font-semibold text-indigo-600 hover:text-indigo-800">Edit</a>

                                            @if ($user->id !== auth()->id())
                                                <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus akun {{ $user->name }}?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="font-semibold text-red-600 hover:text-red-800">Hapus</button>
                                                </form>
                                            @else
                                                <span class="text-xs text-gray-400">Akun Anda</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-gray-500">Belum ada akun pengguna.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
