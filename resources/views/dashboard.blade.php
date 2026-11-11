<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-emerald-700">SMKN 20 Jakarta <span class="mx-1 text-slate-300">/</span> Logbook Magang</p>
                <h2 class="mt-2 text-3xl font-bold text-slate-900">Dashboard <span class="font-medium text-slate-500">{{ ucfirst(Auth::user()->role) }}</span></h2>
                <p class="mt-1 text-sm text-slate-500">Pantau kegiatan praktik kerja lapangan dan perkembangan terbaru.</p>
            </div>
            <span class="inline-flex w-fit items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-semibold uppercase tracking-wide text-emerald-800">
                <span class="h-1.5 w-1.5 rounded-full bg-emerald-600"></span>
                {{ ucfirst(Auth::user()->role) }} aktif
            </span>
        </div>
    </x-slot>

    <div class="bg-gradient-to-b from-white/70 via-[#f4f6f5] to-sky-50/40 py-7 sm:py-9">
        <div class="mx-auto max-w-[1440px] space-y-8 px-4 sm:px-6 lg:px-8 xl:px-10">

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
                <section class="dashboard-enter rounded-xl border border-sky-200 bg-gradient-to-br from-sky-100/80 via-white to-cyan-50/70 p-5 shadow-sm transition duration-300 hover:border-sky-300 hover:shadow-lg hover:shadow-sky-900/5 sm:p-6">
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

            @if (Auth::user()->role === 'siswa')
                <section class="grid gap-4 lg:grid-cols-[minmax(0,1.4fr)_minmax(260px,0.6fr)]">
                    <div class="rounded-xl border border-emerald-200 bg-gradient-to-br from-emerald-50 via-white to-sky-50 p-5 shadow-sm sm:p-6">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div><p class="text-xs font-semibold uppercase tracking-wide text-emerald-800">Perjalanan PKL</p><h3 class="mt-1 text-lg font-bold text-slate-900">Progress magang</h3></div>
                            @if($internshipProgress['configured'])<span class="text-2xl font-bold tabular-nums text-emerald-800">{{ $internshipProgress['percent'] }}%</span>@endif
                        </div>
                        @if($internshipProgress['configured'])
                            <div class="mt-4 h-3 overflow-hidden rounded-full bg-white ring-1 ring-inset ring-emerald-100"><div class="h-full rounded-full bg-gradient-to-r from-emerald-600 via-teal-500 to-sky-500 transition-all duration-700" style="width: {{ $internshipProgress['percent'] }}%"></div></div>
                            <p class="mt-2 text-sm text-slate-600">{{ $internshipProgress['elapsed_days'] }} dari {{ $internshipProgress['total_days'] }} hari kerja · {{ Auth::user()->internship_start->format('d/m/Y') }} – {{ Auth::user()->internship_end->format('d/m/Y') }}</p>
                        @else
                            <p class="mt-3 text-sm text-slate-600">Periode magang belum diatur. Hubungi admin untuk menetapkan tanggal mulai dan selesai.</p>
                        @endif
                    </div>

                    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                        <p class="text-xs font-semibold uppercase tracking-wide text-sky-800">Kehadiran hari ini</p>
                        <h3 class="mt-1 text-lg font-bold text-slate-900">{{ $todayAttendance ? ($todayAttendance->check_out_at ? 'Absensi lengkap' : 'Sudah masuk') : 'Belum absen' }}</h3>
                        <p class="mt-1 text-sm text-slate-500">{{ $todayAttendance?->mode ? $todayAttendance->mode.' · masuk '.$todayAttendance->check_in_at?->format('H:i') : 'Catat masuk dan pulang saat kegiatan magang.' }}</p>
                        <a href="{{ route('attendance.index') }}" class="mt-4 inline-flex rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-700">Buka absensi</a>
                    </div>
                </section>
            @endif

            @if($unreadNotifications->isNotEmpty())
                <section class="rounded-xl border border-amber-200 bg-gradient-to-r from-amber-50 to-white p-5 shadow-sm">
                    <div class="flex flex-wrap items-center justify-between gap-3"><div><p class="text-xs font-semibold uppercase tracking-wide text-amber-800">Pembaruan terbaru</p><p class="mt-1 text-sm font-medium text-slate-800">{{ $unreadNotifications->first()->data['title'] ?? 'Notifikasi baru' }} · {{ $unreadNotifications->first()->data['message'] ?? '' }}</p></div><a href="{{ route('notifications.index') }}" class="text-sm font-semibold text-amber-900 hover:underline">Lihat semua ({{ $unreadNotificationCount }})</a></div>
                </section>
            @endif

            <section aria-labelledby="activity-statistics-title" class="space-y-4">
                <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-emerald-700">Ringkasan aktivitas</p>
                        <h3 id="activity-statistics-title" class="mt-1 text-lg font-bold text-slate-900">Statistik bulan ini</h3>
                    </div>
                    <p class="text-sm text-slate-500">{{ now()->translatedFormat('F Y') }} · {{ $statistics['week_total'] }} entri dalam 7 hari terakhir</p>
                </div>

                <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="dashboard-enter rounded-xl border border-emerald-100 border-l-4 border-l-emerald-600 bg-gradient-to-br from-white to-emerald-50/70 p-5 shadow-sm transition duration-300 hover:-translate-y-1 hover:border-emerald-200 hover:shadow-lg hover:shadow-emerald-900/5">
                        <p class="text-sm font-medium text-slate-500">Total bulan ini</p>
                        <div class="mt-3 flex items-end justify-between">
                            <span class="text-3xl font-bold tabular-nums text-slate-900">{{ $statistics['month_total'] }}</span>
                            <span class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Entri</span>
                        </div>
                    </div>

                    <div class="dashboard-enter rounded-xl border border-teal-100 border-l-4 border-l-teal-600 bg-gradient-to-br from-white to-teal-50/70 p-5 shadow-sm transition duration-300 hover:-translate-y-1 hover:border-teal-200 hover:shadow-lg hover:shadow-teal-900/5" style="animation-delay: 70ms">
                        <p class="text-sm font-medium text-slate-500">Disetujui</p>
                        <div class="mt-3 flex items-end justify-between">
                            <span class="text-3xl font-bold tabular-nums text-slate-900">{{ $statistics['month_approved'] }}</span>
                            <span class="text-xs font-semibold uppercase tracking-wide text-teal-700">Selesai</span>
                        </div>
                    </div>

                    <div class="dashboard-enter rounded-xl border border-rose-100 border-l-4 border-l-rose-500 bg-gradient-to-br from-white to-rose-50/70 p-5 shadow-sm transition duration-300 hover:-translate-y-1 hover:border-rose-200 hover:shadow-lg hover:shadow-rose-900/5" style="animation-delay: 140ms">
                        <p class="text-sm font-medium text-slate-500">Revisi</p>
                        <div class="mt-3 flex items-end justify-between">
                            <span class="text-3xl font-bold tabular-nums text-slate-900">{{ $statistics['month_revision'] }}</span>
                            <span class="text-xs font-semibold uppercase tracking-wide text-rose-700">Tindak lanjut</span>
                        </div>
                    </div>

                    <div class="dashboard-enter rounded-xl border border-amber-100 border-l-4 border-l-amber-500 bg-gradient-to-br from-white to-amber-50/70 p-5 shadow-sm transition duration-300 hover:-translate-y-1 hover:border-amber-200 hover:shadow-lg hover:shadow-amber-900/5" style="animation-delay: 210ms">
                        <p class="text-sm font-medium text-slate-500">Menunggu</p>
                        <div class="mt-3 flex items-end justify-between">
                            <span class="text-3xl font-bold tabular-nums text-slate-900">{{ $statistics['month_pending'] }}</span>
                            <span class="text-xs font-semibold uppercase tracking-wide text-amber-700">Perlu dicek</span>
                        </div>
                    </div>
                </div>

                <div class="dashboard-enter rounded-xl border border-slate-200 bg-gradient-to-br from-white via-white to-slate-50 p-5 shadow-sm transition duration-300 hover:shadow-md sm:p-6" style="animation-delay: 120ms">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <h4 class="font-semibold text-slate-900">Aktivitas logbook harian</h4>
                            <p class="mt-1 text-sm text-slate-500">Jumlah catatan masuk selama tujuh hari terakhir</p>
                        </div>
                        <span class="rounded-full bg-sky-50 px-3 py-1 text-xs font-semibold text-sky-800">7 hari</span>
                    </div>

                    <div class="mt-6 grid h-44 grid-cols-7 items-end gap-2 sm:h-52 sm:gap-4" role="img" aria-label="Grafik jumlah logbook per hari selama tujuh hari terakhir">
                        @foreach($weeklyActivity as $day)
                            <div class="flex h-full min-w-0 flex-col items-center justify-end gap-2" aria-label="{{ $day['date'] }}: {{ $day['count'] }} logbook">
                                <span class="text-xs font-semibold text-slate-700">{{ $day['count'] }}</span>
                                <div class="flex h-full w-full items-end justify-center">
                                    <div class="dashboard-bar-enter w-full max-w-10 rounded-t-md bg-gradient-to-t from-emerald-700 via-teal-500 to-sky-300 shadow-sm shadow-teal-900/10 transition duration-300 hover:brightness-110" style="height: {{ $day['count'] === 0 ? 4 : max(12, (int) round(($day['count'] / $peakDailyActivity) * 100)) }}%; animation-delay: {{ $loop->index * 65 }}ms"></div>
                                </div>
                                <span class="text-[11px] font-medium text-slate-500">{{ $day['label'] }}</span>
                                <span class="text-[10px] text-slate-400">{{ $day['date'] }}</span>
                            </div>
                        @endforeach
                    </div>

                    @if($statistics['week_total'] === 0)
                        <p class="mt-4 text-center text-sm text-slate-500">Belum ada aktivitas logbook dalam tujuh hari terakhir.</p>
                    @endif
                </div>
            </section>

            @if(Auth::user()->role == 'siswa')
                <section class="dashboard-enter rounded-xl border border-emerald-100 bg-gradient-to-br from-white via-white to-emerald-50/50 p-5 shadow-sm transition duration-300 hover:shadow-md sm:p-6">
                    <div class="mb-5 flex items-center gap-3 border-b border-slate-100 pb-5">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100 text-lg text-emerald-800">✍️</div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-800">Tambah Catatan Logbook Baru</h3>
                            <p class="text-sm text-gray-500">Catat aktivitas harianmu selama magang.</p>
                        </div>
                    </div>

                    <form action="{{ route('logbook.store') }}" method="POST" enctype="multipart/form-data" class="grid gap-4 md:grid-cols-2">
                        @csrf
                        <div class="md:col-span-1">
                            <label class="mb-2 block text-sm font-medium text-gray-700">Tanggal kegiatan</label>
                            <input type="date" name="tanggal" required class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                        </div>

                        <div class="md:col-span-2">
                            <label class="mb-2 block text-sm font-medium text-gray-700">Aktivitas / kegiatan magang</label>
                            <textarea name="aktivitas" rows="4" required placeholder="Tuliskan apa saja yang Anda kerjakan hari ini..." class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600"></textarea>
                        </div>

                        @if($projects->isNotEmpty())
                            <div class="md:col-span-2">
                                <label for="logbook_project" class="mb-2 block text-sm font-medium text-gray-700">Proyek terkait (opsional)</label>
                                <select id="logbook_project" name="project_id" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                                    <option value="">Aktivitas individu / tidak terkait proyek</option>
                                    @foreach($projects as $project)
                                        <option value="{{ $project->id }}">{{ $project->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="md:col-span-2">
                            <label class="mb-2 block text-sm font-medium text-gray-700">Bukti foto kegiatan</label>
                            <input id="fotoInput" type="file" name="foto" accept="image/*" class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm file:mr-3 file:rounded-md file:border-0 file:bg-slate-100 file:px-3 file:py-1.5 file:text-sm file:font-semibold file:text-slate-700 hover:file:bg-slate-200">

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
                            <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-gradient-to-r from-emerald-700 to-teal-700 px-5 py-2.5 font-semibold text-white shadow-sm shadow-emerald-900/15 transition duration-300 hover:-translate-y-0.5 hover:from-emerald-800 hover:to-teal-800 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-emerald-700 focus:ring-offset-2">
                                Simpan Logbook
                            </button>

                            <a href="{{ route('report.logbook.pdf') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2.5 font-semibold text-slate-700 transition hover:bg-slate-50">
                                Download PDF Logbook
                            </a>
                        </div>
                    </form>
                </section>
            @endif

            <section class="dashboard-enter overflow-hidden rounded-xl border border-slate-200 bg-gradient-to-b from-white to-slate-50/60 shadow-sm transition duration-300 hover:shadow-md">
                <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-emerald-700">Jurnal kegiatan</p>
                        <h3 class="mt-1 text-lg font-bold text-slate-900">Riwayat logbook</h3>
                        <p class="mt-1 text-sm text-slate-500">Pantau status kegiatan dan umpan balik pembimbing.</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50/80">
                            <tr>
                                <th class="whitespace-nowrap px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Nama</th>
                                <th class="whitespace-nowrap px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Tanggal</th>
                                <th class="min-w-64 px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Aktivitas</th>
                                <th class="whitespace-nowrap px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Status</th>
                                <th class="min-w-48 px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Catatan</th>
                                <th class="min-w-40 px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse($logbooks as $log)
                                <tr class="align-top transition-colors duration-200 hover:bg-emerald-50/50">
                                    <td class="whitespace-nowrap px-5 py-4 font-semibold text-slate-800">{{ $log->user->name ?? Auth::user()->name }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-slate-500">{{ $log->tanggal }}</td>
                                    <td class="px-5 py-4 text-slate-700">
                                        <div class="space-y-2">
                                            <p>{{ $log->aktivitas }}</p>
                                            @if($log->project)
                                                <span class="inline-flex rounded-md bg-sky-50 px-2 py-1 text-xs font-medium text-sky-800">Proyek: {{ $log->project->name }}</span>
                                            @endif
                                            @if($log->foto)
                                                <button type="button" class="open-image-btn cursor-zoom-in" data-image="{{ asset('storage/' . $log->foto) }}" aria-label="Buka foto kegiatan ukuran penuh">
                                                    <img src="{{ asset('storage/' . $log->foto) }}" alt="Foto kegiatan" class="h-20 w-20 rounded-lg border border-gray-200 object-cover transition hover:scale-105">
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4">
                                        <span class="inline-flex rounded-md px-2.5 py-1 text-xs font-semibold
                                            {{ $log->status == 'disetujui' ? 'bg-teal-50 text-teal-800 ring-1 ring-inset ring-teal-200' : ($log->status == 'revisi' ? 'bg-rose-50 text-rose-800 ring-1 ring-inset ring-rose-200' : 'bg-amber-50 text-amber-800 ring-1 ring-inset ring-amber-200') }}">
                                            {{ $log->status }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-slate-600">{{ $log->catatan_guru ?? '-' }}</td>
                                    <td class="px-5 py-4">
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

            <section class="dashboard-enter rounded-xl border border-slate-200 bg-gradient-to-br from-white via-white to-sky-50/40 p-5 shadow-sm transition duration-300 hover:shadow-md sm:p-6">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-emerald-700">Evaluasi</p>
                        <h3 class="mt-1 text-lg font-bold text-slate-900">Rekap Nilai Magang</h3>
                        <p class="mt-1 text-sm text-slate-500">
                            {{ Auth::user()->role === 'guru' ? 'Nilai siswa bimbingan Anda.' : 'Nilai akhir dan catatan evaluasi siswa.' }}
                        </p>
                    </div>
                    @if(Auth::user()->role == 'admin' || Auth::user()->role == 'guru')
                        <span class="rounded-md bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-800 ring-1 ring-inset ring-emerald-200">Input nilai</span>
                    @endif
                </div>

                @if(Auth::user()->role == 'admin' || Auth::user()->role == 'guru')
                    <div class="mb-4 flex justify-end">
                        <a href="{{ route('report.grade.pdf') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2.5 font-semibold text-slate-700 transition hover:bg-slate-50">
                            Download PDF Nilai
                        </a>
                    </div>

                    <form action="{{ route('grades.store') }}" method="POST" class="mb-6 grid gap-4 rounded-lg border border-slate-200 bg-slate-50/80 p-4 md:grid-cols-6">
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

            @if(in_array(Auth::user()->role, ['admin', 'guru']))
                <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                    <div class="mb-5">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Pengesahan internal</p>
                        <h3 class="mt-1 text-lg font-bold text-slate-900">Tanda tangan laporan PDF</h3>
                        <p class="mt-1 text-sm text-slate-600">Tanda tangan gambar ini bukan tanda tangan elektronik tersertifikasi dan tidak dengan sendirinya menjamin kekuatan hukum. Konfirmasi password diperlukan.</p>
                    </div>
                    <div class="grid gap-5 lg:grid-cols-2">
                        @foreach(['logbook' => 'Laporan logbook', 'grades' => 'Rekap nilai'] as $reportType => $reportLabel)
                            <form action="{{ route('report.signature') }}" method="POST" class="signature-form rounded-lg border border-slate-200 bg-slate-50/70 p-4">
                                @csrf
                                <input type="hidden" name="report_type" value="{{ $reportType }}">
                                <input type="hidden" name="signature" value="">
                                <div class="flex items-center justify-between gap-2">
                                    <label class="text-sm font-semibold text-slate-800">{{ $reportLabel }}</label>
                                    <button type="button" class="clear-signature rounded-md px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-white">Bersihkan</button>
                                </div>
                                <canvas class="signature-canvas mt-3 h-28 w-full touch-none rounded-md border border-dashed border-slate-300 bg-white" aria-label="Area tanda tangan untuk {{ $reportLabel }}"></canvas>
                                <label class="mt-3 block text-xs font-medium text-slate-600">Konfirmasi password akun</label>
                                <input type="password" name="password" required autocomplete="current-password" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-emerald-600 focus:ring-emerald-600">
                                <button type="submit" class="mt-3 rounded-lg bg-slate-800 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-700">Simpan tanda tangan</button>
                            </form>
                        @endforeach
                    </div>
                </section>
            @endif
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
            document.querySelectorAll('.signature-form').forEach((form) => {
                const canvas = form.querySelector('.signature-canvas');
                const context = canvas.getContext('2d');
                let drawing = false;
                let hasInk = false;

                const resizeCanvas = () => {
                    const bounds = canvas.getBoundingClientRect();
                    const ratio = window.devicePixelRatio || 1;
                    const strokes = hasInk ? drawPaths : [];
                    canvas.width = Math.max(1, Math.round(bounds.width * ratio));
                    canvas.height = Math.max(1, Math.round(bounds.height * ratio));
                    context.setTransform(ratio, 0, 0, ratio, 0, 0);
                    context.lineWidth = 2.5;
                    context.lineCap = 'round';
                    context.lineJoin = 'round';
                    context.strokeStyle = '#0f172a';
                    if (strokes.length) redrawStrokes();
                };

                const drawPaths = [];
                let activePath = null;
                const redrawStrokes = () => {
                    context.clearRect(0, 0, canvas.width, canvas.height);
                    drawPaths.forEach((path) => {
                        if (!path.length) return;
                        context.beginPath();
                        context.moveTo(path[0].x, path[0].y);
                        path.slice(1).forEach((point) => context.lineTo(point.x, point.y));
                        context.stroke();
                    });
                };

                const point = (event) => {
                    const bounds = canvas.getBoundingClientRect();
                    return { x: event.clientX - bounds.left, y: event.clientY - bounds.top };
                };

                canvas.addEventListener('pointerdown', (event) => {
                    drawing = true;
                    hasInk = true;
                    canvas.setPointerCapture(event.pointerId);
                    const position = point(event);
                    activePath = [position];
                    drawPaths.push(activePath);
                    context.beginPath();
                    context.moveTo(position.x, position.y);
                });
                canvas.addEventListener('pointermove', (event) => {
                    if (!drawing) return;
                    const position = point(event);
                    activePath.push(position);
                    context.lineTo(position.x, position.y);
                    context.stroke();
                });
                ['pointerup', 'pointercancel', 'lostpointercapture'].forEach((eventName) => {
                    canvas.addEventListener(eventName, () => { drawing = false; });
                });
                form.querySelector('.clear-signature').addEventListener('click', () => {
                    context.clearRect(0, 0, canvas.width, canvas.height);
                    drawPaths.length = 0;
                    activePath = null;
                    hasInk = false;
                });
                form.addEventListener('submit', (event) => {
                    if (!hasInk) {
                        event.preventDefault();
                        alert('Buat tanda tangan terlebih dahulu.');
                        return;
                    }
                    const paths = drawPaths.map((points) => {
                        const commands = points.map((point, index) => `${index === 0 ? 'M' : 'L'}${(point.x / canvas.clientWidth * 600).toFixed(2)},${(point.y / canvas.clientHeight * 180).toFixed(2)}`).join(' ');
                        return `<path d="${commands}"/>`;
                    }).join('');
                    const svg = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 180">${paths}</svg>`;
                    form.querySelector('[name="signature"]').value = `data:image/svg+xml;base64,${btoa(svg)}`;
                });
                resizeCanvas();
                window.addEventListener('resize', resizeCanvas);
            });

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
