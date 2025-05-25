<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Registration extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id', 'user_id', 'ticket_type_id', 'registration_data'
    ];

    protected $casts = [
        'registration_data' => 'array',
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
        return $this->hasMany(VisitorLog::class)->orderBy('created_at', 'desc');
    }

    public function getLastActionAttribute()
    {
        $lastLog = $this->visitorLogs()->first();
        return $lastLog ? $lastLog->action : null;
    }

    public function getIsCheckedInAttribute()
    {
        return $this->last_action === 'checkin';
    }
}