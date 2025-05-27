<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class BadgeContent extends Model
{
    use HasFactory;

    protected $fillable = [
        'template_id', 
        'badge_template_id', // Support both naming conventions
        'field_name', 
        'position_x', 
        'position_y', 
        'font_size',
        'font_color', 
        'font_family', 
        'is_bold', 
        'is_italic', 
        'image_width', 
        'image_height'
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

    // Updated field choices with consistent naming
    const FIELD_CHOICES = [
        // User fields
        'user__name' => 'Participant Name',
        'user__username' => 'Username',
        'user__email' => 'Email Address',
        'user__first_name' => 'First Name',
        'user__last_name' => 'Last Name',
        'user__full_name' => 'Full Name',
        'user__phone' => 'Phone Number',
        'user__country' => 'Country',
        'user__title' => 'Title',
        'user__company' => 'Company',
        
        // Event fields
        'event__name' => 'Event Name',
        'event__location' => 'Event Location',
        'event__venue' => 'Venue Name',
        'event__date' => 'Event Date',
        'event__start_date' => 'Event Start Date',
        'event__end_date' => 'Event End Date',
        'event__time' => 'Event Time',
        'event__start_time' => 'Event Start Time',
        
        // Ticket fields
        'ticket__name' => 'Ticket Type',
        'ticket__price' => 'Ticket Price',
        'ticket_type__name' => 'Ticket Type',
        'ticket_type__price' => 'Ticket Price',
        
        // Registration fields
        'registration__id' => 'Registration ID',
        'registration__status' => 'Registration Status',
        'registration__date' => 'Registration Date',
        'registration__created_at' => 'Registration Date',
        
        // QR Code
        'registration__qr_code' => 'QR Code',
        'qr_code__qr_image' => 'QR Code',
        'qr_code' => 'QR Code',
        
        // Venue
        'venue__name' => 'Venue Name',
        'venue__address' => 'Venue Address',
    ];

    /**
     * Relationship to badge template (support both naming conventions)
     */
    public function template()
    {
        return $this->belongsTo(BadgeTemplate::class, 'template_id');
    }

    public function badgeTemplate()
    {
        return $this->belongsTo(BadgeTemplate::class, 'badge_template_id')
                    ?: $this->belongsTo(BadgeTemplate::class, 'template_id');
    }

    /**
     * Check if this is a QR code field
     */
    public function isQrCodeField(): bool
    {
        return in_array($this->field_name, [
            'registration__qr_code',
            'qr_code__qr_image',
            'qr_code'
        ]);
    }

    /**
     * Get field display name
     */
    public function getFieldDisplayName(): string
    {
        return self::FIELD_CHOICES[$this->field_name] ?? 
               ucwords(str_replace(['_', '__'], ' ', $this->field_name));
    }

    /**
     * Get field value from registration - Updated to work with the service
     */
    public function getFieldValue($registration): string
    {
        try {
            // Handle QR code fields specially
            if ($this->isQrCodeField()) {
                return 'QR Code'; // This will be handled by the service
            }

            $value = $this->extractFieldValue($registration, $this->field_name);
            
            // Handle different value types
            if (is_array($value)) {
                return implode(', ', $value);
            }
            
            if (is_bool($value)) {
                return $value ? 'Yes' : 'No';
            }
            
            if (is_null($value) || $value === '') {
                return 'N/A';
            }
            
            return (string) $value;
            
        } catch (\Exception $e) {
            Log::error('BadgeContent field value error: ' . $e->getMessage(), [
                'field_name' => $this->field_name,
                'registration_id' => $registration->id ?? null,
                'trace' => $e->getTraceAsString()
            ]);
            return 'N/A';
        }
    }

    /**
     * Extract field value based on field name
     */
    private function extractFieldValue($registration, $fieldName): mixed
    {
        // Handle built-in fields first
        switch ($fieldName) {
            // User fields
            case 'user__name':
            case 'user__full_name':
                return $registration->user->name ?? null;
                
            case 'user__username':
                return $registration->user->username ?? $registration->user->name ?? null;
                
            case 'user__email':
                return $registration->user->email ?? null;
                
            case 'user__phone':
                return $registration->user->phone ?? 
                       $this->getRegistrationDataValue($registration, ['Phone', 'phone', 'Phone Number', 'phone_number']);
                
            case 'user__first_name':
                return $this->extractFirstName($registration->user);
                
            case 'user__last_name':
                return $this->extractLastName($registration->user);
                
            case 'user__country':
                return $registration->user->country ?? 
                       $this->getRegistrationDataValue($registration, ['Country', 'country']);
                
            case 'user__title':
                return $registration->user->title ?? 
                       $this->getRegistrationDataValue($registration, ['Title', 'Job Title', 'job_title', 'title']);
                
            case 'user__company':
                return $registration->user->company ?? 
                       $this->getRegistrationDataValue($registration, ['Company', 'Organization', 'company', 'organization']);

            // Event fields
            case 'event__name':
                return $registration->event->name ?? null;
                
            case 'event__location':
            case 'event__venue':
            case 'venue__name':
                return $registration->event->venue->name ?? 
                       $registration->event->location ?? 
                       $registration->event->venue ?? null;
                
            case 'venue__address':
                return $registration->event->venue->address ?? null;
                
            case 'event__date':
            case 'event__start_date':
                return $registration->event->start_date ? 
                       $registration->event->start_date->format('M d, Y') : null;
                
            case 'event__end_date':
                return $registration->event->end_date ? 
                       $registration->event->end_date->format('M d, Y') : null;
                
            case 'event__time':
            case 'event__start_time':
                return $registration->event->start_time ? 
                       $registration->event->start_time->format('g:i A') : null;

            // Ticket fields
            case 'ticket__name':
            case 'ticket_type__name':
                return $registration->ticketType->name ?? null;
                
            case 'ticket__price':
            case 'ticket_type__price':
                return $registration->ticketType ? 
                       '$' . number_format($registration->ticketType->price, 2) : null;

            // Registration fields
            case 'registration__id':
                return (string) $registration->id;
                
            case 'registration__status':
                return ucfirst($registration->status);
                
            case 'registration__date':
            case 'registration__created_at':
                return $registration->created_at->format('M d, Y');

            default:
                // Try to get from registration data or custom fields
                return $this->getCustomFieldValue($registration, $fieldName);
        }
    }

    /**
     * Get custom field value from registration data
     */
    private function getCustomFieldValue($registration, $fieldName): mixed
    {
        $registrationData = $registration->registration_data ?? [];
        
        if (empty($registrationData)) {
            return null;
        }
        
        // Try exact match first
        if (isset($registrationData[$fieldName])) {
            return $registrationData[$fieldName];
        }
        
        // Try without prefixes (remove __ parts)
        $cleanFieldName = str_replace(['user__', 'event__', 'ticket__', 'registration__'], '', $fieldName);
        
        // Try various common variations
        $variations = [
            $cleanFieldName,
            ucfirst($cleanFieldName),
            ucwords(str_replace('_', ' ', $cleanFieldName)),
            str_replace('_', ' ', $cleanFieldName),
            Str::studly($cleanFieldName),
            Str::camel($cleanFieldName),
            Str::snake($cleanFieldName),
        ];
        
        foreach ($variations as $variation) {
            if (isset($registrationData[$variation]) && $registrationData[$variation] !== '') {
                return $registrationData[$variation];
            }
        }
        
        return null;
    }

    /**
     * Get registration data value by trying multiple possible keys
     */
    private function getRegistrationDataValue($registration, array $possibleKeys): mixed
    {
        $registrationData = $registration->registration_data ?? [];
        
        foreach ($possibleKeys as $key) {
            if (isset($registrationData[$key]) && !empty($registrationData[$key])) {
                return $registrationData[$key];
            }
        }
        
        return null;
    }

    /**
     * Extract first name from user
     */
    private function extractFirstName($user): string
    {
        if (!$user) return '';
        
        if (isset($user->first_name) && !empty($user->first_name)) {
            return $user->first_name;
        }
        
        $nameParts = explode(' ', trim($user->name ?? ''));
        return $nameParts[0] ?? '';
    }

    /**
     * Extract last name from user
     */
    private function extractLastName($user): string
    {
        if (!$user) return '';
        
        if (isset($user->last_name) && !empty($user->last_name)) {
            return $user->last_name;
        }
        
        $nameParts = explode(' ', trim($user->name ?? ''));
        return count($nameParts) > 1 ? end($nameParts) : '';
    }

    /**
     * Get formatted field value for display
     */
    public function getFormattedFieldValue($registration): string
    {
        if ($this->isQrCodeField()) {
            return 'QR Code';
        }
        
        return $this->getFieldValue($registration);
    }

    /**
     * Get position styles as CSS string
     */
    public function getPositionStyles(): string
    {
        return sprintf(
            'position: absolute; left: %scm; top: %scm;',
            $this->position_x,
            $this->position_y
        );
    }

    /**
     * Get font styles as CSS string
     */
    public function getFontStyles(): string
    {
        $styles = [
            sprintf('font-size: %spt', $this->font_size),
            sprintf('color: %s', $this->font_color),
            sprintf('font-family: %s', $this->font_family),
        ];

        if ($this->is_bold) {
            $styles[] = 'font-weight: bold';
        }

        if ($this->is_italic) {
            $styles[] = 'font-style: italic';
        }

        return implode('; ', $styles);
    }

    /**
     * Debug method to see what data is available
     */
    public function debugFieldValue($registration): array
    {
        return [
            'field_name' => $this->field_name,
            'user_data' => $registration->user ? $registration->user->toArray() : null,
            'event_data' => $registration->event ? $registration->event->toArray() : null,
            'ticket_data' => $registration->ticketType ? $registration->ticketType->toArray() : null,
            'registration_data' => $registration->registration_data,
            'extracted_value' => $this->getFieldValue($registration)
        ];
    }
}