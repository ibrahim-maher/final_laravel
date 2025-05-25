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

    public function assignedUsers()
    {
        return $this->belongsToMany(User::class, 'user_event');
    }

    // Boot method to handle is_active logic
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
}