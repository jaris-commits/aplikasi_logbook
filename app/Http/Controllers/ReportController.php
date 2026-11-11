<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use App\Models\Logbook;
use App\Models\ReportSignature;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Http\RedirectResponse;

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
            ->orderByDesc('tanggal')->orderBy('id')
            ->get();

        $signatureHash = $this->reportHash('logbook', $user->id, $logbooks, [
            'id', 'user_id', 'tanggal', 'aktivitas', 'status', 'catatan_guru',
        ]);
        $signature = $user->reportSignatures()
            ->where('report_type', 'logbook')
            ->where('document_hash', $signatureHash)
            ->latest('signed_at')
            ->first();

        $signatureSvg = $signature && Storage::disk('private')->exists($signature->signature_path)
            ? Storage::disk('private')->get($signature->signature_path)
            : null;

        $pdf = Pdf::loadView('reports.logbook-pdf', [
            'user' => $user,
            'logbooks' => $logbooks,
            'signature' => $signature,
            'signatureSvg' => $signatureSvg,
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
            ->orderBy('semester')->orderBy('id')
            ->get();

        $signatureHash = $this->reportHash('grades', $user->id, $grades, [
            'id', 'user_id', 'semester', 'nilai', 'catatan',
        ]);
        $signature = $user->reportSignatures()
            ->where('report_type', 'grades')
            ->where('document_hash', $signatureHash)
            ->latest('signed_at')
            ->first();

        $signatureSvg = $signature && Storage::disk('private')->exists($signature->signature_path)
            ? Storage::disk('private')->get($signature->signature_path)
            : null;

        $pdf = Pdf::loadView('reports.grade-pdf', [
            'user' => $user,
            'grades' => $grades,
            'signature' => $signature,
            'signatureSvg' => $signatureSvg,
        ]);

        return $pdf->download('rekap-nilai-' . strtolower(str_replace(' ', '-', $user->name)) . '.pdf');
    }

    public function saveSignature(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'report_type' => ['required', 'in:logbook,grades'],
            'signature' => ['required', 'string', 'max:500000'],
            'password' => ['required', 'current_password'],
        ]);

        if (! preg_match('/^data:image\/svg\+xml;base64,([A-Za-z0-9+\/=]+)$/', $data['signature'], $matches)) {
            return back()->withErrors(['signature' => 'Format tanda tangan tidak valid.']);
        }

        $submittedSvg = base64_decode($matches[1], true);
        if ($submittedSvg === false || strlen($submittedSvg) > 250_000) {
            return back()->withErrors(['signature' => 'Gambar tanda tangan tidak valid atau terlalu besar.']);
        }

        preg_match_all('/<path d="([MmLl\d.,\s-]+)"\/>/', $submittedSvg, $pathMatches);
        if (empty($pathMatches[1]) || count($pathMatches[1]) > 50) {
            return back()->withErrors(['signature' => 'Goresan tanda tangan tidak valid.']);
        }
        $paths = collect($pathMatches[1])->map(fn (string $path) => '<path d="'.$path.'"/>')->implode('');
        $safeSvg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 180" width="600" height="180"><g fill="none" stroke="#0f172a" stroke-width="4" stroke-linecap="round" stroke-linejoin="round">'.$paths.'</g></svg>';

        $user = $request->user();
        abort_unless(in_array($user->role, ['admin', 'guru']), 403);

        $reportRows = $data['report_type'] === 'logbook'
            ? Logbook::query()->when($user->role === 'siswa', fn ($query) => $query->where('user_id', $user->id))
                ->when($user->role === 'guru', fn ($query) => $query->whereHas('user', fn ($students) => $students->where('guru_id', $user->id)))
                ->orderByDesc('tanggal')->orderBy('id')->get(['id', 'user_id', 'tanggal', 'aktivitas', 'status', 'catatan_guru'])
            : \App\Models\Grade::query()->when($user->role === 'siswa', fn ($query) => $query->where('user_id', $user->id))
                ->when($user->role === 'guru', fn ($query) => $query->whereHas('user', fn ($students) => $students->where('guru_id', $user->id)))
                ->orderBy('semester')->orderBy('id')->get(['id', 'user_id', 'semester', 'nilai', 'catatan']);

        abort_if($reportRows->isEmpty(), 422, 'Tidak ada data laporan untuk ditandatangani.');

        $fields = $data['report_type'] === 'logbook'
            ? ['id', 'user_id', 'tanggal', 'aktivitas', 'status', 'catatan_guru']
            : ['id', 'user_id', 'semester', 'nilai', 'catatan'];
        $hash = $this->reportHash($data['report_type'], $user->id, $reportRows, $fields);
        $path = 'report-signatures/'.$user->id.'/'.Str::uuid().'.svg';
        Storage::disk('private')->put($path, $safeSvg);

        $user->reportSignatures()->create([
            'report_type' => $data['report_type'],
            'signature_path' => $path,
            'document_hash' => $hash,
            'signed_at' => now(),
        ]);

        return back()->with('success', 'Tanda tangan visual tersimpan untuk data laporan saat ini.');
    }

    private function reportHash(string $type, int $userId, $rows, array $fields): string
    {
        $payload = $rows->map(function ($row) use ($fields) {
            return collect($fields)->mapWithKeys(function (string $field) use ($row) {
                $value = $row->getAttribute($field);
                if ($value instanceof \DateTimeInterface) {
                    $value = $value->format('Y-m-d');
                }

                return [$field => $value];
            });
        });

        $rows = $payload->all();
        usort($rows, function (array $left, array $right) use ($type) {
            $primary = $type === 'logbook' ? 'tanggal' : 'semester';
            $direction = $type === 'logbook' ? -1 : 1;

            return $direction * (($left[$primary] ?? '') <=> ($right[$primary] ?? ''))
                ?: (($left['id'] ?? 0) <=> ($right['id'] ?? 0));
        });

        return hash('sha256', $type.'|'.$userId.'|'.json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
    }
}
