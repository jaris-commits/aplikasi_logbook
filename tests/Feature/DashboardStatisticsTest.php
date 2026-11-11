<?php

namespace Tests\Feature;

use App\Models\Logbook;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardStatisticsTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_teacher_dashboard_statistics_only_include_assigned_students(): void
    {
        Carbon::setTestNow('2026-11-11 12:00:00');

        $teacher = User::factory()->create(['role' => 'guru']);
        $assignedStudent = User::factory()->create([
            'role' => 'siswa',
            'guru_id' => $teacher->id,
        ]);
        $unassignedStudent = User::factory()->create(['role' => 'siswa']);

        Logbook::create([
            'user_id' => $assignedStudent->id,
            'tanggal' => '2026-11-11',
            'aktivitas' => 'Catatan siswa bimbingan.',
            'status' => 'pending',
        ]);
        Logbook::create([
            'user_id' => $unassignedStudent->id,
            'tanggal' => '2026-11-11',
            'aktivitas' => 'Catatan siswa lain yang tidak boleh terlihat.',
            'status' => 'disetujui',
        ]);

        $this->actingAs($teacher)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Statistik bulan ini')
            ->assertSee('Aktivitas logbook harian')
            ->assertSee('Catatan siswa bimbingan.')
            ->assertDontSee('Catatan siswa lain yang tidak boleh terlihat.');
    }
}