<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use App\Models\Logbook;
use App\Models\Attendance;
use App\Services\WorkflowNotifier;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class LogbookController extends Controller
{
    // DASHBOARD LOGBOOK
    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'siswa') {

            // Siswa hanya melihat logbook miliknya sendiri
            $logbooks = Logbook::with(['user', 'project'])
                ->where('user_id', $user->id)
                ->orderBy('tanggal', 'desc')
                ->get();

        } elseif ($user->role === 'guru') {

            // Guru hanya melihat logbook siswa bimbingannya
            $logbooks = Logbook::with(['user', 'project'])
                ->whereHas('user', function ($query) use ($user) {
                    $query->where('guru_id', $user->id)
                        ->where('role', 'siswa');
                })
                ->orderBy('tanggal', 'desc')
                ->get();

        } elseif ($user->role === 'admin') {

            // Admin dapat melihat seluruh logbook
            $logbooks = Logbook::with(['user', 'project'])
                ->orderBy('tanggal', 'desc')
                ->get();

        } else {
            abort(403, 'Role tidak dikenali.');
        }

        $grades = Grade::with('user')
            ->when($user->role === 'siswa', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->when($user->role === 'guru', function ($query) use ($user) {
                $query->whereHas('user', function ($studentQuery) use ($user) {
                    $studentQuery->where('guru_id', $user->id)
                        ->where('role', 'siswa');
                });
            })
            ->orderBy('semester', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        $students = $user->role === 'admin'
            ? \App\Models\User::where('role', 'siswa')->orderBy('name')->get()
            : ($user->role === 'guru' ? $user->siswaBimbingan()->orderBy('name')->get() : collect());
        $projects = $user->role === 'siswa' ? $user->projects()->orderBy('name')->get() : collect();

        $today = CarbonImmutable::today();
        $monthLogbooks = $logbooks->filter(fn (Logbook $logbook) =>
            CarbonImmutable::parse($logbook->tanggal)->isSameMonth($today)
        );
        $weeklyActivity = collect(range(6, 0))->map(function (int $daysAgo) use ($logbooks, $today) {
            $date = $today->subDays($daysAgo);

            return [
                'label' => $date->translatedFormat('D'),
                'date' => $date->format('d/m'),
                'count' => $logbooks->filter(fn (Logbook $logbook) =>
                    CarbonImmutable::parse($logbook->tanggal)->isSameDay($date)
                )->count(),
            ];
        });
        $peakDailyActivity = max(1, (int) $weeklyActivity->max('count'));
        $internshipProgress = $user->role === 'siswa' ? $user->internshipProgress() : null;
        $todayAttendance = $user->role === 'siswa'
            ? $user->attendances()->whereDate('tanggal', today())->first()
            : null;
        $unreadNotifications = $user->unreadNotifications()->latest()->limit(5)->get();
        $unreadNotificationCount = $user->unreadNotifications()->count();

        $statistics = [
            'month_total' => $monthLogbooks->count(),
            'month_approved' => $monthLogbooks->where('status', 'disetujui')->count(),
            'month_revision' => $monthLogbooks->where('status', 'revisi')->count(),
            'month_pending' => $monthLogbooks->where('status', 'pending')->count(),
            'week_total' => $weeklyActivity->sum('count'),
        ];

        return view('dashboard', compact(
            'logbooks', 'grades', 'students', 'statistics', 'weeklyActivity',
            'peakDailyActivity', 'internshipProgress', 'todayAttendance',
            'unreadNotifications', 'unreadNotificationCount', 'projects'
        ));
    }

    // MENAMBAHKAN LOGBOOK
    public function store(Request $request, WorkflowNotifier $notifier)
    {
        $data = $request->validate([
            'tanggal' => 'required|date',
            'aktivitas' => 'required|string|max:10000',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'project_id' => ['nullable', 'integer', 'exists:projects,id'],
        ]);

        if (! empty($data['project_id'])) {
            abort_unless(Auth::user()->projects()->whereKey($data['project_id'])->exists(), 403, 'Anda bukan anggota proyek ini.');
        }

        if (! empty($data['project_id'])) {
            abort_unless(Auth::user()->projects()->whereKey($data['project_id'])->exists(), 403, 'Anda bukan anggota proyek ini.');
        }

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('logbooks', 'public');
        }

        Logbook::create([
            'user_id' => Auth::id(),
            'project_id' => $data['project_id'] ?? null,
            'tanggal' => $data['tanggal'],
            'aktivitas' => $data['aktivitas'],
            'foto' => $data['foto'] ?? null,
            'status' => 'pending',
        ]);

        $student = Auth::user()->loadMissing('guruPembimbing');
        if ($student->guruPembimbing) {
            $notifier->notify(
                $student->guruPembimbing,
                'Logbook baru',
                $student->name.' mengirim catatan logbook baru.',
                route('dashboard')
            );
        }

        return redirect()->route('dashboard')
            ->with('success', 'Logbook berhasil ditambahkan!');
    }

    // MENAMPILKAN FORM EDIT
    public function edit($id)
    {
        $logbook = Logbook::findOrFail($id);

        // Hanya pemilik logbook yang boleh mengedit
        abort_unless(
            $logbook->user_id == Auth::id(),
            403
        );

        // Logbook yang disetujui tidak boleh diedit
        abort_unless(
            in_array($logbook->status, ['pending', 'revisi']),
            403
        );

        return view('logbooks.edit', compact('logbook'));
    }

    // MEMPERBARUI LOGBOOK
    public function update(Request $request, $id)
    {
        $logbook = Logbook::findOrFail($id);

        abort_unless(
            $logbook->user_id == Auth::id(),
            403
        );

        abort_unless(
            in_array($logbook->status, ['pending', 'revisi']),
            403
        );

        $data = $request->validate([
            'tanggal' => 'required|date',
            'aktivitas' => 'required|string|max:10000',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'project_id' => ['nullable', 'integer', 'exists:projects,id'],
        ]);

        if (! empty($data['project_id'])) {
            abort_unless(Auth::user()->projects()->whereKey($data['project_id'])->exists(), 403, 'Anda bukan anggota proyek ini.');
        }

        if (! empty($data['project_id'])) {
            abort_unless(Auth::user()->projects()->whereKey($data['project_id'])->exists(), 403, 'Anda bukan anggota proyek ini.');
        }

        if ($request->hasFile('foto')) {
            if ($logbook->foto && Storage::disk('public')->exists($logbook->foto)) {
                Storage::disk('public')->delete($logbook->foto);
            }

            $data['foto'] = $request->file('foto')->store('logbooks', 'public');
        }

        $logbook->update([
            'tanggal' => $data['tanggal'],
            'project_id' => $data['project_id'] ?? null,
            'aktivitas' => $data['aktivitas'],
            'foto' => $data['foto'] ?? $logbook->foto,
            'status' => 'pending',
            'catatan_guru' => null,
        ]);

        return redirect()->route('dashboard')
            ->with('success', 'Logbook berhasil diperbarui!');
    }

    // GURU / ADMIN MENYETUJUI ATAU MEMINTA REVISI
    public function updateStatus(Request $request, $id, WorkflowNotifier $notifier)
    {
        $request->validate([
            'status' => 'required|in:disetujui,revisi',
            'catatan_guru' => 'nullable|string|max:5000',
        ]);

        $logbook = Logbook::with('user')->findOrFail($id);

        $user = Auth::user();

        // Admin boleh memeriksa semua logbook
        if ($user->role === 'admin') {

            // Admin memiliki akses ke seluruh logbook

        } elseif ($user->role === 'guru') {

            // Guru hanya boleh memeriksa logbook siswa bimbingannya
            abort_unless(
                $logbook->user &&
                $logbook->user->guru_id == $user->id &&
                $logbook->user->role === 'siswa',
                403,
                'Anda bukan guru pembimbing siswa ini.'
            );

        } else {
            abort(403, 'Anda tidak memiliki akses.');
        }

        $logbook->update([
            'status' => $request->status,
            'catatan_guru' => $request->catatan_guru,
        ]);

        $notifier->notify(
            $logbook->user,
            $request->status === 'revisi' ? 'Logbook perlu direvisi' : 'Logbook disetujui',
            $request->status === 'revisi'
                ? 'Guru meminta revisi pada catatan logbook Anda.'
                : 'Catatan logbook Anda telah disetujui.',
            route('dashboard')
        );

        return redirect()->route('dashboard')
            ->with('success', 'Status logbook berhasil diperbarui!');
    }

    // MENGHAPUS LOGBOOK
    public function destroy($id)
    {
        $logbook = Logbook::findOrFail($id);

        // Hanya pemilik logbook yang boleh menghapus
        abort_unless(
            $logbook->user_id == Auth::id(),
            403
        );

        // Hanya logbook pending yang boleh dihapus
        abort_unless(
            $logbook->status === 'pending',
            403
        );

        if ($logbook->foto && Storage::disk('public')->exists($logbook->foto)) {
            Storage::disk('public')->delete($logbook->foto);
        }

        $logbook->delete();

        return redirect()->route('dashboard')
            ->with('success', 'Logbook berhasil dihapus!');
    }
}
