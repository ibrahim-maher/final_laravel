<?php

namespace App\Http\Controllers;

use App\Models\BadgeTemplate;
use App\Models\BadgeContent;
use App\Models\Ticket;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BadgeTemplateController extends Controller
{
    public function index()
    {
        $templates = BadgeTemplate::with(['ticket.event', 'creator'])
                                 ->orderBy('created_at', 'desc')
                                 ->paginate(15);
        
        return view('badge-templates.index', compact('templates'));
    }

    public function create()
    {
        $events = Event::with('tickets')->orderBy('name')->get();
        return view('badge-templates.create', compact('events'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ticket_id' => 'required|exists:tickets,id|unique:badge_templates,ticket_id',
            'name' => 'required|string|max:100',
            'width' => 'required|numeric|min:1|max:50',
            'height' => 'required|numeric|min:1|max:50',
            'background_image' => 'nullable|image|max:2048',
            'default_font' => 'required|in:Arial,Helvetica,Times New Roman,Courier,Verdana,Georgia',
        ]);

        if ($request->hasFile('background_image')) {
            $validated['background_image'] = $request->file('background_image')->store('badge_backgrounds', 'public');
        }

        $validated['created_by'] = auth()->id();
        
        $template = BadgeTemplate::create($validated);
        
        return redirect()->route('badge-templates.show', $template)->with('success', 'Badge template created successfully');
    }

    public function show(BadgeTemplate $badgeTemplate)
    {
        $badgeTemplate->load(['ticket.event', 'creator', 'contents']);
        $availableFields = BadgeContent::FIELD_CHOICES;
        
        return view('badge-templates.show', compact('badgeTemplate', 'availableFields'));
    }

    public function edit(BadgeTemplate $badgeTemplate)
    {
        $events = Event::with('tickets')->orderBy('name')->get();
        return view('badge-templates.edit', compact('badgeTemplate', 'events'));
    }

    public function update(Request $request, BadgeTemplate $badgeTemplate)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'width' => 'required|numeric|min:1|max:50',
            'height' => 'required|numeric|min:1|max:50',
            'background_image' => 'nullable|image|max:2048',
            'default_font' => 'required|in:Arial,Helvetica,Times New Roman,Courier,Verdana,Georgia',
        ]);

        if ($request->hasFile('background_image')) {
            if ($badgeTemplate->background_image) {
                Storage::disk('public')->delete($badgeTemplate->background_image);
            }
            $validated['background_image'] = $request->file('background_image')->store('badge_backgrounds', 'public');
        }

        $badgeTemplate->update($validated);
        
        return redirect()->route('badge-templates.show', $badgeTemplate)->with('success', 'Badge template updated successfully');
    }

    public function destroy(BadgeTemplate $badgeTemplate)
    {
        if ($badgeTemplate->background_image) {
            Storage::disk('public')->delete($badgeTemplate->background_image);
        }
        
        $badgeTemplate->delete();
        return redirect()->route('badge-templates.index')->with('success', 'Badge template deleted successfully');
    }

    public function addContent(Request $request, BadgeTemplate $badgeTemplate)
    {
        $validated = $request->validate([
            'field_name' => 'required|string',
            'position_x' => 'required|numeric|min:0',
            'position_y' => 'required|numeric|min:0',
            'font_size' => 'required|integer|min:6|max:72',
            'font_color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'font_family' => 'required|in:Arial,Helvetica,Times New Roman,Courier,Verdana,Georgia',
            'is_bold' => 'boolean',
            'is_italic' => 'boolean',
            'image_width' => 'nullable|numeric|min:0.5|max:20',
            'image_height' => 'nullable|numeric|min:0.5|max:20',
        ]);

        $validated['template_id'] = $badgeTemplate->id;
        $validated['is_bold'] = $request->has('is_bold');
        $validated['is_italic'] = $request->has('is_italic');

        BadgeContent::create($validated);

        return redirect()->route('badge-templates.show', $badgeTemplate)->with('success', 'Badge content added successfully');
    }

    public function removeContent(BadgeTemplate $badgeTemplate, BadgeContent $badgeContent)
    {
        $badgeContent->delete();
        return redirect()->route('badge-templates.show', $badgeTemplate)->with('success', 'Badge content removed successfully');
    }

    public function preview(BadgeTemplate $badgeTemplate)
    {
        $badgeTemplate->load(['ticket.event', 'contents']);
        
        // Sample registration data for preview
        $sampleData = [
            'user' => [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'full_name' => 'John Doe',
                'email' => 'john.doe@example.com',
                'title' => 'Software Developer',
                'company' => 'Tech Corp',
                'country' => 'USA',
            ],
            'event' => [
                'name' => $badgeTemplate->ticket->event->name,
                'location' => $badgeTemplate->ticket->event->venue->name,
            ],
            'ticket_type' => [
                'name' => $badgeTemplate->ticket->name,
            ],
            'registration_id' => '12345',
        ];

        return view('badge-templates.preview', compact('badgeTemplate', 'sampleData'));
    }
}