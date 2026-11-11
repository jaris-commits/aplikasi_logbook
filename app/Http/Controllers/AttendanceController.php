<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $query = Attendance::with('user')->latest('tanggal');

        if ($user->role === 'siswa') {
            $query->where('user_id', $user->id);
        } elseif ($user->role === 'guru') {
            $query->whereHas('user', fn ($students) => $students
                ->where('guru_id', $user->id)
                ->where('role', 'siswa'));
        }

        $attendances = $query->limit(100)->get();
        $todayAttendance = $user->role === 'siswa'
            ? $user->attendances()->whereDate('tanggal', today())->first()
            : null;

        return view('attendance.index', compact('attendances', 'todayAttendance'));
    }

    public function checkIn(Request $request): RedirectResponse
    {
        abort_unless($request->user()->role === 'siswa', 403);

        $data = $request->validate([
            'mode' => ['required', 'in:WFO,WFH'],
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $existing = Attendance::where('user_id', $request->user()->id)
            ->whereDate('tanggal', today())
            ->first();

        if ($existing) {
            return back()->with('error', 'Absensi masuk hari ini sudah tercatat.');
        }

        $photoPath = $request->file('photo')->store('attendance', 'private');

        Attendance::create([
            'user_id' => $request->user()->id,
            'tanggal' => today(),
            'mode' => $data['mode'],
            'check_in_at' => now(),
            'check_in_photo' => $photoPath,
            'check_in_mime' => $request->file('photo')->getMimeType(),
        ]);

        return back()->with('success', 'Absensi masuk berhasil dicatat.');
    }

    public function checkOut(Request $request): RedirectResponse
    {
        abort_unless($request->user()->role === 'siswa', 403);

        $attendance = Attendance::where('user_id', $request->user()->id)
            ->whereDate('tanggal', today())
            ->firstOrFail();

        if (! $attendance->check_in_at || $attendance->check_out_at) {
            return back()->with('error', 'Absensi pulang tidak dapat dicatat.');
        }

        $attendance->update(['check_out_at' => now()]);

        return back()->with('success', 'Absensi pulang berhasil dicatat.');
    }

    public function photo(Request $request, Attendance $attendance): Response
    {
        $viewer = $request->user();
        $allowed = $viewer->role === 'admin'
            || $attendance->user_id === $viewer->id
            || ($viewer->role === 'guru' && $attendance->user()->where('guru_id', $viewer->id)->exists());
        abort_unless($allowed, 403);

        abort_unless($attendance->check_in_photo && Storage::disk('private')->exists($attendance->check_in_photo), 404);

        return response(Storage::disk('private')->get($attendance->check_in_photo))
            ->header('Content-Type', $attendance->check_in_mime ?: 'application/octet-stream')
            ->header('Cache-Control', 'private, no-store');
    }
}