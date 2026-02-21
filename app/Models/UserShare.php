<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserShare extends Model
{
    protected $table = 'user_shares';

    protected $fillable = [
        'owner_id',
        'user_id',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
