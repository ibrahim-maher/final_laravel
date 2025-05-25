<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BadgeContent extends Model
{
    use HasFactory;

    protected $fillable = [
        'template_id', 'field_name', 'position_x', 'position_y', 'font_size',
        'font_color', 'font_family', 'is_bold', 'is_italic', 'image_width', 'image_height'
    ];

    protected $casts = [
        'position_x' => 'float',
        'position_y' => 'float',
        'font_size' => 'integer',
        'is_bold' => 'boolean',
        'is_italic' => 'boolean',
        'image_width' => 'float',
        'image_height' => 'float',
    ];

    const FIELD_CHOICES = [
        'user__username' => 'Username',
        'user__email' => 'Email',
        'user__first_name' => 'First Name',
        'user__last_name' => 'Last Name',
        'user__full_name' => 'Full Name',
        'user__country' => 'Country',
        'user__title' => 'Title',
        'user__company' => 'Company',
        'ticket_type__name' => 'Ticket Type',
        'event__name' => 'Event Name',
        'event__location' => 'Event Location',
        'qr_code__qr_image' => 'QR Code',
    ];

    public function template()
    {
        return $this->belongsTo(BadgeTemplate::class, 'template_id');
    }

    // Get field display name
    public function getFieldDisplayName()
    {
        return self::FIELD_CHOICES[$this->field_name] ?? $this->field_name;
    }

    // Get field value from registration
    public function getFieldValue($registration)
    {
        try {
            $fieldParts = explode('__', $this->field_name);
            $value = $registration;

            // Special handling for QR code
            if ($fieldParts[0] === 'qr_code') {
                $value = $registration->qrCode;
                for ($i = 1; $i < count($fieldParts); $i++) {
                    $value = $value->{$fieldParts[$i]} ?? null;
                }
                return $value;
            }

            // Handle nested relationships
            foreach ($fieldParts as $part) {
                if (is_object($value)) {
                    if ($part === 'full_name' && isset($value->first_name, $value->last_name)) {
                        $value = trim($value->first_name . ' ' . $value->last_name);
                    } else {
                        $value = $value->$part ?? null;
                    }
                } else {
                    break;
                }
                
                if ($value === null) {
                    break;
                }
            }

            return $value;
        } catch (\Exception $e) {
            return "Field {$this->field_name} not found";
        }
    }

    // Get font styles as CSS string
    public function getFontStyles()
    {
        $styles = [
            "font-size: {$this->font_size}pt",
            "color: {$this->font_color}",
            "font-family: {$this->font_family}",
        ];

        if ($this->is_bold) {
            $styles[] = "font-weight: bold";
        }

        if ($this->is_italic) {
            $styles[] = "font-style: italic";
        }

        return implode('; ', $styles);
    }

    // Get position styles as CSS string
    public function getPositionStyles()
    {
        return "position: absolute; left: {$this->position_x}cm; top: {$this->position_y}cm;";
    }

    // Check if field is QR code
    public function isQrCodeField()
    {
        return $this->field_name === 'qr_code__qr_image';
    }
}