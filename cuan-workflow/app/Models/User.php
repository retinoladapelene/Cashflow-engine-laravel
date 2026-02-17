<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'username',
        'whatsapp',
        'role',
        'is_premium',
        'is_banned',
        'firebase_uid',
        'google_id',
        'auth_provider',
        'avatar',
        'device_type',
        'browser',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
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
            'last_login_at' => 'datetime',
            'is_premium' => 'boolean',
            'is_banned' => 'boolean',
        ];
    }

    // Relationships
    public function businessProfile()
    {
        return $this->hasOne(BusinessProfile::class);
    }

    public function adArsenals()
    {
        return $this->hasMany(AdArsenal::class, 'created_by');
    }

    public function roadmapProgress()
    {
        return $this->hasMany(RoadmapProgress::class);
    }
}
