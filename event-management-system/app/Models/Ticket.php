<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = ['event_id', 'created_by', 'name', 'price', 'capacity'];

    protected $casts = [
        'price' => 'decimal:2',
    ];

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

    public function badgeTemplate()
    {
        return $this->hasOne(BadgeTemplate::class);
    }

    public function qrCodes()
    {
        return $this->hasMany(QRCode::class);
    }
}