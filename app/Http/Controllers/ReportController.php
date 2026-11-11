<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use App\Models\Logbook;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function logbookPdf()
    {
        $user = Auth::user();

        $logbooks = Logbook::with('user')
            ->when($user->role === 'siswa', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->when($user->role === 'guru', function ($query) use ($user) {
                $query->whereHas('user', function ($studentQuery) use ($user) {
                    $studentQuery->where('guru_id', $user->id)
                        ->where('role', 'siswa');
                });
            })
            ->orderBy('tanggal', 'desc')
            ->get();

        $pdf = Pdf::loadView('reports.logbook-pdf', [
            'user' => $user,
            'logbooks' => $logbooks,
        ]);

        return $pdf->download('rekap-logbook-' . strtolower(str_replace(' ', '-', $user->name)) . '.pdf');
    }

    public function gradePdf()
    {
        $user = Auth::user();

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
            ->orderBy('semester', 'asc')
            ->get();

        $pdf = Pdf::loadView('reports.grade-pdf', [
            'user' => $user,
            'grades' => $grades,
        ]);

        return $pdf->download('rekap-nilai-' . strtolower(str_replace(' ', '-', $user->name)) . '.pdf');
    }
}
