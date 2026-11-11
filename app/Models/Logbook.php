<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Logbook extends Model
{
    protected $fillable = [
        'user_id',
        'tanggal',
        'aktivitas',
        'foto',
        'status',
        'catatan_guru',
    ];

    // Relasi: Logbook dimiliki oleh satu User
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
