<?php

namespace App\Http\Controllers;

use App\Models\BadgeTemplate;
use App\Models\BadgeContent;
use App\Models\Ticket;
use App\Models\Event;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class BadgeTemplateController extends Controller
{
    public function index()
    {
        $templates = BadgeTemplate::with(['ticket.event', 'creator', 'contents'])
                                 ->orderBy('created_at', 'desc')
                                 ->paginate(15);
        
        return view('badge-templates.index', compact('templates'));
    }

    public function create()
    {
        $events = Event::with('tickets')->orderBy('name')->get();
        return view('badge-templates.create', compact('events'));
    }

  public function getTickets(Request $request)
{
    try {
        $eventId = $request->get('event_id');
        
        if (!$eventId) {
            return response()->json([]);
        }

        $tickets = \App\Models\Ticket::where('event_id', $eventId)
            ->select('id', 'name')
            ->get();

        return response()->json($tickets);
        
    } catch (\Exception $e) {
        \Log::error('Error fetching tickets: ' . $e->getMessage());
        return response()->json([], 500);
    }
}

    public function createOrEdit(Request $request)
    {
        $ticketId = $request->get('ticket') ?? $request->input('ticket');
        $events = Event::with('tickets')->orderBy('start_date', 'desc')->get();
        
        $template = null;
        $contents = collect();
        $ticket = null;

        if ($ticketId) {
            $ticket = Ticket::with('event')->findOrFail($ticketId);
            $template = BadgeTemplate::where('ticket_id', $ticketId)->first();
            
            if ($template) {
                $contents = $template->contents;
            }
        }

        if ($request->method() === 'POST') {
            return $this->store($request);
        }

        return view('badge-templates.create-or-edit', compact('events', 'template', 'contents', 'ticket'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ticket' => 'required|exists:tickets,id',
            'name' => 'required|string|max:100',
            'width' => 'required|numeric|min:1|max:50',
            'height' => 'required|numeric|min:1|max:50',
            'background_image' => 'nullable|image|max:2048',
            'default_font' => 'required|in:Arial,Helvetica,Times New Roman,Courier,Verdana,Georgia',
            
            // Badge content fields
            'field_name.*' => 'required|string',
            'position_x.*' => 'required|numeric|min:0',
            'position_y.*' => 'required|numeric|min:0',
            'font_size.*' => 'required|integer|min:6|max:72',
            'font_color.*' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'font_family.*' => 'required|string',
            'is_bold.*' => 'nullable|boolean',
            'is_italic.*' => 'nullable|boolean',
        ]);

        DB::beginTransaction();

        try {
            // Handle background image upload
            if ($request->hasFile('background_image')) {
                $validated['background_image'] = $request->file('background_image')
                    ->store('badge_backgrounds', 'public');
            }

            $validated['created_by'] = auth()->id();
            $validated['ticket_id'] = $validated['ticket'];
            unset($validated['ticket']);

            // Create or update template
            $existingTemplate = BadgeTemplate::where('ticket_id', $validated['ticket_id'])->first();
            
            if ($existingTemplate) {
                // Delete old background image if new one is uploaded
                if ($request->hasFile('background_image') && $existingTemplate->background_image) {
                    Storage::disk('public')->delete($existingTemplate->background_image);
                }
                $existingTemplate->update($validated);
                $template = $existingTemplate;
                
                // Delete existing contents
                $template->contents()->delete();
            } else {
                $template = BadgeTemplate::create($validated);
            }

            // Save badge contents
            if ($request->has('field_name')) {
                $fieldNames = $request->input('field_name', []);
                $positionsX = $request->input('position_x', []);
                $positionsY = $request->input('position_y', []);
                $fontSizes = $request->input('font_size', []);
                $fontColors = $request->input('font_color', []);
                $fontFamilies = $request->input('font_family', []);
                $isBold = $request->input('is_bold', []);
                $isItalic = $request->input('is_italic', []);

                foreach ($fieldNames as $index => $fieldName) {
                    if (!empty($fieldName)) {
                        BadgeContent::create([
                            'template_id' => $template->id,
                            'field_name' => $fieldName,
                            'position_x' => $positionsX[$index] ?? 0,
                            'position_y' => $positionsY[$index] ?? 0,
                            'font_size' => $fontSizes[$index] ?? 12,
                            'font_color' => $fontColors[$index] ?? '#000000',
                            'font_family' => $fontFamilies[$index] ?? 'Arial',
                            'is_bold' => isset($isBold[$index]) && $isBold[$index],
                            'is_italic' => isset($isItalic[$index]) && $isItalic[$index],
                        ]);
                    }
                }
            }

            DB::commit();
            
            return redirect()->route('badge-templates.preview', $template)
                            ->with('success', 'Badge template saved successfully');
                            
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()->withErrors(['error' => 'Error saving template: ' . $e->getMessage()]);
        }
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
        $badgeTemplate->load(['ticket.event', 'contents']);
        
        return view('badge-templates.edit', compact('badgeTemplate', 'events'));
    }

   public function preview(BadgeTemplate $badgeTemplate)
{
    $badgeTemplate->load(['ticket.event', 'contents']);
    
    $contents = $badgeTemplate->contents;
    
    return view('badge-templates.preview', compact('badgeTemplate', 'contents'));
}

    public function updateContent(Request $request, $contentId)
    {
        $validated = $request->validate([
            'position_x' => 'required|numeric|min:0',
            'position_y' => 'required|numeric|min:0',
            'font_size' => 'nullable|integer|min:6|max:72',
            'font_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'font_family' => 'nullable|string',
            'is_bold' => 'nullable|boolean',
            'is_italic' => 'nullable|boolean',
        ]);

        try {
            $content = BadgeContent::findOrFail($contentId);
            $content->update($validated);

            return response()->json([
                'status' => 'success',
                'message' => 'Content updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error updating content: ' . $e->getMessage()
            ], 500);
        }
    }

    public function saveAllChanges(Request $request)
    {
        try {
            $updates = $request->input('updates', []);

            foreach ($updates as $update) {
                $content = BadgeContent::findOrFail($update['content_id']);
                $content->update([
                    'position_x' => $update['position_x'],
                    'position_y' => $update['position_y'],
                    'font_size' => $update['font_size'],
                    'font_color' => $update['font_color'],
                    'font_family' => $update['font_family'],
                    'is_bold' => $update['is_bold'],
                    'is_italic' => $update['is_italic'],
                ]);
            }

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error', 
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(BadgeTemplate $badgeTemplate)
    {
        if ($badgeTemplate->background_image) {
            Storage::disk('public')->delete($badgeTemplate->background_image);
        }
        
        $badgeTemplate->delete();
        return redirect()->route('badge-templates.index')
                        ->with('success', 'Badge template deleted successfully');
    }

    public function printBadge(Registration $registration)
    {
        $badgeTemplate = BadgeTemplate::where('ticket_id', $registration->ticket_type_id)
                                     ->with('contents')
                                     ->first();

        if (!$badgeTemplate) {
            return view('badge-templates.no-template', compact('registration'));
        }

        // Prepare badge data
        $badgeData = [];
        foreach ($badgeTemplate->contents as $content) {
            $badgeData[$content->field_name] = $content->getFieldValue($registration);
        }

        return view('badge-templates.print', compact('badgeTemplate', 'registration', 'badgeData'));
    }
}