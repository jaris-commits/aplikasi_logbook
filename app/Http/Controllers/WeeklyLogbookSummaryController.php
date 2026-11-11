<?php

namespace App\Http\Controllers;

use App\Models\Logbook;
use App\Models\User;
use App\Services\WeeklyLogbookSummaryService;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Throwable;

class WeeklyLogbookSummaryController extends Controller
{
    public function __invoke(Request $request, WeeklyLogbookSummaryService $summaryService): RedirectResponse
    {
        $data = $request->validate([
            'student_id' => ['required', 'integer', 'exists:users,id'],
            'week_start' => ['required', 'date'],
            'privacy_notice_accepted' => ['accepted'],
        ]);

        $teacher = $request->user();
        $student = User::whereKey($data['student_id'])
            ->where('role', 'siswa')
            ->where('guru_id', $teacher->id)
            ->first();

        abort_unless($student, 403, 'Siswa tersebut bukan siswa bimbingan Anda.');

        $start = CarbonImmutable::parse($data['week_start'])->startOfDay();
        $end = $start->addDays(6)->endOfDay();
        $logbooks = Logbook::query()
            ->where('user_id', $student->id)
            ->whereBetween('tanggal', [$start->toDateString(), $end->toDateString()])
            ->orderBy('tanggal')
            ->get(['tanggal', 'aktivitas', 'status']);

        if ($logbooks->isEmpty()) {
            return back()
                ->withInput()
                ->with('error', 'Tidak ada logbook siswa pada rentang tanggal tersebut.');
        }

        try {
            $summary = $summaryService->summarize($logbooks->map(fn (Logbook $logbook) => [
                'tanggal' => CarbonImmutable::parse($logbook->tanggal)->format('Y-m-d'),
                'aktivitas' => $logbook->aktivitas,
                'status' => $logbook->status,
            ])->all());
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->with('error', 'Ringkasan AI belum tersedia. Periksa konfigurasi Gemini atau coba lagi nanti.');
        }

        return back()->with([
            'weekly_summary' => $summary,
            'weekly_summary_student' => $student->name,
            'weekly_summary_period' => $start->format('d/m/Y').' - '.$end->format('d/m/Y'),
        ]);
    }
}