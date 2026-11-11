<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'internship_start',
        'internship_end',
        'telegram_chat_id',
        'telegram_consent_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'internship_start' => 'date',
            'internship_end' => 'date',
            'telegram_consent_at' => 'datetime',
        ];
    }

    // Relasi: Siswa memiliki satu Guru pembimbing
    public function guruPembimbing(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guru_id');
    }

    // Relasi: Guru memiliki banyak Siswa bimbingan
    public function siswaBimbingan(): HasMany
    {
        return $this->hasMany(User::class, 'guru_id')
            ->where('role', 'siswa');
    }

    // Relasi: User memiliki banyak Logbook
    public function logbooks(): HasMany
    {
        return $this->hasMany(Logbook::class);
    }

    // Relasi: User memiliki banyak Nilai
    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function projects(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Project::class)->withTimestamps();
    }

    public function reportSignatures(): HasMany
    {
        return $this->hasMany(ReportSignature::class);
    }

    public function internshipProgress(): array
    {
        if (! $this->internship_start || ! $this->internship_end) {
            return ['configured' => false, 'percent' => 0, 'elapsed_days' => 0, 'total_days' => 0];
        }

        $start = $this->internship_start->copy()->startOfDay();
        $end = $this->internship_end->copy()->startOfDay();

        if ($end->lt($start)) {
            return ['configured' => false, 'percent' => 0, 'elapsed_days' => 0, 'total_days' => 0];
        }

        $today = now()->startOfDay()->min($end);
        $totalDays = 0;
        $elapsedDays = 0;

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            if ($date->isWeekday()) {
                $totalDays++;
                if ($date->lte($today)) {
                    $elapsedDays++;
                }
            }
        }

        return [
            'configured' => $totalDays > 0,
            'percent' => $totalDays > 0 ? (int) round(($elapsedDays / $totalDays) * 100) : 0,
            'elapsed_days' => $elapsedDays,
            'total_days' => $totalDays,
        ];
    }
}
