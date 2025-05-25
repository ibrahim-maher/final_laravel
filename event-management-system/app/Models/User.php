<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
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
        ];
    }

    /**
     * Role constants
     */
    const ROLE_ADMIN = 'admin';
    const ROLE_EVENT_MANAGER = 'event_manager';
    const ROLE_USHER = 'usher';
    const ROLE_USER = 'user';

    /**
     * Check if user is admin
     */
    public function isAdmin()
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * Check if user is event manager
     */
    public function isEventManager()
    {
        return $this->role === self::ROLE_EVENT_MANAGER;
    }

    /**
     * Check if user is usher
     */
    public function isUsher()
    {
        return $this->role === self::ROLE_USHER;
    }

    /**
     * Check if user can manage events (Admin or Event Manager)
     */
    public function canManageEvents()
    {
        return $this->isAdmin() || $this->isEventManager();
    }

    /**
     * Check if user can manage venues (Admin only)
     */
    public function canManageVenues()
    {
        return $this->isAdmin();
    }

    /**
     * Check if user can manage users (Admin only)
     */
    public function canManageUsers()
    {
        return $this->isAdmin();
    }

    /**
     * Get role display name
     */
    public function getRoleDisplayName()
    {
        return match($this->role) {
            self::ROLE_ADMIN => 'Administrator',
            self::ROLE_EVENT_MANAGER => 'Event Manager',
            self::ROLE_USHER => 'Usher',
            default => 'User',
        };
    }

    /**
     * Relationships
     */
    public function registrations()
    {
        return $this->hasMany(Registration::class);
    }

    public function assignedEvents()
    {
        return $this->belongsToMany(Event::class, 'user_event');
    }
}