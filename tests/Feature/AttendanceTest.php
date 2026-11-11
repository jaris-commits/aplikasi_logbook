<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_check_in_and_out_once_per_day(): void
    {
        Storage::fake('private');
        $student = User::factory()->create(['role' => 'siswa']);
        $photo = UploadedFile::fake()->createWithContent(
            'selfie.png',
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/lS8AAAAASUVORK5CYII=', true)
        );

        $this->actingAs($student)->post(route('attendance.check-in'), [
            'mode' => 'WFH',
            'photo' => $photo,
        ])->assertRedirect();

        $attendance = Attendance::where('user_id', $student->id)->firstOrFail();
        Storage::disk('private')->assertExists($attendance->check_in_photo);

        $this->actingAs($student)->post(route('attendance.check-out'))->assertRedirect();
        $this->assertNotNull($attendance->fresh()->check_out_at);

        $this->actingAs($student)->post(route('attendance.check-in'), [
            'mode' => 'WFO',
            'photo' => UploadedFile::fake()->createWithContent('second.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/lS8AAAAASUVORK5CYII=', true)),
        ])->assertSessionHas('error');
    }

    public function test_teacher_only_sees_assigned_students_attendance(): void
    {
        $teacher = User::factory()->create(['role' => 'guru']);
        $assigned = User::factory()->create(['role' => 'siswa', 'guru_id' => $teacher->id]);
        $other = User::factory()->create(['role' => 'siswa']);
        Attendance::create(['user_id' => $assigned->id, 'tanggal' => today(), 'mode' => 'WFO']);
        Attendance::create(['user_id' => $other->id, 'tanggal' => today(), 'mode' => 'WFH']);

        $this->actingAs($teacher)->get(route('attendance.index'))
            ->assertOk()->assertSee($assigned->name)->assertDontSee($other->name);
    }
}