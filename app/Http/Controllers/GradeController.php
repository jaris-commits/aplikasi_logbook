<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use App\Models\User;
use App\Services\WorkflowNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GradeController extends Controller
{
    public function store(Request $request, WorkflowNotifier $notifier)
    {
        $user = Auth::user();

        abort_unless(in_array($user->role, ['admin', 'guru']), 403, 'Anda tidak memiliki akses untuk menilai siswa.');

        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'nilai' => ['required', 'numeric', 'min:0', 'max:100'],
            'semester' => ['required', 'string', 'max:255'],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ]);

        $student = User::whereKey($data['user_id'])
            ->where('role', 'siswa')
            ->firstOrFail();

        if ($user->role === 'guru') {
            abort_unless(
                $student->guru_id == $user->id,
                403,
                'Anda bukan guru pembimbing siswa ini.'
            );
        }

        Grade::updateOrCreate(
            [
                'user_id' => $student->id,
                'semester' => $data['semester'],
            ],
            [
                'nilai' => $data['nilai'],
                'catatan' => $data['catatan'] ?? null,
            ]
        );

        $notifier->notify($student,
            'Nilai magang diperbarui',
            'Guru telah memperbarui nilai magang Anda untuk semester '.$data['semester'].'.',
            route('dashboard')
        );

        return redirect()->route('dashboard')->with('success', 'Nilai siswa berhasil disimpan!');
    }
}
