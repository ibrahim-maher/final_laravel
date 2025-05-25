<?php

namespace App\Http\Controllers;

use App\Models\Venue;
use Illuminate\Http\Request;

class VenueController extends Controller
{
    public function index()
    {
        $venues = Venue::when(request('search'), function($query) {
                        $query->where('name', 'like', '%' . request('search') . '%');
                    })
                    ->orderBy('name')
                    ->paginate(15);
        
        return view('venues.index', compact('venues'));
    }

    public function create()
    {
        return view('venues.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'capacity' => 'required|integer|min:1',
        ]);

        Venue::create($validated);
        return redirect()->route('venues.index')->with('success', 'Venue created successfully');
    }

    public function show(Venue $venue)
    {
        $venue->load('events');
        return view('venues.show', compact('venue'));
    }

    public function edit(Venue $venue)
    {
        return view('venues.edit', compact('venue'));
    }

    public function update(Request $request, Venue $venue)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'capacity' => 'required|integer|min:1',
        ]);

        $venue->update($validated);
        return redirect()->route('venues.index')->with('success', 'Venue updated successfully');
    }

    public function destroy(Venue $venue)
    {
        $venue->delete();
        return redirect()->route('venues.index')->with('success', 'Venue deleted successfully');
    }
}