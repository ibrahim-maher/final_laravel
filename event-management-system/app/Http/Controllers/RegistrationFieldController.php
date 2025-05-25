<?php

namespace App\Http\Controllers;

use App\Models\RegistrationField;
use App\Models\Event;
use Illuminate\Http\Request;

class RegistrationFieldController extends Controller
{
    public function index(Event $event)
    {
        $fields = $event->registrationFields()->orderBy('order')->get();
        return view('registration-fields.index', compact('event', 'fields'));
    }

    public function store(Request $request, Event $event)
    {
        $validated = $request->validate([
            'field_name' => 'required|string|max:50',
            'field_type' => 'required|in:text,email,number,dropdown,checkbox',
            'is_required' => 'boolean',
            'options' => 'nullable|string',
        ]);

        $validated['event_id'] = $event->id;
        $validated['is_required'] = $request->has('is_required');
        $validated['order'] = $event->registrationFields()->max('order') + 1;

        RegistrationField::create($validated);

        return redirect()->route('registration-fields.index', $event)
                        ->with('success', 'Registration field added successfully');
    }

    public function update(Request $request, Event $event, RegistrationField $registrationField)
    {
        $validated = $request->validate([
            'field_name' => 'required|string|max:50',
            'field_type' => 'required|in:text,email,number,dropdown,checkbox',
            'is_required' => 'boolean',
            'options' => 'nullable|string',
        ]);

        $validated['is_required'] = $request->has('is_required');

        $registrationField->update($validated);

        return redirect()->route('registration-fields.index', $event)
                        ->with('success', 'Registration field updated successfully');
    }

    public function destroy(Event $event, RegistrationField $registrationField)
    {
        $registrationField->delete();
        
        return redirect()->route('registration-fields.index', $event)
                        ->with('success', 'Registration field deleted successfully');
    }

    public function reorder(Request $request, Event $event)
    {
        $fieldIds = $request->input('field_ids', []);
        
        foreach ($fieldIds as $index => $fieldId) {
            RegistrationField::where('id', $fieldId)
                           ->where('event_id', $event->id)
                           ->update(['order' => $index]);
        }

        return response()->json(['success' => true]);
    }
}