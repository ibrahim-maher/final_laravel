<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use App\Models\BadgeTemplate;
use App\Models\BadgeContent;
use App\Models\QRCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class BadgePrintController extends Controller
{
    /**
     * Print a single badge
     */
    public function printSingleBadge(Registration $registration)
    {
        try {
            // Load necessary relationships
            $registration->load(['user', 'event', 'ticketType', 'qrCode']);
            
            // Get badge template for this registration's ticket type
            $badgeTemplate = BadgeTemplate::where('ticket_id', $registration->ticket_type_id)
                                         ->with('contents')
                                         ->first();
            
            if (!$badgeTemplate) {
                return $this->renderNoTemplateError($registration);
            }
            
            // Ensure QR code exists
            if (!$registration->qrCode) {
                $registration->generateQRCode();
                $registration->refresh();
            }
            
            // Prepare badge data
            $badgeData = $this->prepareBadgeData($registration, $badgeTemplate->contents);
            
            return view('badges.print-single', compact('registration', 'badgeTemplate', 'badgeData'));
            
        } catch (\Exception $e) {
            Log::error('Error printing badge: ' . $e->getMessage(), [
                'registration_id' => $registration->id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return $this->renderPrintError($e->getMessage());
        }
    }
    
    /**
     * Print multiple badges
     */
    public function printMultipleBadges(Request $request)
    {
        try {
            $registrationIds = $request->get('ids', '');
            $registrationIds = array_filter(explode(',', $registrationIds));
            
            if (empty($registrationIds)) {
                return response('No registration IDs provided. Use ?ids=1,2,3', 400);
            }
            
            // Get registrations with relationships
            $registrations = Registration::with(['user', 'event', 'ticketType', 'qrCode'])
                                        ->whereIn('id', $registrationIds)
                                        ->get();
            
            if ($registrations->isEmpty()) {
                return response('No valid registrations found for the provided IDs', 404);
            }
            
            // Group registrations by their badge templates
            $badgeGroups = $this->groupRegistrationsByTemplate($registrations);
            
            if (empty($badgeGroups)) {
                return $this->renderNoTemplateError($registrations->first());
            }
            
            return view('badges.print-multiple', compact('badgeGroups'));
            
        } catch (\Exception $e) {
            Log::error('Error printing multiple badges: ' . $e->getMessage());
            return $this->renderPrintError($e->getMessage());
        }
    }
    
    /**
     * Bulk print badges from form submission
     */
    public function bulkPrintBadges(Request $request)
    {
        $request->validate([
            'registration_ids' => 'required|array',
            'registration_ids.*' => 'exists:registrations,id'
        ]);
        
        try {
            $registrations = Registration::with(['user', 'event', 'ticketType', 'qrCode'])
                                        ->whereIn('id', $request->registration_ids)
                                        ->whereNotNull('ticket_type_id')
                                        ->get();
            
            if ($registrations->isEmpty()) {
                return back()->with('error', 'No valid registrations found for badge printing.');
            }
            
            // Ensure all registrations have QR codes
            foreach ($registrations as $registration) {
                if (!$registration->qrCode) {
                    try {
                        $registration->generateQRCode();
                    } catch (\Exception $e) {
                        Log::warning('Failed to generate QR code for registration ' . $registration->id);
                    }
                }
            }
            
            // Group registrations by their badge templates
            $badgeGroups = $this->groupRegistrationsByTemplate($registrations);
            
            if (empty($badgeGroups)) {
                return back()->with('error', 'No badge templates found for selected registrations.');
            }
            
            return view('badges.print-bulk', compact('badgeGroups'));
            
        } catch (\Exception $e) {
            Log::error('Bulk badge print error: ' . $e->getMessage());
            return back()->with('error', 'Error preparing badges for printing: ' . $e->getMessage());
        }
    }
    
    /**
     * Preview badge before printing
     */
    public function previewBadge(Registration $registration)
    {
        try {
            $registration->load(['user', 'event', 'ticketType', 'qrCode']);
            
            $badgeTemplate = BadgeTemplate::where('ticket_id', $registration->ticket_type_id)
                                         ->with('contents')
                                         ->first();
            
            if (!$badgeTemplate) {
                return response()->json(['error' => 'No badge template found'], 400);
            }
            
            $badgeData = $this->prepareBadgeData($registration, $badgeTemplate->contents);
            
            $previewHtml = view('badges.preview', compact('registration', 'badgeTemplate', 'badgeData'))->render();
            
            return response()->json([
                'success' => true,
                'html' => $previewHtml,
                'template' => $badgeTemplate->toArray()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Badge preview error: ' . $e->getMessage());
            return response()->json(['error' => 'Error generating preview'], 500);
        }
    }
    
    /**
     * Check if registrations have badge templates
     */
    public function checkBadgeTemplates(Request $request)
    {
        $request->validate([
            'registration_ids' => 'required|array',
            'registration_ids.*' => 'exists:registrations,id'
        ]);
        
        try {
            $registrations = Registration::with(['ticketType.badgeTemplate', 'user'])
                                        ->whereIn('id', $request->registration_ids)
                                        ->get();
            
            $results = [];
            foreach ($registrations as $registration) {
                $hasTemplate = $registration->ticketType && 
                              BadgeTemplate::where('ticket_id', $registration->ticket_type_id)->exists();
                
                $results[] = [
                    'registration_id' => $registration->id,
                    'user_name' => $registration->user->name,
                    'ticket_type' => $registration->ticketType->name ?? 'No ticket type',
                    'has_template' => $hasTemplate,
                    'can_print' => $hasTemplate
                ];
            }
            
            return response()->json([
                'success' => true,
                'results' => $results,
                'printable_count' => collect($results)->where('can_print', true)->count()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Badge template check error: ' . $e->getMessage());
            return response()->json(['error' => 'Error checking templates'], 500);
        }
    }
    
    /**
     * Generate QR codes for registrations that don't have them
     */
    public function generateMissingQrCodes(Request $request)
    {
        $request->validate([
            'registration_ids' => 'required|array',
            'registration_ids.*' => 'exists:registrations,id'
        ]);
        
        try {
            $registrations = Registration::whereIn('id', $request->registration_ids)
                                        ->whereDoesntHave('qrCode')
                                        ->get();
            
            $generated = 0;
            foreach ($registrations as $registration) {
                try {
                    $registration->generateQRCode();
                    $generated++;
                } catch (\Exception $e) {
                    Log::error('Failed to generate QR code for registration ' . $registration->id . ': ' . $e->getMessage());
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => "Generated {$generated} QR codes successfully",
                'generated' => $generated
            ]);
            
        } catch (\Exception $e) {
            Log::error('QR code generation error: ' . $e->getMessage());
            return response()->json(['error' => 'Error generating QR codes'], 500);
        }
    }
    
    /**
     * Prepare badge data from registration and badge contents
     */
    private function prepareBadgeData($registration, $badgeContents)
    {
        $badgeData = [];
        
        foreach ($badgeContents as $content) {
            if ($content->isQrCodeField()) {
                // Handle QR code field
                $badgeData[$content->field_name] = [
                    'type' => 'qr_code',
                    'value' => $registration->qrCode ? $registration->qrCode->getQrImageUrl() : null,
                    'registration_id' => $registration->id
                ];
            } else {
                // Handle text fields
                $badgeData[$content->field_name] = [
                    'type' => 'text',
                    'value' => $content->getFormattedFieldValue($registration)
                ];
            }
        }
        
        return $badgeData;
    }
    
    /**
     * Group registrations by their badge templates
     */
    private function groupRegistrationsByTemplate($registrations)
    {
        $badgeGroups = [];
        
        foreach ($registrations as $registration) {
            if (!$registration->ticket_type_id) continue;
            
            $badgeTemplate = BadgeTemplate::where('ticket_id', $registration->ticket_type_id)
                                         ->with('contents')
                                         ->first();
            
            if (!$badgeTemplate) continue;
            
            $templateId = $badgeTemplate->id;
            
            if (!isset($badgeGroups[$templateId])) {
                $badgeGroups[$templateId] = [
                    'template' => $badgeTemplate,
                    'registrations' => [],
                    'badge_data' => []
                ];
            }
            
            $badgeGroups[$templateId]['registrations'][] = $registration;
            $badgeGroups[$templateId]['badge_data'][] = $this->prepareBadgeData($registration, $badgeTemplate->contents);
        }
        
        return array_values($badgeGroups);
    }
    
    /**
     * Render error when no template is found
     */
    private function renderNoTemplateError($registration)
    {
        $eventName = $registration->event->name;
        
        $html = "
        <!DOCTYPE html>
        <html>
        <head>
            <title>Badge Template Not Found</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 40px; text-align: center; }
                .error-container { 
                    max-width: 600px; margin: 0 auto; padding: 30px; 
                    border: 2px solid #dc3545; border-radius: 10px; 
                    background-color: #f8f9fa; 
                }
                h2 { color: #dc3545; margin-bottom: 20px; }
                p { font-size: 16px; margin-bottom: 15px; }
                .btn { 
                    display: inline-block; padding: 10px 20px; 
                    background: #007bff; color: white; text-decoration: none; 
                    border-radius: 5px; margin-top: 20px; 
                }
                .btn:hover { background: #0056b3; }
            </style>
        </head>
        <body>
            <div class='error-container'>
                <h2>🚫 Badge Template Not Found</h2>
                <p>No badge template has been created for the event:</p>
                <p><strong>{$eventName}</strong></p>
                <p>Please contact the event administrator to create a badge template.</p>
                <a href='javascript:history.back()' class='btn'>Go Back</a>
            </div>
        </body>
        </html>";
        
        return response($html, 404);
    }
    
    /**
     * Render general print error
     */
    private function renderPrintError($message)
    {
        $html = "
        <!DOCTYPE html>
        <html>
        <head>
            <title>Badge Print Error</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 40px; text-align: center; }
                .error-container { 
                    max-width: 600px; margin: 0 auto; padding: 30px; 
                    border: 2px solid #dc3545; border-radius: 10px; 
                    background-color: #f8f9fa; 
                }
                h2 { color: #dc3545; margin-bottom: 20px; }
                .btn { 
                    display: inline-block; padding: 10px 20px; 
                    background: #007bff; color: white; text-decoration: none; 
                    border-radius: 5px; margin-top: 20px; 
                }
            </style>
        </head>
        <body>
            <div class='error-container'>
                <h2>⚠️ Error Generating Badge</h2>
                <p><strong>Error:</strong> {$message}</p>
                <a href='javascript:history.back()' class='btn'>Go Back</a>
            </div>
        </body>
        </html>";
        
        return response($html, 500);
    }
}