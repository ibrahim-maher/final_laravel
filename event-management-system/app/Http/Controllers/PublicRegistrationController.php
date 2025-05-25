<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\User;
use App\Models\Registration;
use App\Models\QRCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\RegistrationConfirmation;

class PublicRegistrationController extends Controller
{
    public function index()
    {
        $events = Event::where('is_active', true)
                      ->where('start_date', '>', now())
                      ->with(['venue', 'category', 'tickets'])
                      ->orderBy('start_date')
                      ->get();
        
        return view('public.events.index', compact('events'));
    }

    public function show(Event $event)
    {
        if (!$event->is_active || $event->start_date <= now()) {
            abort(404, 'Event not available for registration');
        }

        $event->load(['venue', 'category', 'tickets', 'registrationFields']);
        
        return view('public.events.show', compact('event'));
    }

    public function register(Event $event)
    {
        if (!$event->is_active || $event->start_date <= now()) {
            abort(404, 'Event not available for registration');
        }

        $event->load(['tickets', 'registrationFields']);
        
        return view('public.events.register', compact('event'));
    }

    public function store(Request $request, Event $event)
    {
        if (!$event->is_active || $event->start_date <= now()) {
            return redirect()->back()->with('error', 'Event not available for registration');
        }

        // Validate basic fields
        $rules = [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone_number' => 'required|string|max:15',
            'ticket_type_id' => 'required|exists:tickets,id',
        ];

        // Add dynamic field validation
        foreach ($event->registrationFields as $field) {
            if ($field->is_required) {
                $rules[$field->field_name] = 'required';
            }
        }

        $validated = $request->validate($rules);

        // Check if user already registered for this event
        $existingUser = User::where('email', $validated['email'])->first();
        if ($existingUser) {
            $existingRegistration = Registration::where('user_id', $existingUser->id)
                                               ->where('event_id', $event->id)
                                               ->first();
            if ($existingRegistration) {
                return redirect()->back()->with('error', 'You are already registered for this event');
            }
        }

        // Create or update user
        $user = User::updateOrCreate(
            ['email' => $validated['email']],
            [
                'name' => $validated['first_name'] . ' ' . $validated['last_name'],
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'phone_number' => $validated['phone_number'],
                'title' => $request->input('title'),
                'company' => $request->input('company'),
                'country' => $request->input('country'),
                'role' => 'VISITOR',
                'password' => $existingUser ? $existingUser->password : Hash::make(Str::random(12)),
            ]
        );

        // Prepare registration data
        $registrationData = [];
        foreach ($event->registrationFields as $field) {
            $registrationData[$field->field_name] = $request->input($field->field_name);
        }

        // Create registration
        $registration = Registration::create([
            'event_id' => $event->id,
            'user_id' => $user->id,
            'ticket_type_id' => $validated['ticket_type_id'],
            'registration_data' => $registrationData,
        ]);

        // Generate QR Code
        $qrCode = QRCode::create([
            'registration_id' => $registration->id,
            'ticket_id' => $registration->ticket_type_id,
        ]);
        $qrCode->generateQRCode();

        // Send confirmation email
        try {
            Mail::to($user->email)->send(new RegistrationConfirmation($registration));
        } catch (\Exception $e) {
            // Log email error but don't fail the registration
            logger()->error('Failed to send registration confirmation email: ' . $e->getMessage());
        }

        return redirect()->route('public.registration.success', $registration)
                        ->with('success', 'Registration successful! Check your email for confirmation.');
    }

    public function success(Registration $registration)
    {
        $registration->load(['user', 'event', 'ticketType', 'qrCode']);
        
        return view('public.events.success', compact('registration'));
    }

    public function downloadQR(Registration $registration)
    {
        if ($registration->user_id !== auth()->id() && !auth()->user()?->isAdmin()) {
            abort(403);
        }

        $qrCode = $registration->qrCode;
        
        if (!$qrCode || !$qrCode->qr_image) {
            abort(404, 'QR Code not found');
        }

        return response()->download(storage_path('app/public/' . $qrCode->qr_image));
    }
}