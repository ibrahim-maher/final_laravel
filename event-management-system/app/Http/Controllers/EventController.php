<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Venue;
use App\Models\Category;
use App\Models\RegistrationField;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EventController extends Controller
{
    /**
     * Display a listing of the events.
     */
    public function index(Request $request)
    {
        $query = Event::with(['venue', 'category']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%')
                  ->orWhereHas('venue', function($vq) use ($search) {
                      $vq->where('name', 'like', '%' . $search . '%');
                  })
                  ->orWhereHas('category', function($cq) use ($search) {
                      $cq->where('name', 'like', '%' . $search . '%');
                  });
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('start_date', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->where('end_date', '<=', $request->date_to);
        }

        // Sort functionality
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        $allowedSorts = ['name', 'start_date', 'end_date', 'created_at', 'is_active'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $events = $query->paginate(15)->withQueryString();

        return view('events.index', compact('events'));
    }

    /**
     * Show the form for creating a new event.
     */
    public function create()
    {
        try {
            $venues = Venue::orderBy('name')->get();
            $categories = Category::orderBy('name')->get();
            
            // Check if we have required data
            if ($venues->isEmpty()) {
                return redirect()->route('venues.create')
                    ->with('warning', 'You need to create at least one venue before creating an event.');
            }
            
            if ($categories->isEmpty()) {
                return redirect()->route('categories.create')
                    ->with('warning', 'You need to create at least one category before creating an event.');
            }
            
            return view('events.create', compact('venues', 'categories'));
            
        } catch (\Exception $e) {
            return redirect()->route('events.index')
                ->with('error', 'Error loading create form: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created event in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:events,name',
            'description' => 'required|string|min:10',
            'start_date' => 'required|date|after:now',
            'end_date' => 'required|date|after:start_date',
            'venue_id' => 'required|exists:venues,id',
            'category_id' => 'required|exists:categories,id',
            'is_active' => 'boolean',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'max_attendees' => 'nullable|integer|min:1',
            'registration_deadline' => 'nullable|date|before:start_date',
        ]);

        try {
            DB::beginTransaction();

            // Handle logo upload
            if ($request->hasFile('logo')) {
                $logoPath = $request->file('logo')->store('event_logos', 'public');
                $validated['logo'] = $logoPath;
            }

            // Set default values
            $validated['is_active'] = $request->has('is_active');
            $validated['slug'] = \Str::slug($validated['name']);
            
            // Ensure unique slug
            $originalSlug = $validated['slug'];
            $counter = 1;
            while (Event::where('slug', $validated['slug'])->exists()) {
                $validated['slug'] = $originalSlug . '-' . $counter;
                $counter++;
            }

            $event = Event::create($validated);

            // Create default registration fields if RegistrationField model exists
            if (class_exists('App\Models\RegistrationField') && defined('App\Models\RegistrationField::DEFAULT_FIELDS')) {
                foreach (RegistrationField::DEFAULT_FIELDS as $index => $field) {
                    RegistrationField::create([
                        'event_id' => $event->id,
                        'field_name' => $field['field_name'],
                        'field_type' => $field['field_type'],
                        'is_required' => $field['is_required'],
                        'order' => $index,
                        'is_active' => true,
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('events.index')
                ->with('success', 'Event "' . $event->name . '" created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Delete uploaded file if it exists
            if (isset($logoPath) && Storage::disk('public')->exists($logoPath)) {
                Storage::disk('public')->delete($logoPath);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'Error creating event: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified event.
     */
    public function show(Event $event)
    {
        $event->load([
            'venue', 
            'category', 
            'tickets.registrations', 
            'registrations.user', 
            'registrationFields'
        ]);

        // Calculate statistics
        $stats = [
            'total_registrations' => $event->registrations->count(),
            'confirmed_registrations' => $event->registrations->where('status', 'confirmed')->count(),
            'pending_registrations' => $event->registrations->where('status', 'pending')->count(),
            'cancelled_registrations' => $event->registrations->where('status', 'cancelled')->count(),
            'total_tickets' => $event->tickets->count(),
            'days_until_event' => Carbon::parse($event->start_date)->diffInDays(now(), false),
        ];

        // Recent registrations
        $recentRegistrations = $event->registrations()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('events.show', compact('event', 'stats', 'recentRegistrations'));
    }

    /**
     * Show the form for editing the specified event.
     */
    public function edit(Event $event)
    {
        $venues = Venue::orderBy('name')->get();
        $categories = Category::orderBy('name')->get();
        
        return view('events.edit', compact('event', 'venues', 'categories'));
    }

    /**
     * Update the specified event in storage.
     */
    public function update(Request $request, Event $event)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:events,name,' . $event->id,
            'description' => 'required|string|min:10',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'venue_id' => 'required|exists:venues,id',
            'category_id' => 'required|exists:categories,id',
            'is_active' => 'boolean',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'max_attendees' => 'nullable|integer|min:1',
            'registration_deadline' => 'nullable|date|before:start_date',
        ]);

        try {
            DB::beginTransaction();

            // Handle logo upload
            if ($request->hasFile('logo')) {
                // Delete old logo
                if ($event->logo && Storage::disk('public')->exists($event->logo)) {
                    Storage::disk('public')->delete($event->logo);
                }
                
                $validated['logo'] = $request->file('logo')->store('event_logos', 'public');
            }

            // Set boolean value
            $validated['is_active'] = $request->has('is_active');
            
            // Update slug if name changed
            if ($event->name !== $validated['name']) {
                $validated['slug'] = \Str::slug($validated['name']);
                
                // Ensure unique slug
                $originalSlug = $validated['slug'];
                $counter = 1;
                while (Event::where('slug', $validated['slug'])->where('id', '!=', $event->id)->exists()) {
                    $validated['slug'] = $originalSlug . '-' . $counter;
                    $counter++;
                }
            }

            $event->update($validated);

            DB::commit();

            return redirect()->route('events.show', $event)
                ->with('success', 'Event updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error updating event: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified event from storage.
     */
    public function destroy(Event $event)
    {
        try {
            DB::beginTransaction();

            // Check if event has registrations
            $registrationCount = $event->registrations()->count();
            if ($registrationCount > 0) {
                return redirect()->route('events.index')
                    ->with('error', 'Cannot delete event with existing registrations. Please cancel all registrations first.');
            }

            // Delete logo if exists
            if ($event->logo && Storage::disk('public')->exists($event->logo)) {
                Storage::disk('public')->delete($event->logo);
            }

            // Delete related records
            $event->registrationFields()->delete();
            $event->tickets()->delete();
            
            $eventName = $event->name;
            $event->delete();

            DB::commit();

            return redirect()->route('events.index')
                ->with('success', 'Event "' . $eventName . '" deleted successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->route('events.index')
                ->with('error', 'Error deleting event: ' . $e->getMessage());
        }
    }

    /**
     * Show analytics for the specified event.
     */
    public function analytics(Event $event)
    {
        try {
            $event->load(['venue', 'category', 'registrations.user', 'tickets']);

            // Registration statistics
            $registrationStats = [
                'total' => $event->registrations->count(),
                'confirmed' => $event->registrations->where('status', 'confirmed')->count(),
                'pending' => $event->registrations->where('status', 'pending')->count(),
                'cancelled' => $event->registrations->where('status', 'cancelled')->count(),
                'checked_in' => $event->registrations->where('checked_in', true)->count(),
            ];

            // Daily registration chart data
            $dailyRegistrations = $event->registrations()
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->pluck('count', 'date')
                ->toArray();

            // Ticket type breakdown
            $ticketBreakdown = [];
            foreach ($event->tickets as $ticket) {
                $ticketBreakdown[$ticket->name] = $ticket->registrations->count();
            }

            // Registration timeline (last 30 days)
            $timelineData = [];
            for ($i = 29; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i)->format('Y-m-d');
                $count = $event->registrations()
                    ->whereDate('created_at', $date)
                    ->count();
                $timelineData[$date] = $count;
            }

            // Demographics (if user data available)
            $demographics = [];
            if ($event->registrations->isNotEmpty()) {
                // You can add more demographic analysis here
                $demographics['total_users'] = $event->registrations->whereNotNull('user_id')->count();
                $demographics['guest_registrations'] = $event->registrations->whereNull('user_id')->count();
            }

            // Revenue calculation (if tickets have prices)
            $revenue = [
                'total' => 0,
                'by_ticket_type' => []
            ];

            foreach ($event->tickets as $ticket) {
                if (isset($ticket->price)) {
                    $ticketRevenue = $ticket->price * $ticket->registrations->where('status', 'confirmed')->count();
                    $revenue['by_ticket_type'][$ticket->name] = $ticketRevenue;
                    $revenue['total'] += $ticketRevenue;
                }
            }

            return view('events.analytics', compact(
                'event',
                'registrationStats',
                'dailyRegistrations',
                'ticketBreakdown',
                'timelineData',
                'demographics',
                'revenue'
            ));

        } catch (\Exception $e) {
            return redirect()->route('events.show', $event)
                ->with('error', 'Error loading analytics: ' . $e->getMessage());
        }
    }

    /**
     * Toggle event status (active/inactive).
     */
    public function toggleStatus(Event $event)
    {
        try {
            $event->is_active = !$event->is_active;
            $event->save();

            $status = $event->is_active ? 'activated' : 'deactivated';
            
            return redirect()->back()
                ->with('success', 'Event "' . $event->name . '" has been ' . $status . '.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error updating event status: ' . $e->getMessage());
        }
    }

    /**
     * Duplicate an existing event.
     */
    public function duplicate(Event $event)
    {
        try {
            DB::beginTransaction();

            $newEvent = $event->replicate();
            $newEvent->name = $event->name . ' (Copy)';
            $newEvent->slug = \Str::slug($newEvent->name);
            $newEvent->is_active = false;
            $newEvent->start_date = Carbon::parse($event->start_date)->addWeek();
            $newEvent->end_date = Carbon::parse($event->end_date)->addWeek();
            
            // Ensure unique slug
            $originalSlug = $newEvent->slug;
            $counter = 1;
            while (Event::where('slug', $newEvent->slug)->exists()) {
                $newEvent->slug = $originalSlug . '-' . $counter;
                $counter++;
            }

            $newEvent->save();

            // Copy registration fields
            foreach ($event->registrationFields as $field) {
                $newField = $field->replicate();
                $newField->event_id = $newEvent->id;
                $newField->save();
            }

            // Copy tickets
            foreach ($event->tickets as $ticket) {
                $newTicket = $ticket->replicate();
                $newTicket->event_id = $newEvent->id;
                $newTicket->save();
            }

            DB::commit();

            return redirect()->route('events.edit', $newEvent)
                ->with('success', 'Event duplicated successfully! Please review and update the details.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->with('error', 'Error duplicating event: ' . $e->getMessage());
        }
    }
}