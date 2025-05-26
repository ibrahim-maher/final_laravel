<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use App\Models\Event;
use App\Models\User;
use App\Models\Ticket;
use App\Models\RegistrationField;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Log;

class RegistrationController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        // For simple setup, just check if user is authenticated
        // $this->authorize('viewAny', Registration::class);

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
        // $this->authorize('create', Registration::class);

        $events = Event::where('is_active', true)->orderBy('name')->get();
        $users = User::orderBy('name')->get();
        
        return view('registrations.create', compact('events', 'users'));
    }

    public function store(Request $request)
    {
        // $this->authorize('create', Registration::class);

        $request->validate([
            'event_id' => 'required|exists:events,id',
            'user_id' => 'nullable|exists:users,id',
            'ticket_type_id' => 'nullable|exists:tickets,id',
        ]);

        $event = Event::findOrFail($request->event_id);
        
        // Validate ticket belongs to event
        if ($request->ticket_type_id) {
            $ticket = Ticket::where('id', $request->ticket_type_id)
                           ->where('event_id', $event->id)
                           ->firstOrFail();
            
            // Check if ticket is available (you may need to implement this method)
            if (method_exists($ticket, 'canRegister') && !$ticket->canRegister()) {
                throw ValidationException::withMessages([
                    'ticket_type_id' => 'This ticket type is not available for registration.'
                ]);
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
                // Create or get user
                if ($request->user_id) {
                    $user = User::findOrFail($request->user_id);
                } else {
                    // Create new user from registration data
                    $user = $this->createUserFromRegistrationData($registrationData);
                }

                // Check if user is already registered for this event
                if (Registration::where('event_id', $event->id)
                               ->where('user_id', $user->id)
                               ->exists()) {
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

                // Generate QR code if method exists
                if (method_exists($registration, 'generateQRCode')) {
                    $registration->generateQRCode();
                }

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
            throw $e;
        } catch (\Exception $e) {
            Log::error('Registration creation failed: ' . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Registration failed. Please try again.'
                ], 500);
            }

            return back()->withErrors(['error' => 'Registration failed. Please try again.'])->withInput();
        }
    }

    public function show(Registration $registration)
    {
        // $this->authorize('view', $registration);

        $registration->load(['user', 'event', 'ticketType', 'qrCode']);
        
        return view('registrations.show', compact('registration'));
    }

    public function edit(Registration $registration)
    {
        // $this->authorize('update', $registration);

        $events = Event::orderBy('name')->get();
        $users = User::orderBy('name')->get();
        $tickets = Ticket::where('event_id', $registration->event_id)->get();
        
        return view('registrations.edit', compact('registration', 'events', 'users', 'tickets'));
    }

    public function update(Request $request, Registration $registration)
    {
        // $this->authorize('update', $registration);

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
        // $this->authorize('delete', $registration);

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

    public function getTickets(Event $event)
    {
        $tickets = $event->tickets()->get(); // Remove active() and available() if these scopes don't exist
        
        return response()->json($tickets->map(function ($ticket) {
            return [
                'id' => $ticket->id,
                'name' => $ticket->name,
                'price' => $ticket->price,
                'formatted_price' => number_format($ticket->price, 2),
                'available_spaces' => $ticket->available_spaces ?? null,
                'is_available' => true, // You can implement this logic
            ];
        }));
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
            // $this->authorize('update', Registration::class);

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
        // $this->authorize('viewAny', Registration::class);

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

    protected function createUserFromRegistrationData(array $registrationData)
    {
        // Try different common field names for email
        $email = $registrationData['Email'] ?? 
                 $registrationData['email'] ?? 
                 $registrationData['Email Address'] ?? 
                 $registrationData['email_address'] ?? null;
        
        $firstName = $registrationData['First Name'] ?? 
                     $registrationData['first_name'] ?? 
                     $registrationData['Name'] ?? '';
        
        $lastName = $registrationData['Last Name'] ?? 
                    $registrationData['last_name'] ?? '';
        
        $phone = $registrationData['Phone Number'] ?? 
                 $registrationData['phone'] ?? 
                 $registrationData['Phone'] ?? '';

        if (!$email) {
            throw ValidationException::withMessages(['email' => 'Email is required']);
        }

        // Check if user already exists
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $name = trim($firstName . ' ' . $lastName);
            if (empty($name)) {
                $name = explode('@', $email)[0]; // Use email prefix as fallback
            }

            $user = User::create([
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'password' => bcrypt(Str::random(12)), // Random password
                'role' => 'visitor',
                'email_verified_at' => now(), // Auto-verify for registration
            ]);
        }

        return $user;
    }
}