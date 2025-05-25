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
        'order' => 'integer',
    ];

    const FIELD_TYPES = [
        'text' => 'Text',
        'email' => 'Email',
        'number' => 'Number',
        'dropdown' => 'Dropdown',
        'checkbox' => 'Checkbox',
        'textarea' => 'Textarea',
        'date' => 'Date',
        'phone' => 'Phone',
    ];

    const DEFAULT_FIELDS = [
        ['field_name' => 'First Name', 'field_type' => 'text', 'is_required' => true],
        ['field_name' => 'Last Name', 'field_type' => 'text', 'is_required' => true],
        ['field_name' => 'Email', 'field_type' => 'email', 'is_required' => true],
        ['field_name' => 'Phone Number', 'field_type' => 'phone', 'is_required' => true],
        ['field_name' => 'Title', 'field_type' => 'text', 'is_required' => true],
        ['field_name' => 'Company Name', 'field_type' => 'text', 'is_required' => false],
    ];

    // Relationships
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    // Accessors
    public function getOptionsArrayAttribute()
    {
        return $this->options ? explode(',', $this->options) : [];
    }

    public function getFieldTypeDisplayAttribute()
    {
        return self::FIELD_TYPES[$this->field_type] ?? $this->field_type;
    }

    // Scopes
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }

    public function scopeForEvent($query, $eventId)
    {
        return $query->where('event_id', $eventId);
    }

    // Methods
    public static function createDefaultFields($eventId)
    {
        foreach (self::DEFAULT_FIELDS as $index => $field) {
            self::create([
                'event_id' => $eventId,
                'field_name' => $field['field_name'],
                'field_type' => $field['field_type'],
                'is_required' => $field['is_required'],
                'order' => $index + 1,
            ]);
        }
    }

    public function generateValidationRules()
    {
        $rules = [];
        
        if ($this->is_required) {
            $rules[] = 'required';
        } else {
            $rules[] = 'nullable';
        }

        switch ($this->field_type) {
            case 'email':
                $rules[] = 'email';
                break;
            case 'number':
                $rules[] = 'numeric';
                break;
            case 'phone':
                $rules[] = 'string';
                $rules[] = 'max:20';
                break;
            case 'date':
                $rules[] = 'date';
                break;
            case 'text':
            case 'textarea':
                $rules[] = 'string';
                $rules[] = 'max:255';
                break;
            case 'dropdown':
                if ($this->options) {
                    $options = $this->options_array;
                    $rules[] = 'in:' . implode(',', $options);
                }
                break;
            case 'checkbox':
                $rules[] = 'boolean';
                break;
        }

        return implode('|', $rules);
    }
}