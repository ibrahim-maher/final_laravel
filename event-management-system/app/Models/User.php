<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'email',
        'password',
        'role',
        'phone_number',
        'title',
        'country',
        'company',
        'is_active',
        'notes',
        'email_verified_at'
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
            'is_active' => 'boolean',
        ];
    }

    /**
     * Role constants
     */
    const ROLE_ADMIN = 'ADMIN';
    const ROLE_EVENT_MANAGER = 'EVENT_MANAGER';
    const ROLE_USHER = 'USHER';
    const ROLE_VISITOR = 'VISITOR';

    /**
     * Get available roles
     */
    public static function getRoles()
    {
        return [
            self::ROLE_ADMIN => 'Administrator',
            self::ROLE_EVENT_MANAGER => 'Event Manager',
            self::ROLE_USHER => 'Usher',
            self::ROLE_VISITOR => 'Visitor',
        ];
    }

    /**
     * Check if user has given role or any of multiple roles.
     *
     * @param  string|array  $roles
     * @return bool
     */
    public function hasRole(string|array $roles): bool
    {
        if (is_array($roles)) {
            return in_array($this->role, $roles, true);
        }
        return $this->role === $roles;
    }

    /**
     * Role check methods
     */
    public function isAdmin()
    {
        return trim(strtoupper($this->role)) === self::ROLE_ADMIN;
    }

    public function isEventManager()
    {
        return $this->role === self::ROLE_EVENT_MANAGER;
    }

    public function isUsher()
    {
        return $this->role === self::ROLE_USHER;
    }

    public function isVisitor()
    {
        return $this->role === self::ROLE_VISITOR;
    }

    /**
     * Permission check methods
     */
    public function canManageEvents()
    {
        return $this->isAdmin() || $this->isEventManager();
    }

    public function canManageVenues()
    {
        return $this->isAdmin();
    }

    public function canManageUsers()
    {
        return $this->isAdmin();
    }

    public function canManageReports()
    {
        return $this->isAdmin() || $this->isEventManager();
    }

    public function canCheckinUsers()
    {
        return $this->isAdmin() || $this->isEventManager() || $this->isUsher();
    }

    /**
     * Get full name attribute
     */
    public function getFullNameAttribute()
    {
        if ($this->first_name && $this->last_name) {
            return $this->first_name . ' ' . $this->last_name;
        }
        
        return $this->name;
    }

    /**
     * Get initials attribute
     */
    public function getInitialsAttribute()
    {
        $nameParts = explode(' ', $this->name);
        $initials = '';
        
        foreach ($nameParts as $part) {
            $initials .= strtoupper(substr($part, 0, 1));
        }
        
        return substr($initials, 0, 2);
    }

    /**
     * Get role display name
     */
    public function getRoleDisplayName()
    {
        return self::getRoles()[$this->role] ?? $this->role;
    }

    /**
     * Get role color for UI
     */
    public function getRoleColorClass()
    {
        return match($this->role) {
            self::ROLE_ADMIN => 'bg-red-100 text-red-800',
            self::ROLE_EVENT_MANAGER => 'bg-blue-100 text-blue-800',
            self::ROLE_USHER => 'bg-green-100 text-green-800',
            self::ROLE_VISITOR => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function isStaff()
{
    return in_array($this->role, ['ADMIN', 'SUPER_ADMIN', 'EVENT_MANAGER', 'USHER']);
}

/**
 * Check if user can manage visitor logs
 */
public function canManageVisitorLogs()
{
    return $this->isAdmin() || $this->isStaff();
}
    /**
     * Get status badge class
     */
    public function getStatusBadgeClass()
    {
        return $this->is_active 
            ? 'bg-green-100 text-green-800' 
            : 'bg-red-100 text-red-800';
    }

    /**
     * Get status display text
     */
    public function getStatusDisplayText()
    {
        return $this->is_active ? 'Active' : 'Inactive';
    }

    /**
     * Scope for active users
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for inactive users
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Scope for users by role
     */
    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Scope for recent users
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope for users with registrations
     */
    public function scopeWithRegistrations($query)
    {
        return $query->has('registrations');
    }

    /**
     * Scope for search
     */
    public function scopeSearch($query, $term)
    {
        return $query->where(function($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('email', 'like', "%{$term}%")
              ->orWhere('first_name', 'like', "%{$term}%")
              ->orWhere('last_name', 'like', "%{$term}%")
              ->orWhere('company', 'like', "%{$term}%");
        });
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
        return $this->belongsToMany(Event::class, 'user_event_assignments')
                    ->withTimestamps()
                    ->withPivot('assigned_by', 'assigned_at', 'notes');
    }

    public function createdEvents()
    {
        return $this->hasMany(Event::class, 'created_by');
    }

    public function createdTickets()
    {
        return $this->hasMany(Ticket::class, 'created_by');
    }

    public function createdBadgeTemplates()
    {
        return $this->hasMany(BadgeTemplate::class, 'created_by');
    }

    public function visitorLogs()
    {
        return $this->hasManyThrough(VisitorLog::class, Registration::class);
    }

    public function createdLogs()
    {
        return $this->hasMany(VisitorLog::class, 'created_by');
    }
public function updatedLogs()
{
    return $this->hasMany(VisitorLog::class, 'updated_by');
}


    /**
     * Get user's recent activity
     */
    public function getRecentActivity($limit = 10)
    {
        return $this->visitorLogs()
                    ->with(['registration.event'])
                    ->orderBy('created_at', 'desc')
                    ->limit($limit)
                    ->get();
    }

    /**
     * Get user's event statistics
     */
    public function getEventStatistics()
    {
        return [
            'total_registrations' => $this->registrations()->count(),
            'events_attended' => $this->visitorLogs()
                                    ->where('action', 'checkin')
                                    ->distinct('registration_id')
                                    ->count(),
            'total_checkins' => $this->visitorLogs()
                                   ->where('action', 'checkin')
                                   ->count(),
            'average_duration' => $this->visitorLogs()
                                     ->where('action', 'checkout')
                                     ->whereNotNull('duration_minutes')
                                     ->avg('duration_minutes') ?? 0,
            'assigned_events' => $this->assignedEvents()->count(),
        ];
    }

    /**
     * Check if user can be assigned to events
     */
    public function canBeAssignedToEvents()
    {
        return in_array($this->role, [self::ROLE_EVENT_MANAGER, self::ROLE_USHER]);
    }

    /**
     * Check if user has access to event
     */
    public function hasAccessToEvent(Event $event)
    {
        if ($this->isAdmin()) {
            return true;
        }

        if ($this->isEventManager() || $this->isUsher()) {
            return $this->assignedEvents()->where('events.id', $event->id)->exists();
        }

        return false;
    }

    /**
     * Get events user has access to
     */
    public function getAccessibleEvents()
    {
        if ($this->isAdmin()) {
            return Event::all();
        }

        return $this->assignedEvents;
    }

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            if (is_null($user->is_active)) {
                $user->is_active = true;
            }
        });
    }
}