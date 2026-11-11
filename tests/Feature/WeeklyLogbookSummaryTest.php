<?php

namespace Tests\Feature;

use App\Models\Logbook;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WeeklyLogbookSummaryTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_can_summarize_an_assigned_students_weekly_logbooks(): void
    {
        config(['services.gemini.api_key' => 'test-api-key']);
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [[
                    'content' => ['parts' => [['text' => "- Menyusun laporan\n- Menguji aplikasi\n- Memperbaiki bug"]]],
                ]],
            ]),
        ]);

        $teacher = User::factory()->create(['role' => 'guru']);
        $student = User::factory()->create([
            'role' => 'siswa',
            'guru_id' => $teacher->id,
        ]);
        Logbook::create([
            'user_id' => $student->id,
            'tanggal' => '2026-11-09',
            'aktivitas' => 'Menguji aplikasi dan memperbaiki bug.',
            'status' => 'pending',
        ]);

        $this->actingAs($teacher)
            ->post(route('report.weekly-summary'), [
                'student_id' => $student->id,
                'week_start' => '2026-11-09',
                'privacy_notice_accepted' => '1',
            ])
            ->assertRedirect()
            ->assertSessionHas('weekly_summary', "- Menyusun laporan\n- Menguji aplikasi\n- Memperbaiki bug")
            ->assertSessionHas('weekly_summary_student', $student->name);

        Http::assertSent(fn ($request) => $request->hasHeader('x-goog-api-key', 'test-api-key')
            && str_contains($request['contents'][0]['parts'][0]['text'], 'Menguji aplikasi dan memperbaiki bug.')
            && ! str_contains($request['contents'][0]['parts'][0]['text'], $student->email));
    }

    public function test_teacher_cannot_summarize_a_student_outside_their_assignment(): void
    {
        Http::fake();

        $teacher = User::factory()->create(['role' => 'guru']);
        $otherTeacher = User::factory()->create(['role' => 'guru']);
        $student = User::factory()->create([
            'role' => 'siswa',
            'guru_id' => $otherTeacher->id,
        ]);

        $this->actingAs($teacher)
            ->post(route('report.weekly-summary'), [
                'student_id' => $student->id,
                'week_start' => '2026-11-09',
                'privacy_notice_accepted' => '1',
            ])
            ->assertForbidden();

        Http::assertNothingSent();
    }

    public function test_students_cannot_access_the_ai_summary_endpoint(): void
    {
        Http::fake();

        $student = User::factory()->create(['role' => 'siswa']);

        $this->actingAs($student)
            ->post(route('report.weekly-summary'), [
                'student_id' => $student->id,
                'week_start' => '2026-11-09',
            ])
            ->assertForbidden();

        Http::assertNothingSent();
    }

    public function test_personal_data_patterns_are_redacted_before_sending_to_gemini(): void
    {
        config(['services.gemini.api_key' => 'test-api-key']);
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [[
                    'content' => ['parts' => [['text' => '- Menyelesaikan tugas']]],
                ]],
            ]),
        ]);

        $teacher = User::factory()->create(['role' => 'guru']);
        $student = User::factory()->create([
            'role' => 'siswa',
            'guru_id' => $teacher->id,
        ]);
        Logbook::create([
            'user_id' => $student->id,
            'tanggal' => '2026-11-09',
            'aktivitas' => 'Menghubungi peserta di siswa@example.com, 081234567890, NIK 3171234567890123.',
            'status' => 'pending',
        ]);

        $this->actingAs($teacher)
            ->post(route('report.weekly-summary'), [
                'student_id' => $student->id,
                'week_start' => '2026-11-09',
                'privacy_notice_accepted' => '1',
            ])
            ->assertRedirect()
            ->assertSessionHas('weekly_summary');

        Http::assertSent(function ($request) {
            $prompt = $request['contents'][0]['parts'][0]['text'];

            return str_contains($prompt, '[EMAIL DIHAPUS]')
                && str_contains($prompt, '[NOMOR HP DIHAPUS]')
                && str_contains($prompt, '[NIK DIHAPUS]')
                && ! str_contains($prompt, 'siswa@example.com')
                && ! str_contains($prompt, '081234567890')
                && ! str_contains($prompt, '3171234567890123');
        });
    }

    public function test_teacher_must_accept_privacy_notice_before_calling_gemini(): void
    {
        config(['services.gemini.api_key' => 'test-api-key']);
        Http::fake();

        $teacher = User::factory()->create(['role' => 'guru']);
        $student = User::factory()->create([
            'role' => 'siswa',
            'guru_id' => $teacher->id,
        ]);
        Logbook::create([
            'user_id' => $student->id,
            'tanggal' => '2026-11-09',
            'aktivitas' => 'Menyusun laporan mingguan.',
            'status' => 'pending',
        ]);

        $this->actingAs($teacher)
            ->from('/dashboard')
            ->post(route('report.weekly-summary'), [
                'student_id' => $student->id,
                'week_start' => '2026-11-09',
            ])
            ->assertRedirect('/dashboard')
            ->assertSessionHasErrors('privacy_notice_accepted');

        Http::assertNothingSent();
    }
}