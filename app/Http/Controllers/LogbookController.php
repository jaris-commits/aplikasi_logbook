<?php

namespace App\Http\Controllers;

use App\Models\Logbook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogbookController extends Controller
{
    // READ: Menampilkan daftar logbook sesuai role user yang login
    public function index()
    {
        $user = Auth::user();

        if ($user->role == 'siswa') {
            // Siswa hanya boleh melihat catatan logbook miliknya sendiri
            $logbooks = Logbook::where('user_id', $user->id)->orderBy('tanggal', 'desc')->get();
        } else {
            // Admin dan Guru bisa melihat semua logbook yang masuk dari seluruh siswa
            $logbooks = Logbook::with('user')->orderBy('tanggal', 'desc')->get(); 
        }

        return view('dashboard', compact('logbooks'));
    }

    // CREATE: Proses menyimpan catatan logbook baru oleh Siswa
    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'aktivitas' => 'required|string',
        ]);

        Logbook::create([
            'user_id' => Auth::id(),
            'tanggal' => $request->tanggal,
            'aktivitas' => $request->aktivitas,
            'status' => 'pending', // Default status awal
        ]);

        return redirect()->route('dashboard')->with('success', 'Logbook berhasil ditambahkan!');
    }

    // UPDATE: Guru atau Admin mengubah status verifikasi dan memberikan catatan masukan
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:disetujui,revisi',
            'catatan_guru' => 'nullable|string',
        ]);

        $logbook = Logbook::findOrFail($id);
        $logbook->update([
            'status' => $request->status,
            'catatan_guru' => $request->catatan_guru,
        ]);

        return redirect()->route('dashboard')->with('success', 'Status logbook berhasil diperbarui!');
    }

    // DELETE: Menghapus catatan logbook (Hanya bisa dilakukan Siswa jika statusnya masih pending)
    public function destroy($id)
    {
        $logbook = Logbook::findOrFail($id);

        if ($logbook->status !== 'pending') {
            return redirect()->route('dashboard')->with('error', 'Logbook yang sudah dinilai tidak boleh dihapus!');
        }

        $logbook->delete();
        return redirect()->route('dashboard')->with('success', 'Logbook berhasil dihapus!');
    }
}