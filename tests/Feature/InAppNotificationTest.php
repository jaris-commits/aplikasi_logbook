<?php

namespace Tests\Feature;

use App\Models\Logbook;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class InAppNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_logbook_notifies_assigned_teacher(): void
    {
        Notification::fake();
        $teacher = User::factory()->create(['role' => 'guru']);
        $student = User::factory()->create(['role' => 'siswa', 'guru_id' => $teacher->id]);

        $this->actingAs($student)->post(route('logbook.store'), [
            'tanggal' => today()->toDateString(),
            'aktivitas' => 'Menyusun rancangan database.',
        ])->assertRedirect();

        Notification::assertSentTo($teacher, \App\Notifications\LogbookWorkflowNotification::class);
    }

    public function test_revision_notifies_student_and_notifications_are_private(): void
    {
        Notification::fake();
        $teacher = User::factory()->create(['role' => 'guru']);
        $student = User::factory()->create(['role' => 'siswa', 'guru_id' => $teacher->id]);
        $logbook = Logbook::create([
            'user_id' => $student->id,
            'tanggal' => today(),
            'aktivitas' => 'Membuat laporan.',
            'status' => 'pending',
        ]);

        $this->actingAs($teacher)->put(route('logbook.status', $logbook), [
            'status' => 'revisi',
            'catatan_guru' => 'Lengkapi hasil kegiatan.',
        ])->assertRedirect();

        Notification::assertSentTo($student, \App\Notifications\LogbookWorkflowNotification::class);
    }
}