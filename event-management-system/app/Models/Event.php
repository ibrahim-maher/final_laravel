<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'description', 'start_date', 'end_date', 
        'venue_id', 'category_id', 'is_active', 'logo'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function venue()
    {
        return $this->belongsTo(Venue::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function registrations()
    {
        return $this->hasMany(Registration::class);
    }

    public function registrationFields()
    {
        return $this->hasMany(RegistrationField::class)->orderBy('order');
    }

    public function getOrderedRegistrationFields()
    {
        return $this->registrationFields()->ordered()->get();
    }

    public function assignedUsers()
    {
        return $this->belongsToMany(User::class, 'user_event');
    }

    // REMOVED: Boot method that was causing the issue
    // The old boot method was deactivating all other events when one was set to active
    // This prevented multiple events from being active simultaneously
    
    // If you need only one active event at a time, uncomment the boot method below:
    /*
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($event) {
            if ($event->is_active) {
                // Deactivate all other events
                static::where('id', '!=', $event->id)->update(['is_active' => false]);
            }
        });
    }
    */

    public function getStatusAttribute()
    {
        $now = now();
        if ($now < $this->start_date) {
            return 'upcoming';
        } elseif ($now > $this->end_date) {
            return 'completed';
        } else {
            return 'ongoing';
        }
    }

    // Helper method to check if event has available tickets
    public function hasAvailableTickets()
    {
        return $this->tickets()->where('is_active', true)->exists();
    }

    // Helper method to check if event is available for registration
    public function isAvailableForRegistration()
    {
        return $this->is_active && 
               $this->start_date > now() && 
               $this->hasAvailableTickets();
    }

    // Scope for available events
    public function scopeAvailableForRegistration($query)
    {
        return $query->where('is_active', true)
                    ->where('start_date', '>', now())
                    ->whereHas('tickets', function($q) {
                        $q->where('is_active', true);
                    });
    }
}