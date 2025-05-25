<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Venue;
use App\Models\Category;
use App\Models\RegistrationField;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::with(['venue', 'category'])
                      ->when(request('search'), function($query) {
                          $query->where('name', 'like', '%' . request('search') . '%');
                      })
                      ->orderBy('created_at', 'desc')
                      ->paginate(15);

        return view('events.index', compact('events'));
    }

    public function create()
    {
        $venues = Venue::all();
        $categories = Category::all();
        return view('events.create', compact('venues', 'categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'venue_id' => 'required|exists:venues,id',
            'category_id' => 'required|exists:categories,id',
            'is_active' => 'boolean',
            'logo' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('event_logos', 'public');
        }

        $event = Event::create($validated);

        // Create default registration fields
        foreach (RegistrationField::DEFAULT_FIELDS as $field) {
            RegistrationField::create([
                'event_id' => $event->id,
                'field_name' => $field['field_name'],
                'field_type' => $field['field_type'],
                'is_required' => $field['is_required'],
                'order' => array_search($field, RegistrationField::DEFAULT_FIELDS),
            ]);
        }

        return redirect()->route('events.index')->with('success', 'Event created successfully');
    }

    public function show(Event $event)
    {
        $event->load(['venue', 'category', 'tickets', 'registrations.user', 'registrationFields']);
        return view('events.show', compact('event'));
    }

    public function edit(Event $event)
    {
        $venues = Venue::all();
        $categories = Category::all();
        return view('events.edit', compact('event', 'venues', 'categories'));
    }

    public function update(Request $request, Event $event)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'venue_id' => 'required|exists:venues,id',
            'category_id' => 'required|exists:categories,id',
            'is_active' => 'boolean',
            'logo' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            // Delete old logo
            if ($event->logo) {
                Storage::disk('public')->delete($event->logo);
            }
            $validated['logo'] = $request->file('logo')->store('event_logos', 'public');
        }

        $event->update($validated);
        return redirect()->route('events.index')->with('success', 'Event updated successfully');
    }

    public function destroy(Event $event)
    {
        if ($event->logo) {
            Storage::disk('public')->delete($event->logo);
        }

        $event->delete();
        return redirect()->route('events.index')->with('success', 'Event deleted successfully');
    }
}