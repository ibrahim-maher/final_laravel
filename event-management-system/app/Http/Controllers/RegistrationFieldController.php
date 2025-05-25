<?php

namespace App\Http\Controllers;

use App\Models\RegistrationField;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;  // ← add


class RegistrationFieldController extends Controller
{   
        use AuthorizesRequests; // ← add

    public function index(Event $event)
    {
        $this->authorize('viewAny', RegistrationField::class);
        
        $fields = $event->registrationFields()->ordered()->get();
        
        return view('registration-fields.index', compact('event', 'fields'));
    }

    public function create(Event $event)
    {
        $this->authorize('create', RegistrationField::class);
        
        return view('registration-fields.create', compact('event'));
    }

    public function store(Request $request, Event $event)
    {
        $this->authorize('create', RegistrationField::class);

        $validated = $request->validate([
            'field_name' => 'required|string|max:50',
            'field_type' => 'required|in:' . implode(',', array_keys(RegistrationField::FIELD_TYPES)),
            'is_required' => 'boolean',
            'options' => 'nullable|string',
        ]);

        $validated['event_id'] = $event->id;
        $validated['is_required'] = $request->has('is_required');
        $validated['order'] = $event->registrationFields()->max('order') + 1;

        // Clean up options for dropdown fields
        if ($validated['field_type'] === 'dropdown' && $validated['options']) {
            $options = array_map('trim', explode(',', $validated['options']));
            $options = array_filter($options); // Remove empty options
            $validated['options'] = implode(',', $options);
        }

        $field = RegistrationField::create($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Registration field added successfully',
                'field' => $field
            ]);
        }

        return redirect()->route('registration-fields.index', $event)
                        ->with('success', 'Registration field added successfully');
    }

    public function show(Event $event, RegistrationField $registrationField)
    {
        $this->authorize('view', $registrationField);
        
        return view('registration-fields.show', compact('event', 'registrationField'));
    }

    public function edit(Event $event, RegistrationField $registrationField)
    {
        $this->authorize('update', $registrationField);
        
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'field' => $registrationField,
                'fieldTypes' => RegistrationField::FIELD_TYPES
            ]);
        }
        
        return view('registration-fields.edit', compact('event', 'registrationField'));
    }

    public function update(Request $request, Event $event, RegistrationField $registrationField)
    {
        $this->authorize('update', $registrationField);

        $validated = $request->validate([
            'field_name' => 'required|string|max:50',
            'field_type' => 'required|in:' . implode(',', array_keys(RegistrationField::FIELD_TYPES)),
            'is_required' => 'boolean',
            'options' => 'nullable|string',
        ]);

        $validated['is_required'] = $request->has('is_required');

        // Clean up options for dropdown fields
        if ($validated['field_type'] === 'dropdown' && $validated['options']) {
            $options = array_map('trim', explode(',', $validated['options']));
            $options = array_filter($options); // Remove empty options
            $validated['options'] = implode(',', $options);
        } elseif ($validated['field_type'] !== 'dropdown') {
            $validated['options'] = null;
        }

        $registrationField->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Registration field updated successfully',
                'field' => $registrationField->fresh()
            ]);
        }

        return redirect()->route('registration-fields.index', $event)
                        ->with('success', 'Registration field updated successfully');
    }

    public function destroy(Event $event, RegistrationField $registrationField)
    {
        $this->authorize('delete', $registrationField);

        $registrationField->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Registration field deleted successfully'
            ]);
        }

        return redirect()->route('registration-fields.index', $event)
                        ->with('success', 'Registration field deleted successfully');
    }

    public function reorder(Request $request, Event $event): JsonResponse
    {
        $this->authorize('update', RegistrationField::class);

        $request->validate([
            'field_ids' => 'required|array',
            'field_ids.*' => 'exists:registration_fields,id'
        ]);

        $fieldIds = $request->input('field_ids', []);
        
        DB::transaction(function () use ($fieldIds, $event) {
            foreach ($fieldIds as $index => $fieldId) {
                RegistrationField::where('id', $fieldId)
                               ->where('event_id', $event->id)
                               ->update(['order' => $index + 1]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Field order updated successfully'
        ]);
    }

    public function duplicate(Event $event, RegistrationField $registrationField)
    {
        $this->authorize('create', RegistrationField::class);

        $newField = $registrationField->replicate();
        $newField->field_name = $registrationField->field_name . ' (Copy)';
        $newField->order = $event->registrationFields()->max('order') + 1;
        $newField->save();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Registration field duplicated successfully',
                'field' => $newField
            ]);
        }

        return redirect()->route('registration-fields.index', $event)
                        ->with('success', 'Registration field duplicated successfully');
    }

    public function bulkDelete(Request $request, Event $event)
    {
        $this->authorize('delete', RegistrationField::class);

        $request->validate([
            'field_ids' => 'required|array',
            'field_ids.*' => 'exists:registration_fields,id'
        ]);

        $fieldIds = $request->input('field_ids', []);
        
        $deletedCount = RegistrationField::whereIn('id', $fieldIds)
                                        ->where('event_id', $event->id)
                                        ->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "{$deletedCount} registration fields deleted successfully"
            ]);
        }

        return redirect()->route('registration-fields.index', $event)
                        ->with('success', "{$deletedCount} registration fields deleted successfully");
    }

    public function export(Event $event)
    {
        $this->authorize('viewAny', RegistrationField::class);

        $fields = $event->registrationFields()->ordered()->get();

        $csvContent = "Field Name,Field Type,Required,Options,Order\n";
        
        foreach ($fields as $field) {
            $csvContent .= sprintf(
                "%s,%s,%s,%s,%d\n",
                $field->field_name,
                $field->field_type,
                $field->is_required ? 'Yes' : 'No',
                $field->options ?? '',
                $field->order
            );
        }

        return response($csvContent)
                ->header('Content-Type', 'text/csv')
                ->header('Content-Disposition', 'attachment; filename="registration-fields-' . $event->name . '.csv"');
    }

    public function import(Request $request, Event $event)
    {
        $this->authorize('create', RegistrationField::class);

        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt'
        ]);

        $file = $request->file('csv_file');
        $csvData = file_get_contents($file->getRealPath());
        $lines = array_map('str_getcsv', explode("\n", $csvData));
        $header = array_shift($lines);

        $imported = 0;
        $errors = [];

        foreach ($lines as $index => $line) {
            if (empty(array_filter($line))) continue; // Skip empty lines

            try {
                $data = array_combine($header, $line);
                
                RegistrationField::create([
                    'event_id' => $event->id,
                    'field_name' => $data['Field Name'] ?? '',
                    'field_type' => $data['Field Type'] ?? 'text',
                    'is_required' => ($data['Required'] ?? 'No') === 'Yes',
                    'options' => $data['Options'] ?? null,
                    'order' => $event->registrationFields()->max('order') + 1,
                ]);

                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Line " . ($index + 2) . ": " . $e->getMessage();
            }
        }

        $message = "Imported {$imported} fields successfully.";
        if (!empty($errors)) {
            $message .= " Errors: " . implode(', ', $errors);
        }

        return redirect()->route('registration-fields.index', $event)
                        ->with('success', $message);
    }
}