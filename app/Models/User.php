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
}
