<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-sky-700">Kolaborasi</p>
            <h2 class="mt-1 text-2xl font-bold text-slate-900">Kelompok Proyek</h2>
            <p class="mt-1 text-sm text-slate-500">Kelola tim dan konteks kolaborasi siswa magang.</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if(session('success'))<div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">{{ session('success') }}</div>@endif
            @if(Auth::user()->role === 'admin' || $students->isNotEmpty())
                <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                    <h3 class="font-semibold text-slate-900">Buat kelompok proyek</h3>
                    <form action="{{ route('projects.store') }}" method="POST" class="mt-4 grid gap-4 md:grid-cols-2">
                        @csrf
                        <div><label for="project_name" class="mb-1 block text-sm font-medium text-slate-700">Nama proyek</label><input id="project_name" name="name" required maxlength="150" value="{{ old('name') }}" class="w-full rounded-lg border-slate-300"></div>
                        <div><label for="project_description" class="mb-1 block text-sm font-medium text-slate-700">Deskripsi</label><input id="project_description" name="description" maxlength="2000" value="{{ old('description') }}" class="w-full rounded-lg border-slate-300"></div>
                        <fieldset class="md:col-span-2"><legend class="mb-2 text-sm font-medium text-slate-700">Anggota siswa</legend><div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach($students as $student)
                                <label class="flex items-center gap-2 rounded-lg border border-slate-200 p-3 text-sm text-slate-700"><input type="checkbox" name="student_ids[]" value="{{ $student->id }}" class="rounded border-slate-300 text-sky-700">{{ $student->name }}</label>
                            @endforeach
                        </div></fieldset>
                        <div class="md:col-span-2"><button class="rounded-lg bg-sky-800 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-sky-900">Simpan kelompok</button></div>
                    </form>
                </section>
            @endif

            <section class="space-y-3">
                @forelse($projects as $project)
                    <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div><h3 class="font-semibold text-slate-900">{{ $project->name }}</h3><p class="mt-1 text-sm text-slate-600">{{ $project->description ?: 'Tanpa deskripsi' }}</p></div>
                            @if(Auth::user()->role === 'admin' || $project->created_by === Auth::id())
                                <form action="{{ route('projects.destroy', $project) }}" method="POST" onsubmit="return confirm('Hapus kelompok proyek ini?')">@csrf @method('DELETE')<button class="text-sm font-semibold text-rose-700 hover:text-rose-900">Hapus</button></form>
                            @endif
                        </div>
                        <div class="mt-4 flex flex-wrap gap-2">@foreach($project->students as $student)<span class="rounded-full bg-sky-50 px-3 py-1 text-xs font-medium text-sky-900">{{ $student->name }}</span>@endforeach</div>
                    </article>
                @empty
                    <p class="rounded-xl border border-dashed border-slate-300 bg-white p-8 text-center text-sm text-slate-500">Belum ada kelompok proyek.</p>
                @endforelse
            </section>
        </div>
    </div>
</x-app-layout>