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
        'is_bold' => 'boolean',
        'is_italic' => 'boolean',
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

    public function getFieldValue($registration)
    {
        $fieldParts = explode('__', $this->field_name);
        $value = $registration;

        try {
            foreach ($fieldParts as $part) {
                if (is_object($value)) {
                    $value = $value->$part;
                } else {
                    break;
                }
            }
            return $value;
        } catch (\Exception $e) {
            return "Field {$this->field_name} not found";
        }
    }
}