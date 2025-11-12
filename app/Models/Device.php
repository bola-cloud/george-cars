<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Device extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'serial',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    /**
     * Device belongs to a user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
