<?php

namespace Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InternshipProgressTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_progress_counts_weekdays_and_caps_at_internship_end(): void
    {
        Carbon::setTestNow('2026-11-11');
        $student = User::factory()->create([
            'role' => 'siswa',
            'internship_start' => '2026-11-09',
            'internship_end' => '2026-11-13',
        ]);

        $this->assertSame([
            'configured' => true,
            'percent' => 60,
            'elapsed_days' => 3,
            'total_days' => 5,
        ], $student->internshipProgress());
    }
}