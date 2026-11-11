<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_project_for_students(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $studentA = User::factory()->create(['role' => 'siswa']);
        $studentB = User::factory()->create(['role' => 'siswa']);

        $this->actingAs($admin)->post(route('projects.store'), [
            'name' => 'Aplikasi Inventaris',
            'description' => 'Proyek tim PKL',
            'student_ids' => [$studentA->id, $studentB->id],
        ])->assertRedirect();

        $project = Project::with('students')->firstOrFail();
        $this->assertCount(2, $project->students);
    }

    public function test_teacher_cannot_add_student_outside_their_supervision(): void
    {
        $teacher = User::factory()->create(['role' => 'guru']);
        $student = User::factory()->create(['role' => 'siswa']);

        $this->actingAs($teacher)->post(route('projects.store'), [
            'name' => 'Proyek tidak sah',
            'student_ids' => [$student->id],
        ])->assertForbidden();

        $this->assertDatabaseCount('projects', 0);
    }
}