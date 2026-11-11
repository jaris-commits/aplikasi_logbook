<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium uppercase tracking-[0.2em] text-indigo-600">Logbook Magang</p>
                <p class="mt-1 text-xs font-medium uppercase tracking-[0.18em] text-slate-500 opacity-70">SMKN 20 Jakarta</p>
                <h2 class="mt-2 text-2xl font-bold text-gray-900">
                    Dashboard
                    <span class="text-indigo-600">{{ ucfirst(Auth::user()->role) }}</span>
                </h2>
            </div>
            <span class="inline-flex items-center rounded-full border border-indigo-200 bg-indigo-50 px-3 py-1 text-sm font-semibold text-indigo-700">
                {{ ucfirst(Auth::user()->role) }} aktif
            </span>
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

            @if (Auth::user()->role === 'guru')
                <section class="rounded-2xl border border-sky-200 bg-sky-50/60 p-5 shadow-sm sm:p-6">
                    <div class="mb-5">
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-sky-800">Bantuan AI</p>
                        <h3 class="mt-1 text-lg font-bold text-slate-900">Ringkasan Jurnal Mingguan</h3>
                        <p class="mt-1 text-sm text-slate-600">Buat 3-5 poin ringkasan dari logbook siswa bimbingan Anda.</p>
                        <p class="mt-3 rounded-lg border border-sky-200 bg-white/70 p-3 text-xs leading-5 text-slate-600">
                            Teks aktivitas minggu terpilih akan diproses oleh Google Gemini. Sistem menyamarkan pola email, nomor HP, dan NIK sebelum pengiriman, tetapi penyamaran otomatis tidak selalu mengenali semua informasi pribadi. Jangan lanjutkan jika jurnal memuat data sensitif yang tidak perlu dibagikan.
                        </p>
                    </div>

                    <form action="{{ route('report.weekly-summary') }}" method="POST" class="grid gap-4 sm:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto] sm:items-end">
                        @csrf
                        <div>
                            <label for="summary_student_id" class="mb-1.5 block text-sm font-medium text-slate-700">Siswa bimbingan</label>
                            <select id="summary_student_id" name="student_id" required class="w-full rounded-lg border-slate-300 shadow-sm focus:border-sky-600 focus:ring-sky-600">
                                <option value="">Pilih siswa</option>
                                @foreach($students as $student)
                                    <option value="{{ $student->id }}" @selected(old('student_id') == $student->id)>{{ $student->name }}</option>
                                @endforeach
                            </select>
                            @error('student_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="summary_week_start" class="mb-1.5 block text-sm font-medium text-slate-700">Tanggal awal minggu</label>
                            <input id="summary_week_start" type="date" name="week_start" value="{{ old('week_start', now()->startOfWeek()->toDateString()) }}" required class="w-full rounded-lg border-slate-300 shadow-sm focus:border-sky-600 focus:ring-sky-600">
                            @error('week_start')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="sm:col-span-3">
                            <label for="privacy_notice_accepted" class="flex items-start gap-2 text-sm leading-5 text-slate-700">
                                <input id="privacy_notice_accepted" type="checkbox" name="privacy_notice_accepted" value="1" required @checked(old('privacy_notice_accepted')) class="mt-0.5 rounded border-slate-300 text-sky-800 shadow-sm focus:ring-sky-700">
                                <span>Saya memahami bahwa teks aktivitas terpilih akan dikirim ke Google Gemini setelah penyamaran otomatis, dan saya telah memastikan isinya layak dibagikan.</span>
                            </label>
                            @error('privacy_notice_accepted')
                                <p class="mt-1 text-sm text-red-600">Persetujuan diperlukan untuk membuat ringkasan AI.</p>
                            @enderror
                        </div>
                        <div class="sm:col-span-3 sm:flex sm:justify-end">
                            <button type="submit" @disabled($students->isEmpty()) class="inline-flex min-h-11 w-full items-center justify-center rounded-lg bg-sky-800 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-sky-900 focus:outline-none focus:ring-2 focus:ring-sky-700 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 sm:w-auto">
                                Buat ringkasan
                            </button>
                        </div>
                    </form>

                    @if($students->isEmpty())
                        <p class="mt-4 text-sm text-slate-600">Belum ada siswa yang ditugaskan kepada Anda.</p>
                    @endif

                    @if(session('weekly_summary'))
                        <div class="mt-6 border-t border-sky-200 pt-5" aria-live="polite">
                            <div class="flex flex-wrap items-baseline justify-between gap-2">
                                <h4 class="font-semibold text-slate-900">{{ session('weekly_summary_student') }}</h4>
                                <span class="text-xs font-medium text-slate-500">{{ session('weekly_summary_period') }}</span>
                            </div>
                            <div class="mt-3 whitespace-pre-line text-sm leading-6 text-slate-700">{{ session('weekly_summary') }}</div>
                            <p class="mt-4 text-xs text-slate-500">Ringkasan AI adalah bahan bantu peninjauan dan perlu diperiksa guru.</p>
                        </div>
                    @endif
                </section>
            @endif

            @php
                $totalLogbook = $logbooks->count();
                $approved = $logbooks->where('status', 'disetujui')->count();
                $revision = $logbooks->where('status', 'revisi')->count();
                $pending = $logbooks->where('status', 'pending')->count();
            @endphp

            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                    <p class="text-sm text-gray-500">Total Logbook</p>
                    <div class="mt-3 flex items-center justify-between">
                        <span class="text-3xl font-bold text-gray-900">{{ $totalLogbook }}</span>
                        <span class="rounded-full bg-indigo-100 px-2 py-1 text-xs font-semibold text-indigo-700">Aktif</span>
                    </div>
                </div>

                <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                    <p class="text-sm text-gray-500">Disetujui</p>
                    <div class="mt-3 flex items-center justify-between">
                        <span class="text-3xl font-bold text-green-600">{{ $approved }}</span>
                        <span class="rounded-full bg-green-100 px-2 py-1 text-xs font-semibold text-green-700">OK</span>
                    </div>
                </div>

                <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                    <p class="text-sm text-gray-500">Revisi</p>
                    <div class="mt-3 flex items-center justify-between">
                        <span class="text-3xl font-bold text-red-500">{{ $revision }}</span>
                        <span class="rounded-full bg-red-100 px-2 py-1 text-xs font-semibold text-red-700">Perbaiki</span>
                    </div>
                </div>

                <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                    <p class="text-sm text-gray-500">Pending</p>
                    <div class="mt-3 flex items-center justify-between">
                        <span class="text-3xl font-bold text-yellow-500">{{ $pending }}</span>
                        <span class="rounded-full bg-yellow-100 px-2 py-1 text-xs font-semibold text-yellow-700">Tunggu</span>
                    </div>
                </div>
            </div>

            @if(Auth::user()->role == 'siswa')
                <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                    <div class="mb-5 flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-100 text-lg">✍️</div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-800">Tambah Catatan Logbook Baru</h3>
                            <p class="text-sm text-gray-500">Catat aktivitas harianmu selama magang.</p>
                        </div>
                    </div>

                    <form action="{{ route('logbook.store') }}" method="POST" enctype="multipart/form-data" class="grid gap-4 md:grid-cols-2">
                        @csrf
                        <div class="md:col-span-1">
                            <label class="mb-2 block text-sm font-medium text-gray-700">Tanggal kegiatan</label>
                            <input type="date" name="tanggal" required class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div class="md:col-span-2">
                            <label class="mb-2 block text-sm font-medium text-gray-700">Aktivitas / kegiatan magang</label>
                            <textarea name="aktivitas" rows="4" required placeholder="Tuliskan apa saja yang Anda kerjakan hari ini..." class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                        </div>

                        <div class="md:col-span-2">
                            <label class="mb-2 block text-sm font-medium text-gray-700">Bukti foto kegiatan</label>
                            <input id="fotoInput" type="file" name="foto" accept="image/*" class="mt-1 block w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm">

                            <div id="fotoPreviewWrapper" class="mt-3 hidden rounded-xl border border-dashed border-gray-300 bg-gray-50 p-3">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-3">
                                        <img id="fotoPreview" src="" alt="Preview foto" class="h-20 w-20 rounded-lg object-cover border border-gray-200 bg-white">
                                        <div>
                                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Preview</p>
                                            <p id="fotoName" class="text-sm font-medium text-gray-700"></p>
                                        </div>
                                    </div>

                                    <button type="button" id="removeFotoBtn" class="rounded-lg border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-100">
                                        Batalkan
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="md:col-span-2 flex flex-wrap items-center gap-3">
                            <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-5 py-2.5 font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                Simpan Logbook
                            </button>

                            <a href="{{ route('report.logbook.pdf') }}" class="inline-flex items-center justify-center rounded-xl border border-indigo-200 bg-indigo-50 px-4 py-2.5 font-semibold text-indigo-700 transition hover:bg-indigo-100">
                                Download PDF Logbook
                            </a>
                        </div>
                    </form>
                </section>
            @endif

            <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="flex flex-col gap-3 border-b border-gray-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-gray-800">Daftar Riwayat Catatan Logbook</h3>
                        <p class="text-sm text-gray-500">Pantau status kegiatan dan feedback dari pembimbing.</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase">Nama</th>
                                <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase">Tanggal</th>
                                <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase">Aktivitas</th>
                                <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase">Catatan</th>
                                <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse($logbooks as $log)
                                <tr class="align-top">
                                    <td class="px-6 py-4 font-semibold text-gray-800">{{ $log->user->name ?? Auth::user()->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-600">{{ $log->tanggal }}</td>
                                    <td class="px-6 py-4 text-gray-700">
                                        <div class="space-y-2">
                                            <p>{{ $log->aktivitas }}</p>
                                            @if($log->foto)
                                                <button type="button" class="open-image-btn cursor-zoom-in" data-image="{{ asset('storage/' . $log->foto) }}" aria-label="Buka foto kegiatan ukuran penuh">
                                                    <img src="{{ asset('storage/' . $log->foto) }}" alt="Foto kegiatan" class="h-20 w-20 rounded-lg border border-gray-200 object-cover transition hover:scale-105">
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold
                                            {{ $log->status == 'disetujui' ? 'bg-green-100 text-green-700' : ($log->status == 'revisi' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') }}">
                                            {{ $log->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-gray-600 italic">{{ $log->catatan_guru ?? '-' }}</td>
                                    <td class="px-6 py-4">
                                        @if(Auth::user()->role == 'siswa' && $log->status == 'pending')
                                            <form action="{{ route('logbook.destroy', $log->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus catatan ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="font-medium text-red-600 transition hover:text-red-800">Hapus</button>
                                            </form>
                                        @endif

                                        @if(Auth::user()->role == 'guru' || Auth::user()->role == 'admin')
                                            <form action="{{ route('logbook.status', $log->id) }}" method="POST" class="mt-2 space-y-2 rounded-xl border border-gray-200 bg-gray-50 p-2">
                                                @csrf
                                                @method('PUT')
                                                <input type="text" name="catatan_guru" placeholder="Beri masukan..." value="{{ $log->catatan_guru }}" class="w-full rounded-lg border border-gray-300 bg-white px-2 py-1.5 text-xs">
                                                <div class="flex gap-2">
                                                    <button type="submit" name="status" value="disetujui" class="rounded-lg bg-green-500 px-2 py-1 text-xs font-semibold text-white hover:bg-green-600">Setujui</button>
                                                    <button type="submit" name="status" value="revisi" class="rounded-lg bg-red-500 px-2 py-1 text-xs font-semibold text-white hover:bg-red-600">Revisi</button>
                                                </div>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-10 text-center text-gray-500">Belum ada catatan logbook yang tersedia.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <div>
                        <h3 class="text-lg font-bold text-gray-800">Rekap Nilai Magang</h3>
                        <p class="text-sm text-gray-500">
                            {{ Auth::user()->role === 'guru' ? 'Nilai siswa bimbingan Anda.' : 'Nilai akhir dan catatan evaluasi siswa.' }}
                        </p>
                    </div>
                    @if(Auth::user()->role == 'admin' || Auth::user()->role == 'guru')
                        <span class="rounded-full bg-indigo-100 px-2.5 py-1 text-xs font-semibold text-indigo-700">Input nilai</span>
                    @endif
                </div>

                @if(Auth::user()->role == 'admin' || Auth::user()->role == 'guru')
                    <div class="mb-4 flex justify-end">
                        <a href="{{ route('report.grade.pdf') }}" class="inline-flex items-center justify-center rounded-xl border border-indigo-200 bg-indigo-50 px-4 py-2.5 font-semibold text-indigo-700 transition hover:bg-indigo-100">
                            Download PDF Nilai
                        </a>
                    </div>

                    <form action="{{ route('grades.store') }}" method="POST" class="mb-6 grid gap-3 rounded-xl border border-gray-200 bg-gray-50 p-4 md:grid-cols-6">
                        @csrf

                        <div class="md:col-span-2">
                            <label class="mb-1 block text-sm font-medium text-gray-700">Siswa</label>
                            <select name="user_id" required class="w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Pilih siswa</option>
                                @foreach($students as $student)
                                    <option value="{{ $student->id }}">{{ $student->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Semester</label>
                            <input type="text" name="semester" required placeholder="2026/2027 Ganjil" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Nilai</label>
                            <input type="number" name="nilai" min="0" max="100" required value="{{ old('nilai') }}" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('nilai')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-4">
                            <label for="grade_catatan" class="mb-1 block text-sm font-medium text-gray-700">Catatan evaluasi</label>
                            <textarea id="grade_catatan" name="catatan" rows="2" maxlength="1000" placeholder="Tulis evaluasi atau masukan untuk siswa (opsional)" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('catatan') }}</textarea>
                            @error('catatan')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-end md:col-span-2">
                            <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-indigo-600 px-4 py-2.5 font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                Simpan Nilai
                            </button>
                        </div>
                    </form>
                @endif

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                @if(Auth::user()->role !== 'siswa')
                                    <th class="px-4 py-3 text-left font-medium uppercase text-gray-500">Nama siswa</th>
                                @endif
                                <th class="px-4 py-3 text-left font-medium uppercase text-gray-500">Semester</th>
                                <th class="px-4 py-3 text-left font-medium uppercase text-gray-500">Nilai</th>
                                <th class="px-4 py-3 text-left font-medium uppercase text-gray-500">Catatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($grades as $grade)
                                <tr>
                                    @if(Auth::user()->role !== 'siswa')
                                        <td class="px-4 py-3 font-medium text-gray-800">{{ $grade->user->name }}</td>
                                    @endif
                                    <td class="px-4 py-3 text-gray-600">{{ $grade->semester }}</td>
                                    <td class="px-4 py-3 font-semibold text-gray-800">{{ $grade->nilai }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $grade->catatan ?: '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ Auth::user()->role === 'siswa' ? 3 : 4 }}" class="px-4 py-6 text-center text-gray-500">
                                        Belum ada rekap nilai.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>

    <div id="logbookImageModal" class="fixed inset-0 z-[100] hidden items-center justify-center bg-black/90 p-4" role="dialog" aria-modal="true" aria-label="Foto kegiatan ukuran penuh" aria-hidden="true">
        <button type="button" class="absolute inset-0 h-full w-full cursor-zoom-out" data-close-image-modal aria-label="Tutup foto"></button>
        <button type="button" class="absolute right-4 top-4 z-10 rounded-full bg-white/10 px-4 py-2 text-sm font-semibold text-white transition hover:bg-white/20" data-close-image-modal aria-label="Tutup foto">
            Tutup <span aria-hidden="true">&times;</span>
        </button>
        <img id="logbookImageModalContent" src="" alt="Foto kegiatan ukuran penuh" class="pointer-events-none relative z-[1] max-h-[90vh] max-w-[95vw] object-contain shadow-2xl">
    </div>

    <script>
        (() => {
            const modal = document.getElementById('logbookImageModal');
            const modalImage = document.getElementById('logbookImageModalContent');

            if (!modal || !modalImage) return;

            const closeModal = () => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                modal.setAttribute('aria-hidden', 'true');
                modalImage.src = '';
                document.body.classList.remove('overflow-hidden');
            };

            document.querySelectorAll('.open-image-btn').forEach((button) => {
                button.addEventListener('click', () => {
                    modalImage.src = button.dataset.image;
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                    modal.setAttribute('aria-hidden', 'false');
                    document.body.classList.add('overflow-hidden');
                });
            });

            modal.querySelectorAll('[data-close-image-modal]').forEach((button) => {
                button.addEventListener('click', closeModal);
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
                    closeModal();
                }
            });
        })();
    </script>
</x-app-layout>
