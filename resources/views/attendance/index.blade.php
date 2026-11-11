<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-emerald-700">Kehadiran</p>
            <h2 class="mt-1 text-2xl font-bold text-slate-900">Absensi Magang</h2>
            <p class="mt-1 text-sm text-slate-500">Catat waktu masuk dan pulang kegiatan PKL.</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="rounded-lg border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800">{{ session('error') }}</div>
            @endif

            @if(Auth::user()->role === 'siswa')
                <section class="rounded-xl border border-emerald-200 bg-gradient-to-br from-white to-emerald-50 p-5 shadow-sm sm:p-6">
                    <h3 class="font-semibold text-slate-900">Absensi hari ini · {{ now()->translatedFormat('l, d F Y') }}</h3>
                    @if(!$todayAttendance)
                        <form action="{{ route('attendance.check-in') }}" method="POST" enctype="multipart/form-data" class="mt-5 grid gap-4 sm:grid-cols-2">
                            @csrf
                            <div>
                                <label for="mode" class="mb-1 block text-sm font-medium text-slate-700">Mode kerja</label>
                                <select id="mode" name="mode" required class="w-full rounded-lg border-slate-300">
                                    <option value="WFO">WFO · Di lokasi</option>
                                    <option value="WFH">WFH · Dari rumah</option>
                                </select>
                            </div>
                            <div>
                                <label for="photo" class="mb-1 block text-sm font-medium text-slate-700">Foto bukti masuk</label>
                                <input id="photo" type="file" name="photo" accept="image/*" capture="user" required class="w-full rounded-lg border border-slate-300 bg-white p-2 text-sm">
                                @error('photo')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                            </div>
                            <div class="sm:col-span-2">
                                <button class="rounded-lg bg-emerald-700 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-800">Catat Absen Masuk</button>
                            </div>
                        </form>
                    @elseif(!$todayAttendance->check_out_at)
                        <div class="mt-4 flex flex-wrap items-center justify-between gap-4 rounded-lg border border-emerald-100 bg-white p-4">
                            <p class="text-sm text-slate-700">Masuk {{ $todayAttendance->check_in_at?->format('H:i') }} · {{ $todayAttendance->mode }}</p>
                            <form action="{{ route('attendance.check-out') }}" method="POST">
                                @csrf
                                <button class="rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-700">Catat Absen Pulang</button>
                            </form>
                        </div>
                    @else
                        <p class="mt-4 rounded-lg bg-white p-4 text-sm text-slate-700">Absensi lengkap: masuk {{ $todayAttendance->check_in_at?->format('H:i') }}, pulang {{ $todayAttendance->check_out_at?->format('H:i') }} ({{ $todayAttendance->mode }}).</p>
                    @endif
                </section>
            @endif

            <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-5 py-4">
                    <h3 class="font-semibold text-slate-900">Riwayat kehadiran</h3>
                    <p class="mt-1 text-sm text-slate-500">Guru hanya melihat absensi siswa bimbingannya.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500"><tr>
                            @if(Auth::user()->role !== 'siswa')<th class="px-5 py-3">Siswa</th>@endif
                            <th class="px-5 py-3">Tanggal</th><th class="px-5 py-3">Mode</th><th class="px-5 py-3">Masuk</th><th class="px-5 py-3">Pulang</th>
                        </tr></thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($attendances as $attendance)
                                <tr>
                                    @if(Auth::user()->role !== 'siswa')<td class="px-5 py-3 font-medium text-slate-800">{{ $attendance->user->name }}</td>@endif
                                    <td class="px-5 py-3 text-slate-600">{{ $attendance->tanggal->format('d/m/Y') }}</td>
                                    <td class="px-5 py-3"><span class="rounded bg-sky-50 px-2 py-1 text-xs font-semibold text-sky-800">{{ $attendance->mode }}</span></td>
                                    <td class="px-5 py-3 text-slate-600">
                                        @if($attendance->check_in_at)
                                            {{ $attendance->check_in_at->format('H:i') }}
                                            @if(Auth::user()->role !== 'siswa')
                                                <a class="ml-2 text-xs font-semibold text-sky-800 hover:underline" href="{{ route('attendance.photo', $attendance) }}" target="_blank" rel="noopener">Lihat bukti</a>
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-5 py-3 text-slate-600">{{ $attendance->check_out_at?->format('H:i') ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-5 py-8 text-center text-slate-500">Belum ada data absensi.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>