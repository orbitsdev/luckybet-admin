<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Bet;
use App\Models\Claim;
use App\Models\Teller;
use App\Models\Commission;

use App\Models\LowWinNumber;
use Laravel\Sanctum\HasApiTokens;
use Spatie\MediaLibrary\HasMedia;
use Laravel\Jetstream\HasProfilePhoto;
use Illuminate\Notifications\Notifiable;
use Spatie\MediaLibrary\InteractsWithMedia;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements HasMedia
{
    use HasApiTokens;

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;
     use InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     *
     *
     */
    protected $fillable = [
        'username',
        'password',
        'name',
        'email',
        'phone',
        'role',
        'profile_photo_path',
        'email_verified_at',
        'current_team_id',
        'remember_token',
        'is_active',
        'location_id',
        'coordinator_id'
    ];


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
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }
    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function bets()
    {
        return $this->hasMany(Bet::class, 'teller_id');
    }


    public function commission()
    {
        return $this->hasOne(Commission::class, 'teller_id');
    }

    

    /**
     * Get all low win numbers set by this user
     */
    public function lowWinNumbers()
    {
        return $this->hasMany(LowWinNumber::class);
    }

    public function coordinator()
    {
        return $this->belongsTo(User::class, 'coordinator_id')->where('role', 'coordinator');
    }

    public function tellers()
    {
        return $this->hasMany(User::class, 'coordinator_id')->where('role', 'teller');
    }

    /**
     * Get the profile photo URL attribute or a default avatar if none exists.
     *
     * @return string
     */
    public function getProfilePhotoUrlAttribute()
    {
        if ($this->profile_photo_path) {
            return url('storage/' . $this->profile_photo_path);
        }

        // Return a free avatar URL based on the user's name
        $name = urlencode($this->name);
        return "https://ui-avatars.com/api/?name={$name}&color=7F9CF5&background=EBF4FF";
    }
}
