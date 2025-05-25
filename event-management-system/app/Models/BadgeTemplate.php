<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class BadgeTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id', 'name', 'width', 'height', 'background_image', 'created_by', 'default_font'
    ];

    protected $casts = [
        'width' => 'float',
        'height' => 'float',
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

    // Get background image URL
    public function getBackgroundImageUrlAttribute()
    {
        return $this->background_image ? Storage::url($this->background_image) : null;
    }

    // Check if template has background image
    public function hasBackgroundImage()
    {
        return !empty($this->background_image);
    }

    // Get template dimensions as array
    public function getDimensions()
    {
        return [
            'width' => $this->width,
            'height' => $this->height
        ];
    }

    // Delete background image when template is deleted
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($template) {
            if ($template->background_image) {
                Storage::disk('public')->delete($template->background_image);
            }
        });
    }
}