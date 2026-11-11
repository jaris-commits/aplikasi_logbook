<?php

namespace Tests\Feature;

use App\Models\Logbook;
use App\Models\ReportSignature;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReportSignatureTest extends TestCase
{
    use RefreshDatabase;

    private function pngDataUrl(): string
    {
        return 'data:image/svg+xml;base64,'.base64_encode(
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 180"><path d="M10,90 L100,40 L180,100"/></svg>'
        );
    }

    public function test_teacher_can_sign_current_logbook_report_after_password_confirmation(): void
    {
        Storage::fake('private');
        $teacher = User::factory()->create(['role' => 'guru', 'password' => 'password']);
        $student = User::factory()->create(['role' => 'siswa', 'guru_id' => $teacher->id]);
        Logbook::create([
            'user_id' => $student->id,
            'tanggal' => today(),
            'aktivitas' => 'Mengerjakan rancangan aplikasi.',
            'status' => 'disetujui',
        ]);

        $this->actingAs($teacher)->post(route('report.signature'), [
            'report_type' => 'logbook',
            'signature' => $this->pngDataUrl(),
            'password' => 'password',
        ])->assertRedirect();

        $signature = ReportSignature::firstOrFail();
        Storage::disk('private')->assertExists($signature->signature_path);
        $this->assertSame(64, strlen($signature->document_hash));
        $this->actingAs($teacher)->get(route('report.logbook.pdf'))->assertOk();
    }

    public function test_invalid_password_does_not_store_signature(): void
    {
        Storage::fake('private');
        $teacher = User::factory()->create(['role' => 'guru']);
        $student = User::factory()->create(['role' => 'siswa', 'guru_id' => $teacher->id]);
        Logbook::create([
            'user_id' => $student->id,
            'tanggal' => today(),
            'aktivitas' => 'Membuat laporan.',
            'status' => 'pending',
        ]);

        $this->actingAs($teacher)->post(route('report.signature'), [
            'report_type' => 'logbook',
            'signature' => $this->pngDataUrl(),
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('password');

        $this->assertDatabaseCount('report_signatures', 0);
    }
}