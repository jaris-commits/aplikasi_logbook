<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $fillable = [
        'user_id', 'tanggal', 'mode', 'check_in_at', 'check_in_photo', 'check_in_mime', 'check_out_at',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'check_in_at' => 'datetime',
            'check_out_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}