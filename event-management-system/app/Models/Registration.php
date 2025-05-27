<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Registration extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id', 'user_id', 'ticket_type_id', 'registration_data', 'status'
    ];

    protected $casts = [
        'registration_data' => 'array',
        'registered_at' => 'datetime',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_CANCELLED = 'cancelled';

    const STATUSES = [
        self::STATUS_PENDING => 'Pending',
        self::STATUS_CONFIRMED => 'Confirmed', 
        self::STATUS_CANCELLED => 'Cancelled',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ticketType()
    {
        return $this->belongsTo(Ticket::class, 'ticket_type_id');
    }

    public function qrCode()
    {
        return $this->hasOne(QRCode::class);
    }

    public function visitorLogs()
    {
        return $this->hasMany(VisitorLog::class);
    }

    // Accessors
    public function getStatusDisplayAttribute()
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    // Remove the custom getter/setter since we're using the cast
    // The cast will handle the JSON conversion automatically

    // Scopes
    public function scopeForEvent($query, $eventId)
    {
        return $query->where('event_id', $eventId);
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', self::STATUS_CONFIRMED);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeSearch($query, $search)
    {
        return $query->whereHas('user', function($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%");
        })->orWhereHas('event', function($q) use ($search) {
            $q->where('name', 'like', "%{$search}%");
        });
    }

    // Methods
    public function getRegistrationFieldValue($fieldName)
    {
        $data = $this->registration_data ?? [];
        return $data[$fieldName] ?? null;
    }

    public function setRegistrationFieldValue($fieldName, $value)
    {
        $data = $this->registration_data ?? [];
        $data[$fieldName] = $value;
        $this->registration_data = $data;
    }

    public function generateQRCode()
    {
        if (!$this->qrCode && class_exists('App\Models\QRCode')) {
            try {
                \App\Models\QRCode::create([
                    'registration_id' => $this->id,
                    'ticket_type_id' => $this->ticket_type_id,
                ]);
            } catch (\Exception $e) {
                \Log::error('Failed to generate QR code: ' . $e->getMessage());
            }
        }
    }

    public static function boot()
    {
        parent::boot();

        static::created(function ($registration) {
            // Auto-generate QR code when registration is created
            try {
                $registration->generateQRCode();
            } catch (\Exception $e) {
                \Log::error('Failed to auto-generate QR code: ' . $e->getMessage());
                // Don't fail the registration creation if QR code generation fails
            }
        });
    }
}