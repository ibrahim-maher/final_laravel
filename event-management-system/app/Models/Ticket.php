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

    public function confirmedRegistrations()
    {
        return $this->hasMany(Registration::class, 'ticket_type_id')
                    ->where('status', 'confirmed');
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
        if (!$this->capacity || $this->capacity <= 0) {
            return null; // Unlimited
        }
        
        // Only count confirmed registrations
        $registeredCount = $this->confirmedRegistrations()->count();
        return max(0, $this->capacity - $registeredCount);
    }

    public function getIsAvailableAttribute()
    {
        // First check if ticket is active
        if (!$this->is_active) {
            return false;
        }

        // If no capacity set, it's unlimited
        if (!$this->capacity || $this->capacity <= 0) {
            return true;
        }

        // Check if there are available spaces
        return $this->available_spaces > 0;
    }

    public function getRegistrationCountAttribute()
    {
        return $this->registrations()->count();
    }

    public function getConfirmedRegistrationCountAttribute()
    {
        return $this->confirmedRegistrations()->count();
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
                           ->orWhere('capacity', '<=', 0)
                           ->orWhereRaw('capacity > (SELECT COUNT(*) FROM registrations WHERE ticket_type_id = tickets.id AND status = "confirmed")');
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
        if (!$this->capacity || $this->capacity <= 0) {
            return 0; // Unlimited capacity
        }

        $registeredCount = $this->confirmed_registration_count;
        return round(($registeredCount / $this->capacity) * 100, 2);
    }

    /**
     * Get detailed availability information for debugging
     */
    public function getAvailabilityInfo()
    {
        return [
            'is_active' => $this->is_active,
            'capacity' => $this->capacity,
            'total_registrations' => $this->registration_count,
            'confirmed_registrations' => $this->confirmed_registration_count,
            'available_spaces' => $this->available_spaces,
            'is_available' => $this->is_available,
            'can_register' => $this->canRegister(),
            'registration_percentage' => $this->getRegistrationPercentage(),
        ];
    }
}