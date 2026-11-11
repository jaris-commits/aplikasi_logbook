<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    // MENAMPILKAN SEMUA AKUN
    public function index()
    {
        $users = User::with('guruPembimbing')
            ->orderBy('role')
            ->orderBy('name')
            ->get();

        $totalAdmin = User::where('role', 'admin')->count();
        $totalGuru = User::where('role', 'guru')->count();
        $totalSiswa = User::where('role', 'siswa')->count();

        return view('admin.users.index', compact(
            'users',
            'totalAdmin',
            'totalGuru',
            'totalSiswa'
        ));
    }

    // FORM TAMBAH AKUN
    public function create()
    {
        $gurus = User::where('role', 'guru')
            ->orderBy('name')
            ->get();

        return view('admin.users.create', compact('gurus'));
    }

    // MENYIMPAN AKUN BARU
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,guru,siswa',
            'guru_id' => [
                'nullable',
                'required_if:role,siswa',
                Rule::exists('users', 'id')
                    ->where(fn ($query) => $query->where('role', 'guru')),
            ],
                    'internship_start' => ['nullable', 'date'],
                    'internship_end' => ['nullable', 'date', 'after_or_equal:internship_start'],
        ]);

        $user = new User();

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->password = $data['password'];
        $user->role = $data['role'];

        $user->guru_id = $data['role'] === 'siswa'
            ? $data['guru_id']
            : null;
        $user->internship_start = $data['role'] === 'siswa' ? ($data['internship_start'] ?? null) : null;
        $user->internship_end = $data['role'] === 'siswa' ? ($data['internship_end'] ?? null) : null;

        $user->save();

        return redirect()->route('admin.users.index')
            ->with('success', 'Akun berhasil dibuat!');
    }

    // FORM EDIT AKUN
    public function edit($id)
    {
        $user = User::findOrFail($id);

        $gurus = User::where('role', 'guru')
            ->orderBy('name')
            ->get();

        return view('admin.users.edit', compact('user', 'gurus'));
    }

    // MEMPERBARUI AKUN
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'password' => 'nullable|string|min:8',
            'role' => 'required|in:admin,guru,siswa',
            'guru_id' => [
                'nullable',
                'required_if:role,siswa',
                Rule::exists('users', 'id')
                    ->where(fn ($query) => $query->where('role', 'guru')),
            ],
                    'internship_start' => ['nullable', 'date'],
                    'internship_end' => ['nullable', 'date', 'after_or_equal:internship_start'],
        ]);

        // Admin tidak boleh mengubah rolenya sendiri
        if (
            $user->id === auth()->id() &&
            $data['role'] !== 'admin'
        ) {
            return back()
                ->withErrors([
                    'role' => 'Anda tidak dapat mengubah role akun sendiri.',
                ])
                ->withInput();
        }

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->role = $data['role'];

        // Password hanya diubah jika diisi
        if (!empty($data['password'])) {
            $user->password = $data['password'];
        }

        $user->guru_id = $data['role'] === 'siswa'
            ? $data['guru_id']
            : null;
        $user->internship_start = $data['role'] === 'siswa' ? ($data['internship_start'] ?? null) : null;
        $user->internship_end = $data['role'] === 'siswa' ? ($data['internship_end'] ?? null) : null;

        $user->save();

        return redirect()->route('admin.users.index')
            ->with('success', 'Akun berhasil diperbarui!');
    }

    // MENGHAPUS AKUN
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        // Admin tidak boleh menghapus akun sendiri
        if ($user->id === auth()->id()) {
            return back()->with(
                'error',
                'Anda tidak dapat menghapus akun sendiri.'
            );
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Akun berhasil dihapus!');
    }
}
