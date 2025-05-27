<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\User;
use App\Models\Registration;
use App\Models\Ticket;
use App\Models\QRCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class PublicRegistrationController extends Controller
{
    /**
     * Display the main events page with registration forms
     */
    public function index()
    {
        // Get events that are active and have active tickets
        $events = Event::where('is_active', true)
                      ->whereHas('tickets', function($query) {
                          $query->where('is_active', true);
                      })
                      ->with([
                          'venue', 
                          'category', 
                          'tickets' => function($query) {
                              $query->where('is_active', true)->orderBy('price');
                          },
                          'registrationFields' => function($query) {
                              $query->orderBy('order');
                          },
                          'registrations'
                      ])
                      ->orderBy('start_date')
                      ->get();

        // Filter events to show only future events (with some buffer time)
        $availableEvents = $events->filter(function($event) {
            return $event->start_date->gt(now()->addMinutes(30));
        });

        // Debug logging
        Log::info('Events loaded for index', [
            'total_events' => Event::count(),
            'active_events' => Event::where('is_active', true)->count(),
            'events_with_tickets' => $events->count(),
            'available_events' => $availableEvents->count(),
            'current_time' => now()->toDateTimeString(),
        ]);
        
        return view('public.events.index', compact('availableEvents'));
    }

    /**
     * Handle registration form submission
     */
    public function store(Request $request, Event $event)
    {
        // Check if event is available for registration
        if (!$event->is_active) {
            return redirect()->route('home')
                            ->with('error', 'Event is not active for registration');
        }

        if ($event->start_date->lt(now()->addMinutes(30))) {
            return redirect()->route('home')
                            ->with('error', 'Event registration has closed');
        }

        // Basic validation
        $rules = [
            'ticket_type_id' => 'required|exists:tickets,id',
        ];

        // Load registration fields for validation
        $event->load(['registrationFields']);

        // Add dynamic field validation
        foreach ($event->registrationFields as $field) {
            $fieldKey = Str::slug($field->field_name, '_');
            
            $fieldRules = [];
            if ($field->is_required) {
                $fieldRules[] = 'required';
            } else {
                $fieldRules[] = 'nullable';
            }

            switch ($field->field_type) {
                case 'email':
                    $fieldRules[] = 'email|max:255';
                    break;
                case 'number':
                    $fieldRules[] = 'numeric';
                    break;
                case 'date':
                    $fieldRules[] = 'date';
                    break;
                case 'url':
                    $fieldRules[] = 'url|max:255';
                    break;
                case 'phone':
                    $fieldRules[] = 'string|max:20';
                    break;
                case 'dropdown':
                case 'radio':
                    if (!empty($field->options_array)) {
                        $fieldRules[] = 'in:' . implode(',', $field->options_array);
                    }
                    break;
                case 'checkbox':
                    if (!empty($field->options_array)) {
                        $fieldRules[] = 'array';
                    } else {
                        $fieldRules[] = 'nullable';
                    }
                    break;
                case 'textarea':
                    $fieldRules[] = 'string|max:1000';
                    break;
                default:
                    $fieldRules[] = 'string|max:500';
                    break;
            }

            $rules[$fieldKey] = implode('|', $fieldRules);
        }

        try {
            $validated = $request->validate($rules);
        } catch (ValidationException $e) {
            return redirect()->route('home')
                ->withErrors($e->errors())
                ->withInput()
                ->with('error', 'Please correct the highlighted fields and try again.')
                ->withFragment('event-' . $event->id);
        }

        // Validate ticket belongs to event and is available
        $ticket = Ticket::where('id', $validated['ticket_type_id'])
                       ->where('event_id', $event->id)
                       ->where('is_active', true)
                       ->first();

        if (!$ticket) {
            return redirect()->route('home')
                ->with('error', 'Selected ticket is not available')
                ->withInput()
                ->withFragment('event-' . $event->id);
        }

        // Check ticket capacity if it exists
        if (isset($ticket->capacity) && $ticket->capacity > 0) {
            $currentRegistrations = Registration::where('ticket_type_id', $ticket->id)
                                              ->where('status', '!=', 'cancelled')
                                              ->count();
            
            if ($currentRegistrations >= $ticket->capacity) {
                return redirect()->route('home')
                    ->with('error', 'This ticket type is sold out')
                    ->withInput()
                    ->withFragment('event-' . $event->id);
            }
        }

        // Extract email from registration data
        $email = $this->extractEmailFromFormData($validated);

        if (!$email) {
            return redirect()->route('home')
                ->with('error', 'Email address is required for registration')
                ->withInput()
                ->withFragment('event-' . $event->id);
        }

        // Check if user already registered for this event
        $existingUser = User::where('email', $email)->first();
        if ($existingUser) {
            $existingRegistration = Registration::where('user_id', $existingUser->id)
                                               ->where('event_id', $event->id)
                                               ->first();
            if ($existingRegistration) {
                return redirect()->route('home')
                    ->with('error', 'You are already registered for this event')
                    ->withInput()
                    ->withFragment('event-' . $event->id);
            }
        }

        try {
            DB::transaction(function () use ($event, $validated, $email) {
                // Prepare registration data
                $registrationData = $this->prepareRegistrationData($event, $validated);

                // Create or update user
                $user = $this->createUserFromRegistrationData($registrationData, $email);

                // Create registration
                $registration = Registration::create([
                    'event_id' => $event->id,
                    'user_id' => $user->id,
                    'ticket_type_id' => $validated['ticket_type_id'],
                    'registration_data' => $registrationData,
                    'status' => 'confirmed',
                ]);

                // Generate QR Code if possible
                $this->generateQRCode($registration);

                // Send confirmation email if possible
                $this->sendConfirmationEmail($registration);

                session(['last_registration_id' => $registration->id]);
            });

            return redirect()->route('public.registration.success', session('last_registration_id'))
                            ->with('success', 'Registration successful! Check your email for confirmation.');

        } catch (\Exception $e) {
            Log::error('Registration failed', [
                'event_id' => $event->id,
                'email' => $email,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->route('home')
                ->with('error', 'Registration failed. Please try again.')
                ->withInput()
                ->withFragment('event-' . $event->id);
        }
    }

    /**
     * Show registration success page
     */
    public function success(Registration $registration)
    {
        $registration->load(['user', 'event', 'ticketType', 'qrCode']);
        
        return view('public.events.success', compact('registration'));
    }

    /**
     * Download QR code
     */
    public function downloadQR(Registration $registration)
    {
        if (!$registration->qrCode || !$registration->qrCode->qr_image) {
            abort(404, 'QR Code not found');
        }

        $filePath = storage_path('app/public/' . $registration->qrCode->qr_image);
        
        if (!file_exists($filePath)) {
            abort(404, 'QR Code file not found');
        }

        $fileName = 'ticket_' . $registration->id . '_' . Str::slug($registration->event->name) . '.png';
        
        return response()->download($filePath, $fileName);
    }

    /**
     * Debug method to check events
     */
    public function debug()
    {
        $now = now();
        
        $debug = [
            'current_time' => $now->toDateTimeString(),
            'timezone' => config('app.timezone'),
            'total_events' => Event::count(),
            'active_events' => Event::where('is_active', true)->count(),
            'events_with_tickets' => Event::whereHas('tickets', function($q) {
                $q->where('is_active', true);
            })->count(),
            'sample_events' => Event::where('is_active', true)
                ->with(['tickets'])
                ->limit(10)
                ->get()
                ->map(function($event) use ($now) {
                    return [
                        'id' => $event->id,
                        'name' => $event->name,
                        'is_active' => $event->is_active,
                        'start_date' => $event->start_date->toDateTimeString(),
                        'is_future' => $event->start_date->gt($now),
                        'minutes_from_now' => $event->start_date->diffInMinutes($now, false),
                        'tickets_count' => $event->tickets->count(),
                        'active_tickets_count' => $event->tickets->where('is_active', true)->count(),
                    ];
                })
        ];

        return response()->json($debug, JSON_PRETTY_PRINT);
    }

    /**
     * Fix event times to be in the future
     */
    public function fixEventTimes()
    {
        $fixed = Event::where('is_active', true)
                     ->where('start_date', '<', now()->addHours(2))
                     ->update([
                         'start_date' => now()->addDays(7),
                         'end_date' => now()->addDays(7)->addHours(3)
                     ]);

        return response()->json([
            'message' => "Fixed {$fixed} events to have future dates",
            'fixed_count' => $fixed
        ]);
    }

    /**
     * Extract email from form data
     */
    private function extractEmailFromFormData(array $validated): ?string
    {
        $possibleEmailFields = [
            'email', 'Email', 'email_address', 'Email_Address', 
            'e_mail', 'E_Mail', 'user_email', 'User_Email',
            'contact_email', 'Contact_Email'
        ];
        
        foreach ($possibleEmailFields as $emailField) {
            $fieldKey = Str::slug($emailField, '_');
            if (!empty($validated[$fieldKey])) {
                return $validated[$fieldKey];
            }
        }

        return null;
    }

    /**
     * Prepare registration data from form submission
     */
    private function prepareRegistrationData(Event $event, array $validated): array
    {
        $registrationData = [];
        
        foreach ($event->registrationFields as $field) {
            $fieldKey = Str::slug($field->field_name, '_');
            $value = $validated[$fieldKey] ?? null;
            
            // Handle different field types
            if ($field->field_type === 'checkbox' && is_array($value)) {
                $registrationData[$field->field_name] = $value;
            } elseif ($field->field_type === 'checkbox' && $value === '1') {
                $registrationData[$field->field_name] = true;
            } else {
                $registrationData[$field->field_name] = $value;
            }
        }

        return $registrationData;
    }

    /**
     * Create or find user from registration data
     */
    private function createUserFromRegistrationData(array $registrationData, string $email): User
    {
        // Check if user already exists
        $user = User::where('email', $email)->first();
        
        if ($user) {
            return $user;
        }

        // Extract name fields
        $firstName = $registrationData['First Name'] ?? 
                     $registrationData['first_name'] ?? 
                     $registrationData['fname'] ?? '';
        
        $lastName = $registrationData['Last Name'] ?? 
                    $registrationData['last_name'] ?? 
                    $registrationData['lname'] ?? '';
        
        $fullName = $registrationData['Full Name'] ?? 
                    $registrationData['name'] ?? 
                    $registrationData['Name'] ?? '';
        
        $phone = $registrationData['Phone Number'] ?? 
                 $registrationData['phone'] ?? 
                 $registrationData['Phone'] ?? '';

        // Determine the name to use
        $name = '';
        if (!empty($fullName)) {
            $name = $fullName;
        } elseif (!empty($firstName) || !empty($lastName)) {
            $name = trim($firstName . ' ' . $lastName);
        }
        
        // Fallback to email prefix if no name found
        if (empty($name)) {
            $name = explode('@', $email)[0];
            $name = ucfirst(str_replace(['.', '_', '-'], ' ', $name));
        }

        return User::create([
            'name' => $name,
            'email' => $email,
            'phone' => $phone ?: null,
            'password' => Hash::make(Str::random(12)),
            'role' => 'visitor',
            'email_verified_at' => now(),
        ]);
    }

    /**
     * Generate QR code for registration
     */
    private function generateQRCode(Registration $registration): void
    {
        try {
            // Check if QRCode model exists and create QR code
            if (class_exists(QRCode::class)) {
                $qrCode = QRCode::create([
                    'registration_id' => $registration->id,
                    'ticket_id' => $registration->ticket_type_id,
                ]);
                
                // Try to generate QR code if method exists
                if (method_exists($qrCode, 'generateQRCode')) {
                    $qrCode->generateQRCode();
                }
            }
        } catch (\Exception $e) {
            Log::warning('QR Code generation failed: ' . $e->getMessage());
        }
    }

    /**
     * Send confirmation email
     */
    private function sendConfirmationEmail(Registration $registration): void
    {
        try {
            // Check if RegistrationConfirmation mailable exists
            if (class_exists('App\Mail\RegistrationConfirmation')) {
                Mail::to($registration->user->email)->send(new \App\Mail\RegistrationConfirmation($registration));
            }
        } catch (\Exception $e) {
            Log::warning('Confirmation email failed: ' . $e->getMessage());
        }
    }
}