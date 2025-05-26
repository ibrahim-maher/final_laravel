<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistrationField extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'field_name',
        'field_type',
        'is_required',
        'options',
        'order',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'order' => 'integer',
    ];

    // Field types constant
    const FIELD_TYPES = [
        'text' => 'Text',
        'textarea' => 'Textarea',
        'email' => 'Email',
        'number' => 'Number',
        'phone' => 'Phone',
        'dropdown' => 'Dropdown',
        'radio' => 'Radio',
        'checkbox' => 'Checkbox',
        'date' => 'Date',
        'time' => 'Time',
        'url' => 'URL',
    ];

    /**
     * Relationship with Event
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Scope for ordered fields
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    /**
     * Get field type display name
     */
    public function getFieldTypeDisplayAttribute()
    {
        return self::FIELD_TYPES[$this->field_type] ?? $this->field_type;
    }

    /**
     * Get options as array
     */
    public function getOptionsArrayAttribute()
    {
        if (empty($this->options)) {
            return [];
        }
        
        return array_map('trim', explode(',', $this->options));
    }

    /**
     * Set options from array
     */
    public function setOptionsArrayAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['options'] = implode(',', array_filter($value));
        }
    }

    /**
     * Check if field has options
     */
    public function hasOptions()
    {
        return in_array($this->field_type, ['dropdown', 'radio', 'checkbox']) && !empty($this->options);
    }

    /**
     * Get validation rules for this field
     */
    public function getValidationRules()
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
                break;
            case 'url':
                $rules[] = 'url';
                break;
            case 'date':
                $rules[] = 'date';
                break;
            case 'time':
                $rules[] = 'date_format:H:i';
                break;
            case 'dropdown':
            case 'radio':
                if ($this->hasOptions()) {
                    $rules[] = 'in:' . implode(',', $this->options_array);
                }
                break;
            case 'checkbox':
                $rules[] = 'array';
                if ($this->hasOptions()) {
                    $rules[] = 'in:' . implode(',', $this->options_array);
                }
                break;
            default:
                $rules[] = 'string';
                break;
        }

        return implode('|', $rules);
    }

    /**
     * Get HTML input type for this field
     */
    public function getHtmlInputType()
    {
        switch ($this->field_type) {
            case 'email':
                return 'email';
            case 'number':
                return 'number';
            case 'phone':
                return 'tel';
            case 'url':
                return 'url';
            case 'date':
                return 'date';
            case 'time':
                return 'time';
            case 'textarea':
                return 'textarea';
            case 'dropdown':
                return 'select';
            case 'radio':
                return 'radio';
            case 'checkbox':
                return 'checkbox';
            default:
                return 'text';
        }
    }

    /**
     * Boot method for model events
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-set order when creating
        static::creating(function ($model) {
            if (empty($model->order)) {
                $model->order = static::where('event_id', $model->event_id)->max('order') + 1;
            }
        });
    }
}