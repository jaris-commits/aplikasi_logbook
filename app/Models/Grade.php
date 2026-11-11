<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Grade extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nilai',
        'semester',
        'catatan',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
