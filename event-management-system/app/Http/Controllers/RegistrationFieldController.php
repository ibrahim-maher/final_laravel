<?php

namespace App\Http\Controllers;

use App\Models\RegistrationField;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Log;

class RegistrationFieldController extends Controller
{   
    use AuthorizesRequests;

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
            'is_required' => 'nullable|boolean',
            'options' => 'nullable|string',
        ]);

        $validated['event_id'] = $event->id;
        $validated['is_required'] = $request->has('is_required') && $request->input('is_required') == '1';
        $validated['order'] = $event->registrationFields()->max('order') + 1;

        // Clean up options for dropdown fields
        if ($validated['field_type'] === 'dropdown' && !empty($validated['options'])) {
            $options = array_map('trim', explode(',', $validated['options']));
            $options = array_filter($options); // Remove empty options
            $validated['options'] = implode(',', $options);
        } else {
            $validated['options'] = null;
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
            'is_required' => 'nullable',
            'options' => 'nullable|string',
        ]);

        // Handle the checkbox properly
        $validated['is_required'] = $request->has('is_required') && $request->input('is_required') == '1';

        // Clean up options for dropdown fields
        if ($validated['field_type'] === 'dropdown' && !empty($validated['options'])) {
            $options = array_map('trim', explode(',', $validated['options']));
            $options = array_filter($options); // Remove empty options
            $validated['options'] = implode(',', $options);
        } else {
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
        try {
            // Check if user can update registration fields for this event
            $this->authorize('create', RegistrationField::class);

            $validated = $request->validate([
                'field_ids' => 'required|array',
                'field_ids.*' => 'exists:registration_fields,id'
            ]);

            $fieldIds = $validated['field_ids'];
            
            // Verify all fields belong to this event
            $fieldsCount = RegistrationField::whereIn('id', $fieldIds)
                                          ->where('event_id', $event->id)
                                          ->count();
            
            if ($fieldsCount !== count($fieldIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid field IDs provided'
                ], 400);
            }
            
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
        } catch (\Exception $e) {
            Log::error('Error reordering fields: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating field order: ' . $e->getMessage()
            ], 500);
        }
    }

    public function duplicate(Event $event, RegistrationField $registrationField)
    {
        try {
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
        } catch (\Exception $e) {
            Log::error('Error duplicating field: ' . $e->getMessage());
            
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error duplicating field'
                ], 500);
            }

            return redirect()->route('registration-fields.index', $event)
                            ->with('error', 'Error duplicating field');
        }
    }

    public function bulkDelete(Request $request, Event $event)
    {
        try {
            $this->authorize('create', RegistrationField::class);

            $validated = $request->validate([
                'field_ids' => 'required|array',
                'field_ids.*' => 'exists:registration_fields,id'
            ]);

            $fieldIds = $validated['field_ids'];
            
            // Verify all fields belong to this event
            $fieldsToDelete = RegistrationField::whereIn('id', $fieldIds)
                                              ->where('event_id', $event->id)
                                              ->get();
            
            if ($fieldsToDelete->count() !== count($fieldIds)) {
                if (request()->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid field IDs provided'
                    ], 400);
                }
                
                return redirect()->route('registration-fields.index', $event)
                                ->with('error', 'Invalid field IDs provided');
            }
            
            $deletedCount = $fieldsToDelete->count();
            
            foreach ($fieldsToDelete as $field) {
                $field->delete();
            }

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "{$deletedCount} registration fields deleted successfully"
                ]);
            }

            return redirect()->route('registration-fields.index', $event)
                            ->with('success', "{$deletedCount} registration fields deleted successfully");
        } catch (\Exception $e) {
            Log::error('Error bulk deleting fields: ' . $e->getMessage());
            
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error deleting fields: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->route('registration-fields.index', $event)
                            ->with('error', 'Error deleting fields');
        }
    }

    public function export(Event $event)
    {
        $this->authorize('viewAny', RegistrationField::class);

        $fields = $event->registrationFields()->ordered()->get();

        $csvContent = "Field Name,Field Type,Required,Options,Order\n";
        
        foreach ($fields as $field) {
            $csvContent .= sprintf(
                "%s,%s,%s,%s,%d\n",
                '"' . str_replace('"', '""', $field->field_name) . '"',
                $field->field_type,
                $field->is_required ? 'Yes' : 'No',
                '"' . str_replace('"', '""', $field->options ?? '') . '"',
                $field->order
            );
        }

        return response($csvContent)
                ->header('Content-Type', 'text/csv')
                ->header('Content-Disposition', 'attachment; filename="registration-fields-' . $event->name . '.csv"');
    }

    public function import(Request $request, Event $event)
    {
        try {
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
                    
                    // Validate field type
                    $fieldType = $data['Field Type'] ?? 'text';
                    if (!array_key_exists($fieldType, RegistrationField::FIELD_TYPES)) {
                        $fieldType = 'text';
                    }
                    
                    RegistrationField::create([
                        'event_id' => $event->id,
                        'field_name' => $data['Field Name'] ?? 'Unnamed Field',
                        'field_type' => $fieldType,
                        'is_required' => ($data['Required'] ?? 'No') === 'Yes',
                        'options' => !empty($data['Options']) ? $data['Options'] : null,
                        'order' => $event->registrationFields()->max('order') + 1,
                    ]);

                    $imported++;
                } catch (\Exception $e) {
                    $errors[] = "Line " . ($index + 2) . ": " . $e->getMessage();
                }
            }

            $message = "Imported {$imported} fields successfully.";
            if (!empty($errors)) {
                $message .= " Errors: " . implode(', ', array_slice($errors, 0, 3));
                if (count($errors) > 3) {
                    $message .= " and " . (count($errors) - 3) . " more.";
                }
            }

            return redirect()->route('registration-fields.index', $event)
                            ->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Error importing fields: ' . $e->getMessage());
            return redirect()->route('registration-fields.index', $event)
                            ->with('error', 'Error importing fields');
        }
    }
}