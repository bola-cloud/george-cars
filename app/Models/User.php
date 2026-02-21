<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Device;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'is_admin',
        'onesignal',
    ];

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    /**
     * Get the devices that belong to the user.
     */
    public function devices(): HasMany
    {
        return $this->hasMany(Device::class);
    }

    /**
     * Users that this user has shared with (children).
     */
    public function sharedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_shares', 'owner_id', 'user_id')->withTimestamps()->withPivot('meta');
    }

    /**
     * Owners that have shared with this user.
     */
    public function sharedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_shares', 'user_id', 'owner_id')->withTimestamps();
    }

    /**
     * Devices that are shared to this user by owners.
     * Returns an Eloquent query (Device builder) so callers may chain/with() as needed.
     */
    public function sharedDevices()
    {
        $ownerIds = $this->sharedBy()->pluck('users.id')->toArray();
        return Device::whereIn('user_id', $ownerIds);
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'onesignal' => 'array',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];
}
