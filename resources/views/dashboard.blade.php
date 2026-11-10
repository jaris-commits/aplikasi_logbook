<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Dashboard Logbook Magang — Anda Login Sebagai: <span class="uppercase font-bold text-indigo-600">{{ Auth::user()->role }}</span>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl x-auto sm:px-6 lg:px-8 space-y-6">

            <!-- NOTIFIKASI SUKSES / EROR -->
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                    {{ session('error') }}
                </div>
            @endif

            <!-- 1. FORM TAMBAH LOGBOOK (KHUSUS ROLE SISWA) -->
            @if(Auth::user()->role == 'siswa')
                <div class="p-6 bg-white border border-gray-200 rounded-lg shadow-sm">
                    <h3 class="text-lg font-bold mb-4 text-gray-700">Tambah Catatan Logbook Baru</h3>
                    <form action="{{ route('logbook.store') }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Tanggal Kegiatan</label>
                            <input type="date" name="tanggal" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Aktivitas / Kegiatan Magang</label>
                            <textarea name="aktivitas" rows="3" required placeholder="Tuliskan apa saja yang Anda kerjakan hari ini..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                        </div>
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded shadow">
                            Simpan Logbook
                        </button>
                    </form>
                </div>
            @endif

            <!-- 2. DAFTAR TABEL LOGBOOK (TAMPIL UNTUK SEMUA ROLE) -->
            <div class="p-6 bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
                <h3 class="text-lg font-bold mb-4 text-gray-700">Daftar Riwayat Catatan Logbook</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase">Nama Siswa</th>
                                <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase">Tanggal</th>
                                <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase">Aktivitas</th>
                                <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase">Catatan Guru</th>
                                <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($logbooks as $log)
                                <tr>
                                    <!-- Jika role siswa, tampilkan nama sendiri, jika admin/guru tampilkan nama pembuat -->
                                    <td class="px-6 py-4 whitespace-nowrap font-semibold">{{ $log->user->name ?? Auth::user()->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $log->tanggal }}</td>
                                    <td class="px-6 py-4">{{ $log->aktivitas }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            {{ $log->status == 'disetujui' ? 'bg-green-100 text-green-800' : ($log->status == 'revisi' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                            {{ $log->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-gray-500 italic">{{ $log->catatan_guru ?? '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap space-y-2">
                                        
                                        <!-- Aksi Hapus (Hanya muncul untuk Siswa jika status pending) -->
                                        @if(Auth::user()->role == 'siswa' && $log->status == 'pending')
                                            <form action="{{ route('logbook.destroy', $log->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus catatan ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900 font-medium">Hapus</button>
                                            </form>
                                        @endif

                                        <!-- Form Penilaian (Hanya muncul untuk Guru & Admin) -->
                                        @if(Auth::user()->role == 'guru' || Auth::user()->role == 'admin')
                                            <form action="{{ route('logbook.status', $log->id) }}" method="POST" class="flex flex-col space-y-1 border-t pt-2 mt-2">
                                                @csrf
                                                @method('PUT')
                                                <input type="text" name="catatan_guru" placeholder="Beri masukan..." value="{{ $log->catatan_guru }}" class="text-xs p-1 border rounded">
                                                <div class="flex space-x-1">
                                                    <button type="submit" name="status" value="disetujui" class="bg-green-500 text-white text-xs px-2 py-1 rounded hover:bg-green-600">Setujui</button>
                                                    <button type="submit" name="status" value="revisi" class="bg-red-500 text-white text-xs px-2 py-1 rounded hover:bg-red-600">Revisi</button>
                                                </div>
                                            </form>
                                        @endif

                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">Belum ada catatan logbook yang tersedia.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>