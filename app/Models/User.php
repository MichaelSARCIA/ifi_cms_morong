<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'title',
        'email',
        'password',
        'role',
        'profile_pic',
        'status',
        'modules',
        'last_seen_at',
        'working_days',
        'working_hours',
        'max_services_per_day',
        'session_id',
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
            'modules' => 'array',
            'last_seen_at' => 'datetime',
            'working_days' => 'array',
            'working_hours' => 'array',
        ];
    }

    /**
     * Cache for role definition
     */
    protected $roleDefinition = null;

    /**
     * Check if the user has access to a specific module.
     * 
     * @param string $module
     * @return bool
     */
    public function hasModule(string $module): bool
    {
        // Admins have access to everything by default
        if ($this->role === 'Admin') {
            return true;
        }

        // Check against the centralized Role definition first (with caching)
        if ($this->roleDefinition === null) {
            $this->roleDefinition = \App\Models\Role::where('name', $this->role)->first();
        }

        if ($this->roleDefinition && !empty($this->roleDefinition->modules)) {
            return in_array($module, $this->roleDefinition->modules);
        }

        // Fallback to user-specific modules (legacy)
        if (empty($this->modules)) {
            return false;
        }

        return in_array($module, $this->modules);
    }


    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        // Use the custom queued notification to ensure fast UI response
        $this->notify(new \App\Notifications\QueuedResetPassword($token));
    }
    public function getInitialsAttribute(): string
    {
        $name = trim($this->name);
        $words = explode(' ', $name);

        if (count($words) >= 2) {
            return strtoupper(substr($words[0], 0, 1) . substr(end($words), 0, 1));
        }

        return strtoupper(substr($name, 0, 2));
    }

    public function getIsOnlineAttribute(): bool
    {
        return $this->last_seen_at && $this->last_seen_at->diffInMinutes(now()) < 5;
    }
}
