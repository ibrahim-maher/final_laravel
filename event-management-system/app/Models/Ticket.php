<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id', 'name', 'description', 'price', 'capacity', 'is_active', 'created_by'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'capacity' => 'integer',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function registrations()
    {
        return $this->hasMany(Registration::class, 'ticket_type_id');
    }

    public function qrCodes()
    {
        return $this->hasMany(QRCode::class, 'ticket_type_id');
    }

    // Accessors
    public function getFormattedPriceAttribute()
    {
        return $this->price ? '$' . number_format($this->price, 2) : 'Free';
    }

    public function getAvailableSpacesAttribute()
    {
        if (!$this->capacity) {
            return null; // Unlimited
        }
        
        $registeredCount = $this->registrations()->count();
        return max(0, $this->capacity - $registeredCount);
    }

    public function getIsAvailableAttribute()
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->capacity) {
            return $this->available_spaces > 0;
        }

        return true; // Unlimited capacity
    }

    public function getRegistrationCountAttribute()
    {
        return $this->registrations()->count();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAvailable($query)
    {
        return $query->where('is_active', true)
                     ->where(function($q) {
                         $q->whereNull('capacity')
                           ->orWhereRaw('capacity > (SELECT COUNT(*) FROM registrations WHERE ticket_type_id = tickets.id)');
                     });
    }

    public function scopeForEvent($query, $eventId)
    {
        return $query->where('event_id', $eventId);
    }

    // Methods
    public function canRegister()
    {
        return $this->is_available;
    }

    public function getRegistrationPercentage()
    {
        if (!$this->capacity) {
            return 0; // Unlimited capacity
        }

        $registeredCount = $this->registration_count;
        return round(($registeredCount / $this->capacity) * 100, 2);
    }
}