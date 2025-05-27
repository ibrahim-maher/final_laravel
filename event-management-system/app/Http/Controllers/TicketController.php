<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $query = Ticket::with(['event', 'creator', 'registrations']);
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%')
                  ->orWhereHas('event', function($eventQuery) use ($search) {
                      $eventQuery->where('name', 'like', '%' . $search . '%');
                  });
            });
        }
        
        // Filter by event
        if ($request->filled('event_id')) {
            $query->where('event_id', $request->event_id);
        }
        
        // Filter by status
        if ($request->filled('status')) {
            $isActive = $request->status === 'active';
            $query->where('is_active', $isActive);
        }
        
        $tickets = $query->orderBy('created_at', 'desc')->paginate(15);
        
        return view('tickets.index', data: compact('tickets'));
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
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'price' => 'required|numeric|min:0',
            'capacity' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $validated['created_by'] = auth()->id();
        $validated['is_active'] = $request->has('is_active');
        
        $ticket = Ticket::create($validated);
        
        return redirect()->route('tickets.index')
                        ->with('success', 'Ticket created successfully');
    }

    public function show(Ticket $ticket)
    {
        $ticket->load(['event', 'creator', 'registrations.user']);
        return view('tickets.show', compact('ticket'));
    }

    public function edit(Ticket $ticket)
    {
        $events = Event::orderBy('name')->get();
        $ticket->load(['event', 'registrations']);
        return view('tickets.edit', compact('ticket', 'events'));
    }

    public function update(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'event_id' => 'required|exists:events,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'price' => 'required|numeric|min:0',
            'capacity' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $ticket->update($validated);
        
        // Check if this is a "save and continue" request
        if ($request->has('save_and_continue')) {
            return redirect()->route('tickets.edit', $ticket)
                            ->with('success', 'Ticket updated successfully. Continue editing.');
        }
        
        return redirect()->route('tickets.index')
                        ->with('success', 'Ticket updated successfully');
    }

    public function destroy(Ticket $ticket)
    {
        try {
            // Check if ticket has registrations
            if ($ticket->registrations()->count() > 0) {
                return redirect()->route('tickets.index')
                                ->with('error', 'Cannot delete ticket with existing registrations. Please cancel or transfer registrations first.');
            }
            
            $ticket->delete();
            
            return redirect()->route('tickets.index')
                            ->with('success', 'Ticket deleted successfully');
        } catch (\Exception $e) {
            Log::error('Failed to delete ticket: ' . $e->getMessage());
            return redirect()->route('tickets.index')
                            ->with('error', 'Failed to delete ticket. Please try again.');
        }
    }

    /**
     * Toggle ticket active status
     */
    public function toggleStatus(Ticket $ticket)
    {
        $ticket->update(['is_active' => !$ticket->is_active]);
        
        $status = $ticket->is_active ? 'activated' : 'deactivated';
        
        return redirect()->back()
                        ->with('success', "Ticket {$status} successfully");
    }

    /**
     * Duplicate a ticket
     */
    public function duplicate(Ticket $ticket)
    {
        $newTicket = $ticket->replicate();
        $newTicket->name = $ticket->name . ' (Copy)';
        $newTicket->created_by = auth()->id();
        $newTicket->save();
        
        return redirect()->route('tickets.edit', $newTicket)
                        ->with('success', 'Ticket duplicated successfully');
    }

    /**
     * Bulk actions for tickets
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'ticket_ids' => 'required|array',
            'ticket_ids.*' => 'exists:tickets,id'
        ]);

        $tickets = Ticket::whereIn('id', $request->ticket_ids)->get();
        $count = 0;

        try {
            DB::transaction(function () use ($request, $tickets, &$count) {
                foreach ($tickets as $ticket) {
                    switch ($request->action) {
                        case 'activate':
                            $ticket->update(['is_active' => true]);
                            $count++;
                            break;
                        case 'deactivate':
                            $ticket->update(['is_active' => false]);
                            $count++;
                            break;
                        case 'delete':
                            if ($ticket->registrations()->count() == 0) {
                                $ticket->delete();
                                $count++;
                            }
                            break;
                    }
                }
            });

            $message = ucfirst($request->action) . "d {$count} tickets successfully";
            
            if ($request->action === 'delete' && $count < count($request->ticket_ids)) {
                $skipped = count($request->ticket_ids) - $count;
                $message .= ". {$skipped} tickets with registrations were skipped.";
            }

            return redirect()->route('tickets.index')->with('success', $message);

        } catch (\Exception $e) {
            Log::error('Bulk action failed: ' . $e->getMessage());
            return redirect()->route('tickets.index')
                           ->with('error', 'Bulk action failed. Please try again.');
        }
    }

    /**
     * Get tickets for a specific event (AJAX)
     */
    public function getTicketsForEvent(Event $event)
    {
        $tickets = $event->tickets()->active()->with('registrations')->get();
        
        return response()->json($tickets->map(function ($ticket) {
            return [
                'id' => $ticket->id,
                'name' => $ticket->name,
                'description' => $ticket->description,
                'price' => $ticket->price,
                'formatted_price' => $ticket->formatted_price,
                'capacity' => $ticket->capacity,
                'available_spaces' => $ticket->available_spaces,
                'is_available' => $ticket->is_available,
                'can_register' => $ticket->canRegister(),
                'registration_count' => $ticket->registration_count,
            ];
        }));
    }

    /**
     * Check ticket availability (AJAX)
     */
    public function checkAvailability(Ticket $ticket)
    {
        return response()->json([
            'is_available' => $ticket->is_available,
            'can_register' => $ticket->canRegister(),
            'available_spaces' => $ticket->available_spaces,
            'registration_count' => $ticket->registration_count,
            'is_active' => $ticket->is_active,
        ]);
    }

    /**
     * Get ticket statistics (AJAX)
     */
    public function getStats(Ticket $ticket)
    {
        $registrations = $ticket->registrations();
        
        return response()->json([
            'total_sold' => $registrations->count(),
            'confirmed' => $registrations->where('status', 'confirmed')->count(),
            'pending' => $registrations->where('status', 'pending')->count(),
            'cancelled' => $registrations->where('status', 'cancelled')->count(),
            'total_revenue' => $registrations->where('status', 'confirmed')->count() * $ticket->price,
            'available_spaces' => $ticket->available_spaces,
            'capacity_percentage' => $ticket->getRegistrationPercentage(),
        ]);
    }

    /**
     * Update ticket status via AJAX
     */
    public function updateStatus(Request $request, Ticket $ticket)
    {
        $request->validate([
            'is_active' => 'required|boolean'
        ]);

        $ticket->update(['is_active' => $request->is_active]);

        return response()->json([
            'success' => true,
            'message' => 'Ticket status updated successfully',
            'is_active' => $ticket->is_active,
            'status_text' => $ticket->is_active ? 'Active' : 'Inactive'
        ]);
    }

    /**
     * Show analytics for a ticket
     */
    public function analytics(Ticket $ticket)
    {
        $ticket->load(['registrations', 'event']);
        
        // Generate analytics data
        $analytics = [
            'total_registrations' => $ticket->registrations->count(),
            'confirmed_registrations' => $ticket->registrations->where('status', 'confirmed')->count(),
            'total_revenue' => $ticket->registrations->where('status', 'confirmed')->count() * $ticket->price,
            'registration_trend' => $this->getRegistrationTrend($ticket),
            'daily_registrations' => $this->getDailyRegistrations($ticket),
        ];
        
        return view('tickets.analytics', compact('ticket', 'analytics'));
    }

    /**
     * Show registrations for a ticket
     */
    public function registrations(Ticket $ticket)
    {
        $registrations = $ticket->registrations()
                               ->with(['user', 'event'])
                               ->latest()
                               ->paginate(20);
        
        return view('tickets.registrations', compact('ticket', 'registrations'));
    }

    /**
     * Export tickets
     */
    public function export(Request $request)
    {
        $query = Ticket::with(['event', 'registrations']);
        
        // Apply filters if provided
        if ($request->filled('event_id')) {
            $query->where('event_id', $request->event_id);
        }
        
        if ($request->filled('status')) {
            $isActive = $request->status === 'active';
            $query->where('is_active', $isActive);
        }
        
        $tickets = $query->get();
        
        // Generate CSV content
        $csvContent = "Name,Event,Description,Price,Capacity,Sold,Revenue,Status,Created\n";
        
        foreach ($tickets as $ticket) {
            $csvContent .= sprintf(
                '"%s","%s","%s","%s","%s","%s","%s","%s","%s"' . "\n",
                str_replace('"', '""', $ticket->name),
                str_replace('"', '""', $ticket->event->name),
                str_replace('"', '""', $ticket->description ?? ''),
                $ticket->price,
                $ticket->capacity ?? 'Unlimited',
                $ticket->registrations->count(),
                $ticket->registrations->where('status', 'confirmed')->count() * $ticket->price,
                $ticket->is_active ? 'Active' : 'Inactive',
                $ticket->created_at->format('Y-m-d H:i:s')
            );
        }
        
        return response($csvContent)
                ->header('Content-Type', 'text/csv')
                ->header('Content-Disposition', 'attachment; filename="tickets-' . date('Y-m-d') . '.csv"');
    }

    /**
     * Helper method to get registration trend
     */
    private function getRegistrationTrend(Ticket $ticket)
    {
        // Implementation for registration trend analysis
        return $ticket->registrations()
                     ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                     ->groupBy('date')
                     ->orderBy('date')
                     ->get();
    }

    /**
     * Helper method to get daily registrations
     */
    private function getDailyRegistrations(Ticket $ticket)
    {
        // Implementation for daily registration statistics
        return $ticket->registrations()
                     ->whereDate('created_at', '>=', now()->subDays(30))
                     ->selectRaw('DATE(created_at) as date, COUNT(*) as registrations')
                     ->groupBy('date')
                     ->orderBy('date')
                     ->get();
    }
}