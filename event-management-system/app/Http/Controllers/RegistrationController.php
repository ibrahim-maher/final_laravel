<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use App\Models\Event;
use App\Models\User;
use App\Models\Ticket;
use App\Models\RegistrationField;
use App\Models\BadgeTemplate;
use App\Models\BadgeContent;
use App\Services\BadgePrintingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class RegistrationController extends Controller
{
    use AuthorizesRequests;

    protected $badgePrintingService;

    public function __construct(?BadgePrintingService $badgePrintingService = null)
    {
        $this->badgePrintingService = $badgePrintingService;
    }

    public function index(Request $request)
    {
        $query = Registration::with(['user', 'event', 'ticketType']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', "%{$search}%")
                             ->orWhere('email', 'like', "%{$search}%");
                })
                ->orWhereHas('event', function ($eventQuery) use ($search) {
                    $eventQuery->where('name', 'like', "%{$search}%");
                });
            });
        }

        // Event filter
        if ($request->filled('event_id')) {
            $query->where('event_id', $request->event_id);
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $registrations = $query->latest()->paginate(15);
        $events = Event::orderBy('name')->get();

        return view('registrations.index', compact('registrations', 'events'));
    }

    public function create()
    {
        $events = Event::where('is_active', true)->orderBy('name')->get();
        $users = User::orderBy('name')->get();
        
        return view('registrations.create', compact('events', 'users'));
    }

    public function store(Request $request)
    {
        // Basic validation first
        $request->validate([
            'event_id' => 'required|exists:events,id',
            'user_id' => 'nullable|exists:users,id',
            'ticket_type_id' => 'nullable|exists:tickets,id',
        ]);

        $event = Event::findOrFail($request->event_id);
        
        // Validate ticket belongs to event (simplified)
        if ($request->ticket_type_id) {
            $ticket = Ticket::where('id', $request->ticket_type_id)
                           ->where('event_id', $event->id)
                           ->first();
            
            if (!$ticket) {
                throw ValidationException::withMessages([
                    'ticket_type_id' => 'Selected ticket does not belong to this event.'
                ]);
            }
            
            // Simplified availability check
            if (!$ticket->is_active) {
                throw ValidationException::withMessages([
                    'ticket_type_id' => 'This ticket type is currently inactive.'
                ]);
            }
            
            // Check capacity if it exists and is set
            if (isset($ticket->capacity) && $ticket->capacity > 0) {
                $currentRegistrations = Registration::where('ticket_type_id', $ticket->id)
                                                   ->where('status', '!=', 'cancelled')
                                                   ->count();
                
                if ($currentRegistrations >= $ticket->capacity) {
                    throw ValidationException::withMessages([
                        'ticket_type_id' => 'This ticket type is fully booked.'
                    ]);
                }
            }
        }

        // Get dynamic fields for the event
        $registrationFields = $event->registrationFields()->ordered()->get();
        $registrationData = [];
        $validationRules = [];

        // Build validation rules for dynamic fields
        foreach ($registrationFields as $field) {
            $fieldKey = Str::slug($field->field_name, '_');
            
            // Build validation rules based on field type and requirements
            $rules = [];
            if ($field->is_required) {
                $rules[] = 'required';
            } else {
                $rules[] = 'nullable';
            }

            switch ($field->field_type) {
                case 'email':
                    $rules[] = 'email';
                    break;
                case 'number':
                    $rules[] = 'numeric';
                    break;
                case 'date':
                    $rules[] = 'date';
                    break;
                case 'url':
                    $rules[] = 'url';
                    break;
                case 'dropdown':
                case 'radio':
                    if (!empty($field->options_array)) {
                        $rules[] = 'in:' . implode(',', $field->options_array);
                    }
                    break;
                case 'checkbox':
                    if (!empty($field->options_array)) {
                        $rules[] = 'array';
                    } else {
                        $rules[] = 'nullable';
                    }
                    break;
                default:
                    if ($field->is_required) {
                        $rules[] = 'string|max:255';
                    } else {
                        $rules[] = 'nullable|string|max:255';
                    }
                    break;
            }

            $validationRules[$fieldKey] = implode('|', $rules);
        }

        // Validate dynamic fields
        if (!empty($validationRules)) {
            try {
                $request->validate($validationRules);
            } catch (ValidationException $e) {
                Log::error('Dynamic field validation failed', [
                    'rules' => $validationRules,
                    'input' => $request->all(),
                    'errors' => $e->errors()
                ]);
                throw $e;
            }
        }

        // Collect dynamic field data
        foreach ($registrationFields as $field) {
            $fieldKey = Str::slug($field->field_name, '_');
            $value = $request->input($fieldKey);
            
            // Handle different field types
            if ($field->field_type === 'checkbox' && is_array($value)) {
                $registrationData[$field->field_name] = $value;
            } elseif ($field->field_type === 'checkbox' && $value === '1') {
                $registrationData[$field->field_name] = true;
            } else {
                $registrationData[$field->field_name] = $value;
            }
        }

        try {
            DB::transaction(function () use ($request, $event, $registrationData) {
                // Create or get user
                if ($request->user_id) {
                    $user = User::findOrFail($request->user_id);
                } else {
                    // Create new user from registration data
                    $user = $this->createUserFromRegistrationData($registrationData, $request);
                }

                // Check if user is already registered for this event
                $existingRegistration = Registration::where('event_id', $event->id)
                                                    ->where('user_id', $user->id)
                                                    ->first();
                
                if ($existingRegistration) {
                    throw ValidationException::withMessages([
                        'user_id' => 'User is already registered for this event.'
                    ]);
                }

                // Create registration
                $registration = Registration::create([
                    'event_id' => $event->id,
                    'user_id' => $user->id,
                    'ticket_type_id' => $request->ticket_type_id,
                    'registration_data' => $registrationData,
                    'status' => 'confirmed',
                ]);

                session(['last_registration_id' => $registration->id]);
            });

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Registration created successfully',
                    'registration_id' => session('last_registration_id')
                ]);
            }

            return redirect()->route('registrations.index')
                            ->with('success', 'Registration created successfully');
                            
        } catch (ValidationException $e) {
            Log::error('Registration validation failed', [
                'errors' => $e->errors(),
                'input' => $request->all()
            ]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('Registration creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'input' => $request->all()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Registration failed: ' . $e->getMessage()
                ], 500);
            }

            return back()->withErrors(['error' => 'Registration failed: ' . $e->getMessage()])->withInput();
        }
    }

    public function show(Registration $registration)
    {
        $registration->load(['user', 'event', 'ticketType', 'qrCode']);
        
        return view('registrations.show', compact('registration'));
    }

    public function edit(Registration $registration)
    {
        $events = Event::orderBy('name')->get();
        $users = User::orderBy('name')->get();
        $tickets = Ticket::where('event_id', $registration->event_id)->get();
        
        return view('registrations.edit', compact('registration', 'events', 'users', 'tickets'));
    }

    public function update(Request $request, Registration $registration)
    {
        $request->validate([
            'event_id' => 'required|exists:events,id',
            'user_id' => 'required|exists:users,id',
            'ticket_type_id' => 'nullable|exists:tickets,id',
            'status' => 'required|in:confirmed,pending,cancelled',
        ]);

        $event = Event::findOrFail($request->event_id);
        
        // Validate ticket belongs to event
        if ($request->ticket_type_id) {
            Ticket::where('id', $request->ticket_type_id)
                  ->where('event_id', $event->id)
                  ->firstOrFail();
        }

        // Get dynamic fields for the event
        $registrationFields = $event->registrationFields()->ordered()->get();
        $registrationData = [];
        $validationRules = [];

        // Build validation rules for dynamic fields
        foreach ($registrationFields as $field) {
            $fieldKey = Str::slug($field->field_name, '_');
            
            $rules = [];
            if ($field->is_required) {
                $rules[] = 'required';
            } else {
                $rules[] = 'nullable';
            }

            switch ($field->field_type) {
                case 'email':
                    $rules[] = 'email';
                    break;
                case 'number':
                    $rules[] = 'numeric';
                    break;
                case 'date':
                    $rules[] = 'date';
                    break;
                case 'url':
                    $rules[] = 'url';
                    break;
                case 'dropdown':
                case 'radio':
                    if ($field->options_array) {
                        $rules[] = 'in:' . implode(',', $field->options_array);
                    }
                    break;
                case 'checkbox':
                    if ($field->options_array) {
                        $rules[] = 'array';
                    }
                    break;
                default:
                    $rules[] = 'string';
                    break;
            }

            $validationRules[$fieldKey] = implode('|', $rules);
        }

        // Validate dynamic fields
        if (!empty($validationRules)) {
            $request->validate($validationRules);
        }

        // Collect dynamic field data
        foreach ($registrationFields as $field) {
            $fieldKey = Str::slug($field->field_name, '_');
            $registrationData[$field->field_name] = $request->input($fieldKey);
        }

        $registration->update([
            'event_id' => $request->event_id,
            'user_id' => $request->user_id,
            'ticket_type_id' => $request->ticket_type_id,
            'registration_data' => $registrationData,
            'status' => $request->status,
        ]);

        return redirect()->route('registrations.index')
                        ->with('success', 'Registration updated successfully');
    }

    public function destroy(Registration $registration)
    {
        $registration->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Registration deleted successfully'
            ]);
        }

        return redirect()->route('registrations.index')
                        ->with('success', 'Registration deleted successfully');
    }

    public function getRegistrationFields(Event $event)
    {
        $fields = $event->registrationFields()->ordered()->get();
        
        return response()->json($fields->map(function ($field) {
            return [
                'field_name' => $field->field_name,
                'field_type' => $field->field_type,
                'is_required' => $field->is_required,
                'options' => $field->options_array,
                'field_key' => Str::slug($field->field_name, '_'),
            ];
        }));
    }

    public function bulkAction(Request $request)
    {
        try {
            $request->validate([
                'action' => 'required|in:confirm,cancel,delete',
                'registration_ids' => 'required|array',
                'registration_ids.*' => 'exists:registrations,id'
            ]);

            $registrations = Registration::whereIn('id', $request->registration_ids)->get();
            $count = 0;

            DB::transaction(function () use ($request, $registrations, &$count) {
                foreach ($registrations as $registration) {
                    switch ($request->action) {
                        case 'confirm':
                            $registration->update(['status' => 'confirmed']);
                            $count++;
                            break;
                        case 'cancel':
                            $registration->update(['status' => 'cancelled']);
                            $count++;
                            break;
                        case 'delete':
                            $registration->delete();
                            $count++;
                            break;
                    }
                }
            });

            $message = ucfirst($request->action) . "ed {$count} registrations successfully";

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'count' => $count
                ]);
            }

            return redirect()->route('registrations.index')->with('success', $message);

        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            Log::error('Bulk action failed: ' . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bulk action failed. Please try again.'
                ], 500);
            }

            return redirect()->route('registrations.index')
                           ->with('error', 'Bulk action failed. Please try again.');
        }
    }

    public function export(Request $request)
    {
        $query = Registration::with(['user', 'event', 'ticketType']);

        // Apply same filters as index
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', "%{$search}%")
                             ->orWhere('email', 'like', "%{$search}%");
                })
                ->orWhereHas('event', function ($eventQuery) use ($search) {
                    $eventQuery->where('name', 'like', "%{$search}%");
                });
            });
        }

        if ($request->filled('event_id')) {
            $query->where('event_id', $request->event_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $registrations = $query->get();

        // Get all unique registration data fields
        $allFields = collect();
        foreach ($registrations as $registration) {
            $allFields = $allFields->merge(array_keys($registration->registration_data ?? []));
        }
        $allFields = $allFields->unique()->sort();

        // Build CSV content
        $csvContent = "Name,Email,Event,Ticket Type,Status,Registration Date";
        foreach ($allFields as $field) {
            $csvContent .= "," . '"' . str_replace('"', '""', $field) . '"';
        }
        $csvContent .= "\n";

        foreach ($registrations as $registration) {
            $csvContent .= sprintf(
                '"%s","%s","%s","%s","%s","%s"',
                str_replace('"', '""', $registration->user->name ?? ''),
                str_replace('"', '""', $registration->user->email ?? ''),
                str_replace('"', '""', $registration->event->name ?? ''),
                str_replace('"', '""', $registration->ticketType->name ?? ''),
                str_replace('"', '""', ucfirst($registration->status)),
                $registration->created_at->format('Y-m-d H:i:s')
            );

            foreach ($allFields as $field) {
                $value = $registration->registration_data[$field] ?? '';
                if (is_array($value)) {
                    $value = implode(', ', $value);
                }
                $csvContent .= ',"' . str_replace('"', '""', $value) . '"';
            }
            $csvContent .= "\n";
        }

        return response($csvContent)
                ->header('Content-Type', 'text/csv')
                ->header('Content-Disposition', 'attachment; filename="registrations-' . date('Y-m-d') . '.csv"');
    }

    public function exportSelected(Request $request)
    {
        $request->validate([
            'registration_ids' => 'required|array',
            'registration_ids.*' => 'exists:registrations,id'
        ]);

        $registrations = Registration::with(['user', 'event', 'ticketType'])
                                    ->whereIn('id', $request->registration_ids)
                                    ->get();

        // Get all unique registration data fields
        $allFields = collect();
        foreach ($registrations as $registration) {
            $allFields = $allFields->merge(array_keys($registration->registration_data ?? []));
        }
        $allFields = $allFields->unique()->sort();

        // Build CSV content
        $csvContent = "Name,Email,Event,Ticket Type,Status,Registration Date";
        foreach ($allFields as $field) {
            $csvContent .= "," . '"' . str_replace('"', '""', $field) . '"';
        }
        $csvContent .= "\n";

        foreach ($registrations as $registration) {
            $csvContent .= sprintf(
                '"%s","%s","%s","%s","%s","%s"',
                str_replace('"', '""', $registration->user->name ?? ''),
                str_replace('"', '""', $registration->user->email ?? ''),
                str_replace('"', '""', $registration->event->name ?? ''),
                str_replace('"', '""', $registration->ticketType->name ?? ''),
                str_replace('"', '""', ucfirst($registration->status)),
                $registration->created_at->format('Y-m-d H:i:s')
            );

            foreach ($allFields as $field) {
                $value = $registration->registration_data[$field] ?? '';
                if (is_array($value)) {
                    $value = implode(', ', $value);
                }
                $csvContent .= ',"' . str_replace('"', '""', $value) . '"';
            }
            $csvContent .= "\n";
        }

        return response($csvContent)
                ->header('Content-Type', 'text/csv')
                ->header('Content-Disposition', 'attachment; filename="selected-registrations-' . date('Y-m-d') . '.csv"');
    }

    public function import(Request $request)
    {
        $request->validate([
            'event_id' => 'required|exists:events,id',
            'csv_file' => 'required|file|mimes:csv,txt|max:10240'
        ]);

        $event = Event::findOrFail($request->event_id);
        $file = $request->file('csv_file');
        
        $csvData = array_map('str_getcsv', file($file->getRealPath()));
        $headers = array_shift($csvData);
        
        $imported = 0;
        $failed = 0;
        $errors = [];

        DB::beginTransaction();
        
        try {
            foreach ($csvData as $row) {
                try {
                    if (count($row) < count($headers)) {
                        continue;
                    }
                    
                    $data = array_combine($headers, $row);
                    
                    // Extract basic fields
                    $name = $data['Name'] ?? $data['name'] ?? null;
                    $email = $data['Email'] ?? $data['email'] ?? null;
                    $ticketTypeName = $data['Ticket Type'] ?? $data['ticket_type'] ?? null;
                    
                    if (!$email) {
                        $failed++;
                        $errors[] = "Row missing email";
                        continue;
                    }
                    
                    // Find or create user
                    $user = User::firstOrCreate(
                        ['email' => $email],
                        [
                            'name' => $name ?: explode('@', $email)[0],
                            'password' => bcrypt(Str::random(12)),
                            'role' => 'visitor',
                            'email_verified_at' => now()
                        ]
                    );
                    
                    // Find ticket type if specified
                    $ticketTypeId = null;
                    if ($ticketTypeName) {
                        $ticket = Ticket::where('event_id', $event->id)
                                       ->where('name', $ticketTypeName)
                                       ->first();
                        if ($ticket) {
                            $ticketTypeId = $ticket->id;
                        }
                    }
                    
                    // Check if already registered
                    if (Registration::where('event_id', $event->id)
                                   ->where('user_id', $user->id)
                                   ->exists()) {
                        $failed++;
                        $errors[] = "User {$email} already registered";
                        continue;
                    }
                    
                    // Prepare registration data from remaining fields
                    $registrationData = [];
                    foreach ($data as $key => $value) {
                        if (!in_array($key, ['Name', 'name', 'Email', 'email', 'Ticket Type', 'ticket_type'])) {
                            $registrationData[$key] = $value;
                        }
                    }
                    
                    // Create registration
                    $registration = Registration::create([
                        'event_id' => $event->id,
                        'user_id' => $user->id,
                        'ticket_type_id' => $ticketTypeId,
                        'registration_data' => $registrationData,
                        'status' => 'confirmed'
                    ]);
                    
                    $imported++;
                    
                } catch (\Exception $e) {
                    $failed++;
                    $errors[] = "Error processing row: " . $e->getMessage();
                    Log::error('Import row failed: ' . $e->getMessage());
                }
            }
            
            DB::commit();
            
            $message = "Import completed: {$imported} successful, {$failed} failed.";
            if (!empty($errors)) {
                $message .= " Errors: " . implode('; ', array_slice($errors, 0, 5));
                if (count($errors) > 5) {
                    $message .= " (and " . (count($errors) - 5) . " more)";
                }
            }
            
            return redirect()->route('registrations.index')
                           ->with('success', $message);
                           
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Import failed: ' . $e->getMessage());
            return redirect()->route('registrations.index')
                           ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    public function publicRegister(Request $request)
    {
        $events = Event::where('is_active', true)
                       ->with(['registrationFields' => function ($query) {
                           $query->ordered();
                       }, 'tickets'])
                       ->get();

        if ($request->isMethod('post')) {
            return $this->processPublicRegistration($request);
        }

        return view('registrations.public-register', compact('events'));
    }

    protected function processPublicRegistration(Request $request)
    {
        $request->validate([
            'event_id' => 'required|exists:events,id',
            'ticket_type_id' => 'nullable|exists:tickets,id',
        ]);

        $event = Event::findOrFail($request->event_id);
        
        // Validate ticket belongs to event and is available
        if ($request->ticket_type_id) {
            $ticket = Ticket::where('id', $request->ticket_type_id)
                           ->where('event_id', $event->id)
                           ->firstOrFail();
            
            if (method_exists($ticket, 'canRegister') && !$ticket->canRegister()) {
                return back()->withErrors(['ticket_type_id' => 'This ticket type is not available for registration.']);
            }
        }

        // Get dynamic fields for the event
        $registrationFields = $event->registrationFields()->ordered()->get();
        $registrationData = [];
        $validationRules = [];

        // Build validation rules for dynamic fields
        foreach ($registrationFields as $field) {
            $fieldKey = Str::slug($field->field_name, '_');
            
            $rules = [];
            if ($field->is_required) {
                $rules[] = 'required';
            } else {
                $rules[] = 'nullable';
            }

            switch ($field->field_type) {
                case 'email':
                    $rules[] = 'email';
                    break;
                case 'number':
                    $rules[] = 'numeric';
                    break;
                case 'date':
                    $rules[] = 'date';
                    break;
                case 'url':
                    $rules[] = 'url';
                    break;
                case 'dropdown':
                case 'radio':
                    if ($field->options_array) {
                        $rules[] = 'in:' . implode(',', $field->options_array);
                    }
                    break;
                case 'checkbox':
                    if ($field->options_array) {
                        $rules[] = 'array';
                    }
                    break;
                default:
                    $rules[] = 'string';
                    break;
            }

            $validationRules[$fieldKey] = implode('|', $rules);
        }

        // Validate dynamic fields
        if (!empty($validationRules)) {
            $request->validate($validationRules);
        }

        // Collect dynamic field data
        foreach ($registrationFields as $field) {
            $fieldKey = Str::slug($field->field_name, '_');
            $registrationData[$field->field_name] = $request->input($fieldKey);
        }

        try {
            DB::transaction(function () use ($request, $event, $registrationData) {
                // Create user from registration data
                $user = $this->createUserFromRegistrationData($registrationData);

                // Check if user is already registered for this event
                if (Registration::where('event_id', $event->id)
                               ->where('user_id', $user->id)
                               ->exists()) {
                    throw ValidationException::withMessages([
                        'email' => 'You are already registered for this event.'
                    ]);
                }

                // Create registration
                $registration = Registration::create([
                    'event_id' => $event->id,
                    'user_id' => $user->id,
                    'ticket_type_id' => $request->ticket_type_id,
                    'registration_data' => $registrationData,
                    'status' => 'confirmed',
                ]);

                session(['last_registration_id' => $registration->id]);
            });

            return redirect()->route('registrations.success')
                           ->with('success', 'Registration completed successfully!');

        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Public registration failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Registration failed. Please try again.'])->withInput();
        }
    }

    public function registrationSuccess()
    {
        $registrationId = session('last_registration_id');
        if (!$registrationId) {
            return redirect()->route('registrations.public-register');
        }

        $registration = Registration::with(['user', 'event', 'ticketType', 'qrCode'])
                                   ->findOrFail($registrationId);

        return view('registrations.success', compact('registration'));
    }

    protected function createUserFromRegistrationData(array $registrationData, Request $request = null)
    {
        // Try different common field names for email
        $email = null;
        $possibleEmailFields = [
            'Email', 'email', 'Email Address', 'email_address', 
            'e_mail', 'e-mail', 'user_email', 'contact_email'
        ];
        
        foreach ($possibleEmailFields as $field) {
            if (!empty($registrationData[$field])) {
                $email = $registrationData[$field];
                break;
            }
        }
        
        // If not found in registration data, check request directly
        if (!$email && $request) {
            foreach ($possibleEmailFields as $field) {
                $fieldKey = Str::slug($field, '_');
                if ($request->filled($fieldKey)) {
                    $email = $request->input($fieldKey);
                    break;
                }
            }
        }
        
        if (!$email) {
            Log::error('Email not found in registration data', [
                'registration_data' => $registrationData,
                'request_data' => $request ? $request->all() : null
            ]);
            throw ValidationException::withMessages(['email' => 'Email is required for registration']);
        }

        // Try different common field names for name components
        $firstName = $registrationData['First Name'] ?? 
                     $registrationData['first_name'] ?? 
                     $registrationData['fname'] ?? 
                     $registrationData['given_name'] ?? '';
        
        $lastName = $registrationData['Last Name'] ?? 
                    $registrationData['last_name'] ?? 
                    $registrationData['lname'] ?? 
                    $registrationData['family_name'] ?? '';
        
        $fullName = $registrationData['Full Name'] ?? 
                    $registrationData['full_name'] ?? 
                    $registrationData['Name'] ?? 
                    $registrationData['name'] ?? '';
        
        $phone = $registrationData['Phone Number'] ?? 
                 $registrationData['phone_number'] ?? 
                 $registrationData['phone'] ?? 
                 $registrationData['Phone'] ?? 
                 $registrationData['mobile'] ?? 
                 $registrationData['Mobile'] ?? '';

        // Check if user already exists
        $user = User::where('email', $email)->first();
        
        if ($user) {
            return $user;
        }

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

        try {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'phone' => $phone ?: null,
                'password' => bcrypt(Str::random(12)), // Random password
                'role' => 'visitor',
                'email_verified_at' => now(), // Auto-verify for registration
            ]);
            
            Log::info('User created successfully', [
                'user_id' => $user->id,
                'email' => $email,
                'name' => $name
            ]);
            
            return $user;
        } catch (\Exception $e) {
            Log::error('User creation failed', [
                'error' => $e->getMessage(),
                'email' => $email,
                'name' => $name,
                'registration_data' => $registrationData
            ]);
            throw new \Exception('Failed to create user: ' . $e->getMessage());
        }
    }

    public function getTickets(Event $event)
    {
        $tickets = $event->tickets()->where('is_active', true)->get();
        
        return response()->json($tickets->map(function ($ticket) {
            // Calculate available spaces
            $currentRegistrations = Registration::where('ticket_type_id', $ticket->id)
                                               ->where('status', '!=', 'cancelled')
                                               ->count();
            
            $availableSpaces = null;
            $isAvailable = true;
            
            if ($ticket->capacity && $ticket->capacity > 0) {
                $availableSpaces = $ticket->capacity - $currentRegistrations;
                $isAvailable = $availableSpaces > 0;
            }
            
            return [
                'id' => $ticket->id,
                'name' => $ticket->name,
                'price' => (float) $ticket->price,
                'formatted_price' => number_format($ticket->price, 2),
                'capacity' => $ticket->capacity,
                'current_registrations' => $currentRegistrations,
                'available_spaces' => $availableSpaces,
                'is_available' => $isAvailable && $ticket->is_active,
                'description' => $ticket->description ?? '',
            ];
        }));
    }

    // ===== BADGE PRINTING METHODS =====

    /**
     * Print single badge
     */
    public function printBadge(Registration $registration)
    {
        try {
            // Use service if available, otherwise fallback
            if ($this->badgePrintingService) {
                $badgeData = $this->badgePrintingService->getBadgeData($registration);
                
                return view('badges.print-single', [
                    'registration' => $badgeData['registration'],
                    'badgeTemplate' => $badgeData['template'],
                    'badgeData' => $badgeData['badge_data']
                ]);
            } else {
                return $this->printBadgeFallback($registration);
            }
            
        } catch (\Exception $e) {
            Log::error('Print badge error', [
                'registration_id' => $registration->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'Failed to print badge: ' . $e->getMessage());
        }
    }

    /**
     * Preview single badge (AJAX)
     */
    public function previewBadge(Registration $registration)
    {
        try {
            // Use service if available, otherwise fallback
            if ($this->badgePrintingService) {
                $result = $this->badgePrintingService->generatePreviewHtml($registration);
                
                if (!$result['success']) {
                    return response()->json(['error' => $result['error']], 400);
                }
                
                return response()->json([
                    'html' => $result['html'],
                    'template' => $result['template']
                ]);
            } else {
                return $this->previewBadgeFallback($registration);
            }
            
        } catch (\Exception $e) {
            Log::error('Preview badge error', [
                'registration_id' => $registration->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to generate preview: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk print badges
     */
    public function bulkPrintBadges(Request $request)
    {
        try {
            $request->validate([
                'registration_ids' => 'required|array',
                'registration_ids.*' => 'exists:registrations,id'
            ]);
            
            // Use service if available, otherwise fallback
            if ($this->badgePrintingService) {
                $badgeGroups = $this->badgePrintingService->getBulkBadgeData($request->registration_ids);
                
                return view('badges.print-bulk', compact('badgeGroups'));
            } else {
                return $this->bulkPrintBadgesFallback($request);
            }
            
        } catch (\Exception $e) {
            Log::error('Bulk print badges error', [
                'registration_ids' => $request->registration_ids ?? [],
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'Failed to print badges: ' . $e->getMessage());
        }
    }

    /**
     * Generate missing QR codes
     */
    public function generateMissingQrCodes(Request $request)
    {
        try {
            Log::info('Generate QR codes request', [
                'request_data' => $request->all()
            ]);

            $request->validate([
                'registration_ids' => 'required|array',
                'registration_ids.*' => 'exists:registrations,id'
            ]);

            // Use service if available, otherwise fallback
            if ($this->badgePrintingService) {
                $result = $this->badgePrintingService->generateMissingQrCodes($request->registration_ids);
                return response()->json($result);
            } else {
                return $this->generateMissingQrCodesFallback($request);
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Generate QR codes failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate QR codes: ' . $e->getMessage()
            ], 500);
        }
    }

  
    // ===== FALLBACK METHODS (when service is not available) =====

    /**
     * Fallback method for printing single badge
     */
    protected function printBadgeFallback(Registration $registration)
    {
        $registration->load(['user', 'event', 'event.venue', 'ticketType', 'qrCode']);
        
        if (!$registration->ticket_type_id) {
            return back()->with('error', 'No ticket type assigned to this registration.');
        }
        
        $badgeTemplate = BadgeTemplate::where('ticket_id', $registration->ticket_type_id)
                                     ->with('contents')
                                     ->first();
        
        if (!$badgeTemplate) {
            return back()->with('error', 'No badge template configured for this ticket type.');
        }
        
        // Ensure QR code exists
        if (!$registration->qrCode && method_exists($registration, 'generateQRCode')) {
            try {
                $registration->generateQRCode();
                $registration->refresh();
            } catch (\Exception $e) {
                Log::error('Failed to generate QR code for registration ' . $registration->id);
            }
        }
        
        return view('registrations.print-badge', compact('registration', 'badgeTemplate'));
    }

    /**
     * Fallback method for badge preview
     */
    protected function previewBadgeFallback(Registration $registration)
    {
        $registration->load(['user', 'event', 'event.venue', 'ticketType', 'qrCode']);
        
        if (!$registration->ticket_type_id) {
            return response()->json([
                'success' => false,
                'error' => 'No ticket type assigned to this registration'
            ], 400);
        }
        
        $badgeTemplate = BadgeTemplate::where('ticket_id', $registration->ticket_type_id)
                                     ->with('contents')
                                     ->first();
        
        if (!$badgeTemplate) {
            return response()->json([
                'success' => false,
                'error' => 'No badge template found for this ticket type'
            ], 400);
        }

        // Basic preview generation
        $html = view('registrations.badge-preview', [
            'badgeTemplate' => $badgeTemplate,
            'registration' => $registration
        ])->render();
        
        return response()->json([
            'success' => true,
            'html' => $html,
            'template' => $badgeTemplate->toArray()
        ]);
    }

    /**
     * Fallback method for bulk badge printing
     */
    protected function bulkPrintBadgesFallback(Request $request)
{
    $registrations = Registration::with(['user', 'event', 'ticketType', 'qrCode'])
                                ->whereIn('id', $request->registration_ids)
                                ->whereNotNull('ticket_type_id')
                                ->get();
    
    if ($registrations->isEmpty()) {
        return back()->with('error', 'No valid registrations found for badge printing.');
    }
    
    // Group registrations by ticket type
    $groupedRegistrations = $registrations->groupBy('ticket_type_id');
    $badgeGroups = []; // Changed from $badgeData to $badgeGroups
    
    foreach ($groupedRegistrations as $ticketTypeId => $ticketRegistrations) {
        $badgeTemplate = BadgeTemplate::where('ticket_id', $ticketTypeId)
                                     ->with('contents')
                                     ->first();
        
        if ($badgeTemplate) {
            $registrationData = [];
            $badgeDataList = [];
            
            foreach ($ticketRegistrations as $registration) {
                // Generate QR code if missing
                if (!$registration->qrCode && method_exists($registration, 'generateQRCode')) {
                    try {
                        $registration->generateQRCode();
                        $registration->refresh();
                    } catch (\Exception $e) {
                        Log::error('Failed to generate QR code for registration ' . $registration->id . ': ' . $e->getMessage());
                    }
                }
                
                // Prepare badge data
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
    }
    
    if (empty($badgeGroups)) {
        return back()->with('error', 'No badge templates found for selected registrations.');
    }
    
    return view('badges.print-bulk', compact('badgeGroups')); // Changed view path
}

   protected function getFieldDataFromContent($content, $registration)
{
    // Handle QR code field
    if ($content->field_type === 'qr_code' || $content->field_name === 'qr_code') {
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
    
    // Handle text fields
    $value = '';
    
    // Get value based on field mapping
    switch ($content->field_name) {
        case 'name':
        case 'participant_name':
            $value = $registration->user->name ?? '';
            break;
        case 'email':
        case 'participant_email':
            $value = $registration->user->email ?? '';
            break;
        case 'event_name':
            $value = $registration->event->name ?? '';
            break;
        case 'ticket_type':
            $value = $registration->ticketType->name ?? '';
            break;
        case 'company':
        case 'organization':
            $value = $registration->registration_data['company'] ?? 
                    $registration->registration_data['organization'] ?? '';
            break;
        default:
            // Check in registration data
            if (isset($registration->registration_data[$content->field_name])) {
                $value = $registration->registration_data[$content->field_name];
            }
            break;
    }
    
    return [
        'type' => 'text',
        'value' => $value,
        'font_size' => $content->font_size ?? 12,
        'font_color' => $content->font_color ?? '#000000',
        'font_family' => $content->font_family ?? 'Arial, sans-serif',
        'is_bold' => $content->is_bold ?? false,
        'is_italic' => $content->is_italic ?? false
    ];
}

    /**
     * Fallback method for generating QR codes
     */
    protected function generateMissingQrCodesFallback(Request $request)
    {
        $registrations = Registration::whereIn('id', $request->registration_ids)
                                    ->whereDoesntHave('qrCode')
                                    ->get();

        if ($registrations->isEmpty()) {
            return response()->json([
                'success' => true,
                'generated' => 0,
                'failed' => 0,
                'errors' => [],
                'message' => 'No QR codes needed to be generated'
            ]);
        }

        $generated = 0;
        $failed = 0;
        $errors = [];

        foreach ($registrations as $registration) {
            try {
                if (method_exists($registration, 'generateQRCode')) {
                    $registration->generateQRCode();
                    $generated++;
                    Log::info("Generated QR code for registration {$registration->id}");
                } else {
                    $failed++;
                    $errors[] = "Registration {$registration->id}: generateQRCode method not available";
                }

            } catch (\Exception $e) {
                $failed++;
                $errorMsg = "Registration {$registration->id}: " . $e->getMessage();
                $errors[] = $errorMsg;
                Log::error('Failed to generate QR code: ' . $errorMsg);
            }
        }

        $message = "Generated {$generated} QR codes";
        if ($failed > 0) {
            $message .= ", {$failed} failed";
        }

        return response()->json([
            'success' => true,
            'generated' => $generated,
            'failed' => $failed,
            'errors' => $errors,
            'message' => $message
        ]);
    }

 public function checkBadgeTemplates(Request $request)
{
    try {
        // Log the request for debugging
        Log::info('Badge template check request', [
            'registration_ids' => $request->registration_ids,
            'request_all' => $request->all()
        ]);

        // Validate request
        $request->validate([
            'registration_ids' => 'required|array',
            'registration_ids.*' => 'exists:registrations,id'
        ]);

        // Get registrations with all necessary relationships
        $registrations = Registration::with(['user', 'event', 'ticketType', 'qrCode'])
                                    ->whereIn('id', $request->registration_ids)
                                    ->get();

        if ($registrations->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No registrations found',
                'results' => [],
                'printable_count' => 0,
                'total_count' => 0
            ]);
        }

        $results = [];
        
        foreach ($registrations as $registration) {
            try {
                $hasTemplate = false;
                $hasQrCode = false;
                $templateInfo = null;
                $issues = [];
                
                // Check if registration has a ticket type
                if (!$registration->ticket_type_id) {
                    $issues[] = 'No ticket type assigned';
                } else {
                    // Check for badge template
                    $template = BadgeTemplate::where('ticket_id', $registration->ticket_type_id)->first();
                    if ($template) {
                        $hasTemplate = true;
                        $templateInfo = [
                            'id' => $template->id,
                            'name' => $template->name ?? 'Unnamed Template',
                            'contents_count' => BadgeContent::where('badge_template_id', $template->id)->count()
                        ];
                    } else {
                        $issues[] = 'No badge template configured';
                    }
                }
                
                // Check for QR code
                if ($registration->qrCode && !empty($registration->qrCode->qr_image)) {
                    $hasQrCode = true;
                } else {
                    $issues[] = 'No QR code generated';
                }
                
                $canPrint = $hasTemplate && $hasQrCode;
                
                $result = [
                    'registration_id' => $registration->id,
                    'user_name' => $registration->user->name ?? 'N/A',
                    'user_email' => $registration->user->email ?? 'N/A',
                    'event_name' => $registration->event->name ?? 'N/A',
                    'ticket_type' => $registration->ticketType->name ?? 'No ticket type',
                    'ticket_type_id' => $registration->ticket_type_id,
                    'has_template' => $hasTemplate,
                    'has_qr_code' => $hasQrCode,
                    'can_print' => $canPrint,
                    'issues' => $issues,
                    'template_info' => $templateInfo
                ];
                
                $results[] = $result;
                
            } catch (\Exception $e) {
                Log::error("Error processing registration {$registration->id}", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                // Add error result
                $results[] = [
                    'registration_id' => $registration->id,
                    'user_name' => $registration->user->name ?? 'N/A',
                    'user_email' => $registration->user->email ?? 'N/A',
                    'event_name' => 'Error',
                    'ticket_type' => 'Error',
                    'ticket_type_id' => $registration->ticket_type_id,
                    'has_template' => false,
                    'has_qr_code' => false,
                    'can_print' => false,
                    'issues' => ['Error processing: ' . $e->getMessage()],
                    'template_info' => null
                ];
            }
        }
        
        $printableCount = collect($results)->where('can_print', true)->count();
        
        Log::info('Badge template check completed', [
            'total_checked' => count($results),
            'printable_count' => $printableCount
        ]);
        
        return response()->json([
            'success' => true,
            'results' => $results,
            'printable_count' => $printableCount,
            'total_count' => count($results)
        ]);
        
    } catch (\Illuminate\Validation\ValidationException $e) {
        Log::error('Validation error in checkBadgeTemplates', [
            'errors' => $e->errors()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $e->errors()
        ], 422);
        
    } catch (\Exception $e) {
        Log::error('Error in checkBadgeTemplates', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Server error occurred',
            'error' => $e->getMessage()
        ], 500);
    }
}
}