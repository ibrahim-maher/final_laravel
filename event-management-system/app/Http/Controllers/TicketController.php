<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Event;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function index()
    {
        $tickets = Ticket::with(['event', 'creator'])
                        ->when(request('search'), function($query) {
                            $query->where('name', 'like', '%' . request('search') . '%')
                                  ->orWhereHas('event', function($q) {
                                      $q->where('name', 'like', '%' . request('search') . '%');
                                  });
                        })
                        ->orderBy('created_at', 'desc')
                        ->paginate(15);
        
        return view('tickets.index', compact('tickets'));
    }

    public function create()
    {
        $events = Event::orderBy('name')->get();
        return view('tickets.create', compact('events'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'event_id' => 'required|exists:events,id',
            'name' => 'required|string|max:50',
            'price' => 'required|numeric|min:0',
            'capacity' => 'nullable|integer|min:1',
        ]);

        $validated['created_by'] = auth()->id();
        
        Ticket::create($validated);
        return redirect()->route('tickets.index')->with('success', 'Ticket created successfully');
    }

    public function show(Ticket $ticket)
    {
        $ticket->load(['event', 'creator', 'registrations.user']);
        return view('tickets.show', compact('ticket'));
    }

    public function edit(Ticket $ticket)
    {
        $events = Event::orderBy('name')->get();
        return view('tickets.edit', compact('ticket', 'events'));
    }

    public function update(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'event_id' => 'required|exists:events,id',
            'name' => 'required|string|max:50',
            'price' => 'required|numeric|min:0',
            'capacity' => 'nullable|integer|min:1',
        ]);

        $ticket->update($validated);
        return redirect()->route('tickets.index')->with('success', 'Ticket updated successfully');
    }

    public function destroy(Ticket $ticket)
    {
        $ticket->delete();
        return redirect()->route('tickets.index')->with('success', 'Ticket deleted successfully');
    }
}