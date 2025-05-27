<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

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
        'user__name' => 'Name',
        'user__phone' => 'Phone Number',
        'user__country' => 'Country',
        'user__title' => 'Title',
        'user__company' => 'Company',
        'ticket_type__name' => 'Ticket Type',
        'ticket_type__price' => 'Ticket Price',
        'event__name' => 'Event Name',
        'event__location' => 'Event Location',
        'event__start_date' => 'Event Start Date',
        'event__end_date' => 'Event End Date',
        'registration__status' => 'Registration Status',
        'registration__created_at' => 'Registration Date',
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
            $value = null;

            // Handle different field types
            switch ($fieldParts[0]) {
                case 'user':
                    $value = $this->getUserFieldValue($registration, $fieldParts);
                    break;
                case 'event':
                    $value = $this->getEventFieldValue($registration, $fieldParts);
                    break;
                case 'ticket_type':
                    $value = $this->getTicketFieldValue($registration, $fieldParts);
                    break;
                case 'registration':
                    $value = $this->getRegistrationFieldValue($registration, $fieldParts);
                    break;
                case 'qr_code':
                    $value = $this->getQrCodeFieldValue($registration, $fieldParts);
                    break;
                default:
                    // Try to get from registration data
                    $value = $this->getCustomFieldValue($registration, $this->field_name);
                    break;
            }

            return $value ?: $this->getFieldDisplayName();
        } catch (\Exception $e) {
            \Log::error('BadgeContent field value error: ' . $e->getMessage(), [
                'field_name' => $this->field_name,
                'registration_id' => $registration->id ?? null
            ]);
            return $this->getFieldDisplayName();
        }
    }

    private function getUserFieldValue($registration, $fieldParts)
    {
        $user = $registration->user;
        if (!$user) return null;

        $field = $fieldParts[1] ?? null;
        switch ($field) {
            case 'username':
                return $user->username ?? $user->name;
            case 'email':
                return $user->email;
            case 'first_name':
                return $this->extractFirstName($user);
            case 'last_name':
                return $this->extractLastName($user);
            case 'full_name':
            case 'name':
                return $user->name;
            case 'phone':
                return $user->phone;
            case 'country':
                return $user->country ?? $this->getRegistrationDataValue($registration, ['Country', 'country']);
            case 'title':
                return $user->title ?? $this->getRegistrationDataValue($registration, ['Title', 'Job Title', 'job_title']);
            case 'company':
                return $user->company ?? $this->getRegistrationDataValue($registration, ['Company', 'Organization', 'company', 'organization']);
            default:
                return $user->$field ?? null;
        }
    }

    private function getEventFieldValue($registration, $fieldParts)
    {
        $event = $registration->event;
        if (!$event) return null;

        $field = $fieldParts[1] ?? null;
        switch ($field) {
            case 'name':
                return $event->name;
            case 'location':
                return $event->location ?? $event->venue->name ?? null;
            case 'start_date':
                return $event->start_date ? $event->start_date->format('M d, Y') : null;
            case 'end_date':
                return $event->end_date ? $event->end_date->format('M d, Y') : null;
            default:
                return $event->$field ?? null;
        }
    }

    private function getTicketFieldValue($registration, $fieldParts)
    {
        $ticket = $registration->ticketType;
        if (!$ticket) return null;

        $field = $fieldParts[1] ?? null;
        switch ($field) {
            case 'name':
                return $ticket->name;
            case 'price':
                return '$' . number_format($ticket->price, 2);
            default:
                return $ticket->$field ?? null;
        }
    }

    private function getRegistrationFieldValue($registration, $fieldParts)
    {
        $field = $fieldParts[1] ?? null;
        switch ($field) {
            case 'status':
                return ucfirst($registration->status);
            case 'created_at':
                return $registration->created_at->format('M d, Y');
            default:
                return $registration->$field ?? null;
        }
    }

    private function getQrCodeFieldValue($registration, $fieldParts)
    {
        // QR code is handled specially in the view
        return null;
    }

    private function getCustomFieldValue($registration, $fieldName)
    {
        $registrationData = $registration->registration_data ?? [];
        
        // Try exact match first
        if (isset($registrationData[$fieldName])) {
            return $registrationData[$fieldName];
        }
        
        // Try common variations
        $variations = [
            $fieldName,
            ucfirst($fieldName),
            Str::title(str_replace('_', ' ', $fieldName)),
            str_replace('_', ' ', $fieldName),
        ];
        
        foreach ($variations as $variation) {
            if (isset($registrationData[$variation])) {
                return $registrationData[$variation];
            }
        }
        
        return null;
    }

    private function getRegistrationDataValue($registration, $possibleKeys)
    {
        $registrationData = $registration->registration_data ?? [];
        
        foreach ($possibleKeys as $key) {
            if (isset($registrationData[$key]) && !empty($registrationData[$key])) {
                return $registrationData[$key];
            }
        }
        
        return null;
    }

    private function extractFirstName($user)
    {
        if (isset($user->first_name)) {
            return $user->first_name;
        }
        
        $nameParts = explode(' ', $user->name ?? '');
        return $nameParts[0] ?? '';
    }

    private function extractLastName($user)
    {
        if (isset($user->last_name)) {
            return $user->last_name;
        }
        
        $nameParts = explode(' ', $user->name ?? '');
        return count($nameParts) > 1 ? end($nameParts) : '';
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

    // Get formatted field value for display
    public function getFormattedFieldValue($registration)
    {
        $value = $this->getFieldValue($registration);
        
        if (is_array($value)) {
            return implode(', ', $value);
        }
        
        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }
        
        return (string) $value;
    }
}