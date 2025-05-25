<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use App\Models\Event;
use App\Models\User;
use App\Models\Ticket;
use App\Models\QRCode;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RegistrationController extends Controller
{
    public function index()
    {
        $registrations = Registration::with(['user', 'event', 'ticketType'])
                                   ->when(request('search'), function($query) {
                                       $query->whereHas('user', function($q) {
                                           $q->where('name', 'like', '%' . request('search') . '%')
                                             ->orWhere('email', 'like', '%' . request('search') . '%');
                                       })
                                       ->orWhereHas('event', function($q) {
                                           $q->where('name', 'like', '%' . request('search') . '%');
                                       });
                                   })
                                   ->when(request('event_id'), function($query) {
                                       $query->where('event_id', request('event_id'));
                                   })
                                   ->orderBy('created_at', 'desc')
                                   ->paginate(15);
        
        $events = Event::orderBy('name')->get();
        return view('registrations.index', compact('registrations', 'events'));
    }

    public function create()
    {
        $events = Event::where('is_active', true)->orderBy('name')->get();
        $users = User::where('role', 'VISITOR')->orderBy('name')->get();
        return view('registrations.create', compact('events', 'users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'event_id' => 'required|exists:events,id',
            'user_id' => 'required|exists:users,id',
            'ticket_type_id' => 'nullable|exists:tickets,id',
            'registration_data' => 'nullable|json',
        ]);

        $registration = Registration::create($validated);
        
        // Generate QR Code
        $qrCode = QRCode::create([
            'registration_id' => $registration->id,
            'ticket_id' => $registration->ticket_type_id,
        ]);
        $qrCode->generateQRCode();

        return redirect()->route('registrations.index')->with('success', 'Registration created successfully');
    }

    public function show(Registration $registration)
    {
        $registration->load(['user', 'event', 'ticketType', 'qrCode', 'visitorLogs.creator']);
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
        $validated = $request->validate([
            'event_id' => 'required|exists:events,id',
            'user_id' => 'required|exists:users,id',
            'ticket_type_id' => 'nullable|exists:tickets,id',
            'registration_data' => 'nullable|json',
        ]);

        $registration->update($validated);
        return redirect()->route('registrations.index')->with('success', 'Registration updated successfully');
    }

    public function destroy(Registration $registration)
    {
        $registration->delete();
        return redirect()->route('registrations.index')->with('success', 'Registration deleted successfully');
    }

    public function getTickets(Event $event)
    {
        return response()->json($event->tickets);
    }
}