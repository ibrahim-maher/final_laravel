<?php

namespace App\Services;

use App\Models\Registration;
use App\Models\BadgeTemplate;
use App\Models\BadgeContent;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class BadgePrintingService
{
    /**
     * Get badge data for a single registration
     */
    public function getBadgeData(Registration $registration): array
    {
        $registration->load(['user', 'event', 'event.venue', 'ticketType', 'qrCode']);
        
        if (!$registration->ticket_type_id) {
            throw new \Exception('No ticket type assigned to this registration.');
        }
        
        $badgeTemplate = BadgeTemplate::where('ticket_id', $registration->ticket_type_id)
                                     ->with('contents')
                                     ->first();
        
        if (!$badgeTemplate) {
            throw new \Exception('No badge template configured for this ticket type.');
        }
        
        // Ensure QR code exists
        if (!$registration->qrCode) {
            $registration->generateQRCode();
            $registration->refresh();
        }
        
        // Prepare field data for template
        $badgeData = [];
        foreach ($badgeTemplate->contents as $content) {
            $badgeData[$content->field_name] = $this->getFieldDataFromContent($content, $registration);
        }
        
        return [
            'registration' => $registration,
            'template' => $badgeTemplate,
            'badge_data' => $badgeData
        ];
    }
    
    /**
     * Get badge data for multiple registrations grouped by template
     */
    public function getBulkBadgeData(array $registrationIds): array
    {
        $registrations = Registration::with(['user', 'event', 'event.venue', 'ticketType', 'qrCode'])
                                    ->whereIn('id', $registrationIds)
                                    ->whereNotNull('ticket_type_id')
                                    ->get();
        
        if ($registrations->isEmpty()) {
            throw new \Exception('No valid registrations found for badge printing.');
        }
        
        // Group registrations by ticket type to get their templates
        $groupedRegistrations = $registrations->groupBy('ticket_type_id');
        $badgeGroups = [];
        
        foreach ($groupedRegistrations as $ticketTypeId => $ticketRegistrations) {
            $badgeTemplate = BadgeTemplate::where('ticket_id', $ticketTypeId)
                                         ->with('contents')
                                         ->first();
            
            if (!$badgeTemplate) {
                Log::warning("No badge template found for ticket type {$ticketTypeId}");
                continue;
            }
            
            $registrationData = [];
            $badgeDataList = [];
            
            foreach ($ticketRegistrations as $registration) {
                // Ensure QR code exists
                if (!$registration->qrCode) {
                    $registration->generateQRCode();
                    $registration->refresh();
                }
                
                // Prepare field data for template
                $badgeData = [];
                foreach ($badgeTemplate->contents as $content) {
                    $badgeData[$content->field_name] = $this->getFieldDataFromContent($content, $registration);
                }
                
                $registrationData[] = $registration;
                $badgeDataList[] = $badgeData;
            }
            
            $badgeGroups[] = [
                'template' => $badgeTemplate,
                'registrations' => collect($registrationData),
                'badge_data' => $badgeDataList
            ];
        }
        
        if (empty($badgeGroups)) {
            throw new \Exception('No badge templates found for selected registrations.');
        }
        
        return $badgeGroups;
    }
    
    /**
     * Get field data using the BadgeContent model's logic
     */
    protected function getFieldDataFromContent(BadgeContent $content, Registration $registration): array
    {
        // Handle QR code field
        if ($content->isQrCodeField()) {
            $qrImageData = null;
            
            if ($registration->qrCode && $registration->qrCode->qr_image) {
                // Check if it's already base64 encoded
                if (str_starts_with($registration->qrCode->qr_image, 'data:image')) {
                    $qrImageData = $registration->qrCode->qr_image;
                } elseif (str_starts_with($registration->qrCode->qr_image, '/')) {
                    // It's a file path
                    $qrImageData = Storage::url($registration->qrCode->qr_image);
                } else {
                    // It's base64 data
                    $qrImageData = 'data:image/png;base64,' . $registration->qrCode->qr_image;
                }
            }
            
            return [
                'type' => 'qr_code',
                'value' => $qrImageData,
                'registration_id' => $registration->id,
                'width' => $content->image_width ?? 3,
                'height' => $content->image_height ?? 3
            ];
        }
        
        // Handle text fields using the model's logic
        $value = $content->getFieldValue($registration);
        
        return [
            'type' => 'text',
            'value' => $value,
            'font_size' => $content->font_size,
            'font_color' => $content->font_color,
            'font_family' => $content->font_family,
            'is_bold' => $content->is_bold,
            'is_italic' => $content->is_italic
        ];
    }
    
    /**
     * Generate preview HTML for a registration badge
     */
    public function generatePreviewHtml(Registration $registration): array
    {
        try {
            $badgeData = $this->getBadgeData($registration);
            
            $html = view('badges.preview', [
                'badgeTemplate' => $badgeData['template'],
                'registration' => $badgeData['registration'],
                'badgeData' => $badgeData['badge_data']
            ])->render();
            
            return [
                'success' => true,
                'html' => $html,
                'template' => $badgeData['template']->toArray()
            ];
            
        } catch (\Exception $e) {
            Log::error('Badge preview generation failed', [
                'registration_id' => $registration->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Check if registrations can be printed
     */
    public function checkPrintability(array $registrationIds): array
    {
        $registrations = Registration::with(['ticketType', 'qrCode'])
                                    ->whereIn('id', $registrationIds)
                                    ->get();
        
        $results = [];
        foreach ($registrations as $registration) {
            $hasTemplate = $registration->ticketType && 
                          BadgeTemplate::where('ticket_id', $registration->ticket_type_id)->exists();
            
            $hasQrCode = $registration->qrCode !== null;
            $canPrint = $hasTemplate && $hasQrCode;
            
            $results[] = [
                'registration_id' => $registration->id,
                'user_name' => $registration->user->name ?? 'N/A',
                'ticket_type' => $registration->ticketType->name ?? 'No ticket type',
                'has_template' => $hasTemplate,
                'has_qr_code' => $hasQrCode,
                'can_print' => $canPrint,
                'issues' => $this->getPrintIssues($hasTemplate, $hasQrCode)
            ];
        }
        
        return [
            'results' => $results,
            'printable_count' => collect($results)->where('can_print', true)->count(),
            'total_count' => count($results)
        ];
    }
    
    /**
     * Get print issues for a registration
     */
    protected function getPrintIssues(bool $hasTemplate, bool $hasQrCode): array
    {
        $issues = [];
        
        if (!$hasTemplate) {
            $issues[] = 'No badge template configured';
        }
        
        if (!$hasQrCode) {
            $issues[] = 'No QR code generated';
        }
        
        return $issues;
    }
    
    /**
     * Generate QR codes for registrations that don't have them
     */
    public function generateMissingQrCodes(array $registrationIds): array
    {
        $registrations = Registration::whereIn('id', $registrationIds)
                                    ->whereDoesntHave('qrCode')
                                    ->get();
        
        $generated = 0;
        $failed = 0;
        $errors = [];
        
        foreach ($registrations as $registration) {
            try {
                $registration->generateQRCode();
                $generated++;
                Log::info("Generated QR code for registration {$registration->id}");
            } catch (\Exception $e) {
                $failed++;
                $errors[] = "Registration {$registration->id}: " . $e->getMessage();
                Log::error('Failed to generate QR code for registration ' . $registration->id . ': ' . $e->getMessage());
            }
        }
        
        return [
            'success' => true,
            'generated' => $generated,
            'failed' => $failed,
            'errors' => $errors,
            'message' => "Generated {$generated} QR codes" . ($failed > 0 ? ", {$failed} failed" : "")
        ];
    }
    
    /**
     * Debug method to check what data is available for a registration
     */
    public function debugRegistrationData(Registration $registration): array
    {
        $registration->load(['user', 'event', 'event.venue', 'ticketType', 'qrCode']);
        
        $debug = [
            'registration_id' => $registration->id,
            'has_user' => $registration->user !== null,
            'has_event' => $registration->event !== null,
            'has_ticket_type' => $registration->ticketType !== null,
            'has_qr_code' => $registration->qrCode !== null,
            'registration_data_keys' => $registration->registration_data ? array_keys($registration->registration_data) : [],
            'user_data' => $registration->user ? $registration->user->toArray() : null,
            'event_data' => $registration->event ? [
                'id' => $registration->event->id,
                'name' => $registration->event->name,
                'start_date' => $registration->event->start_date?->format('Y-m-d'),
                'venue' => $registration->event->venue?->name
            ] : null,
            'ticket_data' => $registration->ticketType ? [
                'id' => $registration->ticketType->id,
                'name' => $registration->ticketType->name,
                'price' => $registration->ticketType->price
            ] : null,
            'registration_data' => $registration->registration_data
        ];
        
        // Check for badge template
        if ($registration->ticket_type_id) {
            $badgeTemplate = BadgeTemplate::where('ticket_id', $registration->ticket_type_id)
                                         ->with('contents')
                                         ->first();
            
            $debug['has_badge_template'] = $badgeTemplate !== null;
            
            if ($badgeTemplate) {
                $debug['badge_template'] = [
                    'id' => $badgeTemplate->id,
                    'name' => $badgeTemplate->name ?? 'Unnamed',
                    'fields_count' => $badgeTemplate->contents->count(),
                    'fields' => $badgeTemplate->contents->pluck('field_name')->toArray()
                ];
                
                // Test each field
                $debug['field_values'] = [];
                foreach ($badgeTemplate->contents as $content) {
                    $debug['field_values'][$content->field_name] = $content->debugFieldValue($registration);
                }
            }
        }
        
        return $debug;
    }
}