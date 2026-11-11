
<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">
            Edit Logbook Magang
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white shadow-sm rounded-lg p-6">

                <h3 class="text-lg font-bold mb-5">
                    Form Edit Kegiatan
                </h3>

                <form action="{{ route('logbook.update', $logbook->id) }}"
                      method="POST">

                    @csrf
                    @method('PUT')

                    <div class="mb-4">

                        <label class="block font-medium mb-2">
                            Tanggal Kegiatan
                        </label>

                        <input
                            type="date"
                            name="tanggal"
                            value="{{ old('tanggal', $logbook->tanggal) }}"
                            class="w-full border-gray-300 rounded-md"
                            required
                        >

                        @error('tanggal')
                            <p class="text-red-500 text-sm">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>

                    @if(auth()->user()->projects()->exists())
                        <div class="mb-4">
                            <label for="project_id" class="mb-2 block font-medium">Proyek terkait (opsional)</label>
                            <select id="project_id" name="project_id" class="w-full rounded-md border-gray-300">
                                <option value="">Aktivitas individu / tidak terkait proyek</option>
                                @foreach(auth()->user()->projects()->orderBy('name')->get() as $project)
                                    <option value="{{ $project->id }}" @selected(old('project_id', $logbook->project_id) == $project->id)>{{ $project->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="mb-4">

                        <label class="block font-medium mb-2">
                            Aktivitas Magang
                        </label>

                        <textarea
                            name="aktivitas"
                            rows="5"
                            class="w-full border-gray-300 rounded-md"
                            required
                        >{{ old('aktivitas', $logbook->aktivitas) }}</textarea>

                        @error('aktivitas')
                            <p class="text-red-500 text-sm">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>

                    <div class="flex gap-3">

                        <button
                            type="submit"
                            class="bg-blue-600 text-white px-5 py-2 rounded-md"
                        >
                            Simpan Perubahan
                        </button>

                        <a
                            href="{{ route('dashboard') }}"
                            class="bg-gray-500 text-white px-5 py-2 rounded-md"
                        >
                            Kembali
                        </a>

                    </div>

                </form>

            </div>

        </div>
    </div>

</x-app-layout>
