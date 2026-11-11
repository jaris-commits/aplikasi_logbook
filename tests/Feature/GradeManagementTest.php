<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class GradeManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_grade_for_student(): void
    {
        Notification::fake();
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $student = User::factory()->create([
            'role' => 'siswa',
        ]);

        $response = $this->actingAs($admin)
            ->post('/grades', [
                'user_id' => $student->id,
                'nilai' => 88,
                'semester' => '2026/2027 Ganjil',
                'catatan' => 'Sangat baik dalam kerja sama tim.',
            ]);

        $response->assertRedirect('/dashboard');

        $this->assertDatabaseHas('grades', [
            'user_id' => $student->id,
            'nilai' => 88,
            'semester' => '2026/2027 Ganjil',
            'catatan' => 'Sangat baik dalam kerja sama tim.',
        ]);

        Notification::assertSentTo($student, \App\Notifications\LogbookWorkflowNotification::class);
    }

    public function test_teacher_can_only_create_grade_for_assigned_student(): void
    {
        $teacher = User::factory()->create(['role' => 'guru']);
        $assignedStudent = User::factory()->create([
            'role' => 'siswa',
            'guru_id' => $teacher->id,
        ]);
        $otherStudent = User::factory()->create(['role' => 'siswa']);

        $this->actingAs($teacher)
            ->post('/grades', [
                'user_id' => $assignedStudent->id,
                'nilai' => 90,
                'semester' => '2026/2027 Ganjil',
            ])
            ->assertRedirect('/dashboard');

        $this->actingAs($teacher)
            ->post('/grades', [
                'user_id' => $otherStudent->id,
                'nilai' => 70,
                'semester' => '2026/2027 Ganjil',
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('grades', [
            'user_id' => $assignedStudent->id,
            'nilai' => 90,
        ]);
        $this->assertDatabaseMissing('grades', [
            'user_id' => $otherStudent->id,
        ]);
    }
}
