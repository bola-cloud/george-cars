<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Device extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'serial',
        'meta',
        'ip',
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

    protected static function booted()
    {
        static::creating(function ($device) {
            if (empty($device->serial)) {
                $device->serial = static::generateUniqueSerial(14);
            }
        });
    }

    public static function generateUniqueSerial(int $length = 14): string
    {
        $tries = 0;
        do {
            // Uppercase letters and digits only
            $serial = Str::upper(Str::random($length));
            $exists = static::where('serial', $serial)->exists();
            $tries++;
        } while ($exists && $tries < 10);

        if ($exists) {
            throw new \RuntimeException('Unable to generate unique device serial');
        }

        return $serial;
    }
}
