<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(Request $request): View
    {
        $projects = Project::with(['students', 'creator'])
            ->when($request->user()->role === 'guru', fn ($query) => $query->whereHas('students', fn ($students) =>
                $students->where('users.guru_id', $request->user()->id)))
            ->latest()
            ->get();

        $students = User::where('role', 'siswa')
            ->when($request->user()->role === 'guru', fn ($query) => $query->where('guru_id', $request->user()->id))
            ->orderBy('name')
            ->get();

        return view('projects.index', compact('projects', 'students'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:2000'],
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => ['required', 'integer', 'distinct', 'exists:users,id'],
        ]);

        $students = User::whereIn('id', $data['student_ids'])
            ->where('role', 'siswa')
            ->when($request->user()->role === 'guru', fn ($query) => $query->where('guru_id', $request->user()->id))
            ->get();

        abort_unless($students->count() === count($data['student_ids']), 403, 'Siswa yang dipilih tidak berada dalam cakupan Anda.');

        DB::transaction(function () use ($data, $students, $request) {
            $project = Project::create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'created_by' => $request->user()->id,
            ]);
            $project->students()->sync($students->modelKeys());
        });

        return back()->with('success', 'Kelompok proyek berhasil dibuat.');
    }

    public function destroy(Request $request, Project $project): RedirectResponse
    {
        abort_unless($request->user()->role === 'admin' || $project->created_by === $request->user()->id, 403);
        $project->delete();

        return back()->with('success', 'Kelompok proyek berhasil dihapus.');
    }
}