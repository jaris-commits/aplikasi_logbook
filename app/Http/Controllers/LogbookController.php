<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use App\Models\Logbook;
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
            $logbooks = Logbook::with('user')
                ->where('user_id', $user->id)
                ->orderBy('tanggal', 'desc')
                ->get();

        } elseif ($user->role === 'guru') {

            // Guru hanya melihat logbook siswa bimbingannya
            $logbooks = Logbook::with('user')
                ->whereHas('user', function ($query) use ($user) {
                    $query->where('guru_id', $user->id)
                        ->where('role', 'siswa');
                })
                ->orderBy('tanggal', 'desc')
                ->get();

        } elseif ($user->role === 'admin') {

            // Admin dapat melihat seluruh logbook
            $logbooks = Logbook::with('user')
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

        return view('dashboard', compact('logbooks', 'grades', 'students'));
    }

    // MENAMBAHKAN LOGBOOK
    public function store(Request $request)
    {
        $data = $request->validate([
            'tanggal' => 'required|date',
            'aktivitas' => 'required|string|max:10000',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('logbooks', 'public');
        }

        Logbook::create([
            'user_id' => Auth::id(),
            'tanggal' => $data['tanggal'],
            'aktivitas' => $data['aktivitas'],
            'foto' => $data['foto'] ?? null,
            'status' => 'pending',
        ]);

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
        ]);

        if ($request->hasFile('foto')) {
            if ($logbook->foto && Storage::disk('public')->exists($logbook->foto)) {
                Storage::disk('public')->delete($logbook->foto);
            }

            $data['foto'] = $request->file('foto')->store('logbooks', 'public');
        }

        $logbook->update([
            'tanggal' => $data['tanggal'],
            'aktivitas' => $data['aktivitas'],
            'foto' => $data['foto'] ?? $logbook->foto,
            'status' => 'pending',
            'catatan_guru' => null,
        ]);

        return redirect()->route('dashboard')
            ->with('success', 'Logbook berhasil diperbarui!');
    }

    // GURU / ADMIN MENYETUJUI ATAU MEMINTA REVISI
    public function updateStatus(Request $request, $id)
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
