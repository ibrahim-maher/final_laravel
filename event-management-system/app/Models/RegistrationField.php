<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegistrationField extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id', 'field_name', 'field_type', 'is_required', 'options', 'order'
    ];

    protected $casts = [
        'is_required' => 'boolean',
    ];

    const FIELD_TYPES = [
        'text' => 'Text',
        'email' => 'Email',
        'number' => 'Number',
        'dropdown' => 'Dropdown',
        'checkbox' => 'Checkbox',
    ];

    const DEFAULT_FIELDS = [
        ['field_name' => 'First Name', 'field_type' => 'text', 'is_required' => true],
        ['field_name' => 'Last Name', 'field_type' => 'text', 'is_required' => true],
        ['field_name' => 'Email', 'field_type' => 'email', 'is_required' => true],
        ['field_name' => 'Phone Number', 'field_type' => 'number', 'is_required' => true],
        ['field_name' => 'Title', 'field_type' => 'text', 'is_required' => true],
        ['field_name' => 'Company Name', 'field_type' => 'text', 'is_required' => false],
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function getOptionsArrayAttribute()
    {
        return $this->options ? explode(',', $this->options) : [];
    }
}