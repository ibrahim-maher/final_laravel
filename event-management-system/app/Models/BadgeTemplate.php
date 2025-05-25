<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BadgeTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id', 'name', 'width', 'height', 'background_image', 'created_by', 'default_font'
    ];

    const FONT_CHOICES = [
        'Arial' => 'Arial',
        'Helvetica' => 'Helvetica',
        'Times New Roman' => 'Times New Roman',
        'Courier' => 'Courier',
        'Verdana' => 'Verdana',
        'Georgia' => 'Georgia',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function contents()
    {
        return $this->hasMany(BadgeContent::class, 'template_id');
    }
}