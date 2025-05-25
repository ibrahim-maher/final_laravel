{{-- resources/views/registration-fields/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Manage Registration Fields - ' . $event->name)

@section('content')
<div class="container mx-auto px-6 py-8">
    <!-- Header -->
    <div class="mb-8">
        <nav class="flex mb-4" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('events.index') }}" class="text-gray-700 hover:text-blue-600">Events</a>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <a href="{{ route('events.show', $event) }}" class="ml-1 text-gray-700 hover:text-blue-600 md:ml-2">{{ $event->name }}</a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="ml-1 text-gray-500 md:ml-2">Registration Fields</span>
                    </div>
                </li>
            </ol>
        </nav>

        <h1 class="text-3xl font-bold text-gray-800">Manage Registration Fields</h1>
        <p class="text-gray-600 mt-2">Configure custom fields for <strong>{{ $event->name }}</strong> registration form</p>
    </div>

    <!-- Messages -->
    @if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
        {{ session('success') }}
    </div>
    @endif

    @if($errors->any())
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
        <ul class="list-disc list-inside">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Add New Field Form -->
        <div class="lg:col-span-1">
            <div class="bg-white shadow-lg rounded-lg border border-gray-200 p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Add New Field</h2>
                
                <form id="field-form" method="POST" action="{{ route('registration-fields.store', $event) }}">
                    @csrf
                    
                    <!-- Field Name -->
                    <div class="mb-4">
                        <label for="field_name" class="block text-sm font-medium text-gray-700 mb-2">Field Name</label>
                        <input type="text" 
                               id="field_name" 
                               name="field_name" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Enter field name" 
                               required>
                    </div>

                    <!-- Field Type -->
                    <div class="mb-4">
                        <label for="field_type" class="block text-sm font-medium text-gray-700 mb-2">Field Type</label>
                        <select id="field_type" 
                                name="field_type" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                required>
                            <option value="">Select field type</option>
                            @foreach(\App\Models\RegistrationField::FIELD_TYPES as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Options (for dropdown) -->
                    <div id="options-field" class="mb-4 hidden">
                        <label for="options" class="block text-sm font-medium text-gray-700 mb-2">Options</label>
                        <textarea id="options" 
                                  name="options" 
                                  rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                  placeholder="Enter comma-separated options"></textarea>
                        <p class="text-xs text-gray-500 mt-1">Enter options separated by commas (e.g., Option 1, Option 2, Option 3)</p>
                    </div>

                    <!-- Is Required -->
                    <div class="mb-6">
                        <label class="flex items-center">
                            <input type="checkbox" 
                                   name="is_required" 
                                   class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-700">Required field</span>
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" 
                            class="w-full px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        Add Field
                    </button>
                </form>
            </div>

            <!-- Bulk Actions -->
            <div class="bg-white shadow-lg rounded-lg border border-gray-200 p-6 mt-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Bulk Actions</h3>
                
                <div class="space-y-3">
                    <button onclick="exportFields()" 
                            class="w-full px-4 py-2 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700">
                        Export Fields
                    </button>
                    
                    <form action="{{ route('registration-fields.import', $event) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="file" name="csv_file" accept=".csv" class="hidden" id="csv-import" onchange="this.form.submit()">
                        <label for="csv-import" 
                               class="w-full block px-4 py-2 bg-yellow-600 text-white font-medium rounded-lg hover:bg-yellow-700 text-center cursor-pointer">
                            Import Fields
                        </label>
                    </form>
                    
                    <button onclick="deleteSelectedFields()" 
                            class="w-full px-4 py-2 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700"
                            disabled id="bulk-delete-btn">
                        Delete Selected
                    </button>
                </div>
            </div>
        </div>

        <!-- Existing Fields List -->
        <div class="lg:col-span-2">
            <div class="bg-white shadow-lg rounded-lg border border-gray-200">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-800">Registration Fields ({{ $fields->count() }})</h2>
                    <p class="text-gray-600 text-sm mt-1">Drag and drop to reorder fields</p>
                </div>

                <div class="p-6">
                    @if($fields->count() > 0)
                    <div id="sortable-fields" class="space-y-3">
                        @foreach($fields as $field)
                        <div class="field-item bg-gray-50 border border-gray-200 rounded-lg p-4 cursor-move" 
                             data-field-id="{{ $field->id }}">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-4">
                                    <!-- Drag Handle -->
                                    <div class="handle text-gray-400 hover:text-gray-600">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                                        </svg>
                                    </div>

                                    <!-- Field Info -->
                                    <div>
                                        <h3 class="font-medium text-gray-900">{{ $field->field_name }}</h3>
                                        <div class="flex items-center space-x-3 text-sm text-gray-500">
                                            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">
                                                {{ $field->field_type_display }}
                                            </span>
                                            @if($field->is_required)
                                            <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs">Required</span>
                                            @endif
                                            @if($field->options)
                                            <span class="text-xs">{{ count($field->options_array) }} options</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="flex items-center space-x-2">
                                    <!-- Checkbox for bulk actions -->
                                    <input type="checkbox" 
                                           class="field-checkbox h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                           data-field-id="{{ $field->id }}">

                                    <!-- Edit Button -->
                                    <button onclick="editField({{ $field->id }})" 
                                            class="text-blue-600 hover:text-blue-900 p-1 rounded hover:bg-blue-50" 
                                            title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>

                                    <!-- Duplicate Button -->
                                    <button onclick="duplicateField({{ $field->id }})" 
                                            class="text-green-600 hover:text-green-900 p-1 rounded hover:bg-green-50" 
                                            title="Duplicate">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                        </svg>
                                    </button>

                                    <!-- Delete Button -->
                                    <form action="{{ route('registration-fields.destroy', [$event, $field]) }}" 
                                          method="POST" 
                                          class="inline-block"
                                          onsubmit="return confirm('Are you sure you want to delete this field?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="text-red-600 hover:text-red-900 p-1 rounded hover:bg-red-50" 
                                                title="Delete">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <!-- Field Options Preview -->
                            @if($field->options)
                            <div class="mt-3 pl-10">
                                <p class="text-sm text-gray-600 mb-1">Options:</p>
                                <div class="flex flex-wrap gap-1">
                                    @foreach($field->options_array as $option)
                                    <span class="bg-gray-200 text-gray-700 px-2 py-1 rounded text-xs">{{ $option }}</span>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>

                    <!-- Select All Checkbox -->
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <label class="flex items-center">
                            <input type="checkbox" 
                                   id="select-all"
                                   class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-700">Select all fields</span>
                        </label>
                    </div>

                    @else
                    <!-- Empty State -->
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No registration fields</h3>
                        <p class="mt-1 text-sm text-gray-500">Get started by adding your first registration field.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Field Modal -->
<div id="editModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Edit Registration Field</h3>
            <form id="editFieldForm" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div id="editFormContent">
                    <!-- Form content will be loaded here -->
                </div>
                <div class="flex justify-end space-x-3 mt-4">
                    <button type="button" onclick="closeEditModal()"
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeSortable();
    initializeFormHandlers();
    initializeBulkActions();
});

// Initialize sortable functionality
function initializeSortable() {
    const sortableContainer = document.getElementById('sortable-fields');
    if (sortableContainer) {
        new Sortable(sortableContainer, {
            handle: '.handle',
            animation: 150,
            onEnd: function(evt) {
                const fieldIds = Array.from(sortableContainer.children).map(item => 
                    item.getAttribute('data-field-id')
                );
                
                updateFieldOrder(fieldIds);
            }
        });
    }
}

// Update field order via AJAX
function updateFieldOrder(fieldIds) {
    fetch(`{{ route('registration-fields.reorder', $event) }}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            field_ids: fieldIds
        })
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            alert('Error updating field order');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating field order');
    });
}

// Initialize form handlers
function initializeFormHandlers() {
    const fieldTypeSelect = document.getElementById('field_type');
    const optionsField = document.getElementById('options-field');

    if (fieldTypeSelect && optionsField) {
        fieldTypeSelect.addEventListener('change', function() {
            if (this.value === 'dropdown') {
                optionsField.classList.remove('hidden');
            } else {
                optionsField.classList.add('hidden');
            }
        });
    }
}

// Initialize bulk actions
function initializeBulkActions() {
    const selectAllCheckbox = document.getElementById('select-all');
    const fieldCheckboxes = document.querySelectorAll('.field-checkbox');
    const bulkDeleteBtn = document.getElementById('bulk-delete-btn');

    // Select all functionality
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            fieldCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBulkActionButtons();
        });
    }

    // Individual checkbox change
    fieldCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateBulkActionButtons);
    });

    function updateBulkActionButtons() {
        const checkedBoxes = document.querySelectorAll('.field-checkbox:checked');
        bulkDeleteBtn.disabled = checkedBoxes.length === 0;
    }
}

// Edit field function
function editField(fieldId) {
    fetch(`{{ route('registration-fields.index', $event) }}/${fieldId}/edit`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                populateEditForm(data.field, data.fieldTypes);
                document.getElementById('editModal').classList.remove('hidden');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading field data');
        });
}

// Populate edit form
function populateEditForm(field, fieldTypes) {
    const formContent = document.getElementById('editFormContent');
    const editForm = document.getElementById('editFieldForm');
    
    editForm.action = `{{ route('registration-fields.index', $event) }}/${field.id}`;
    
    let optionsHtml = '';
    if (field.field_type === 'dropdown') {
        optionsHtml = `
            <div class="mb-4">
                <label for="edit_options" class="block text-sm font-medium text-gray-700 mb-2">Options</label>
                <textarea id="edit_options" name="options" rows="3"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                          placeholder="Enter comma-separated options">${field.options || ''}</textarea>
            </div>
        `;
    }
    
    formContent.innerHTML = `
        <div class="mb-4">
            <label for="edit_field_name" class="block text-sm font-medium text-gray-700 mb-2">Field Name</label>
            <input type="text" id="edit_field_name" name="field_name" value="${field.field_name}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
        </div>
        
        <div class="mb-4">
            <label for="edit_field_type" class="block text-sm font-medium text-gray-700 mb-2">Field Type</label>
            <select id="edit_field_type" name="field_type"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                ${Object.entries(fieldTypes).map(([key, label]) => 
                    `<option value="${key}" ${key === field.field_type ? 'selected' : ''}>${label}</option>`
                ).join('')}
            </select>
        </div>
        
        <div id="edit_options_field" class="${field.field_type === 'dropdown' ? '' : 'hidden'}">
            ${optionsHtml}
        </div>
        
        <div class="mb-4">
            <label class="flex items-center">
                <input type="checkbox" name="is_required" ${field.is_required ? 'checked' : ''}
                       class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                <span class="ml-2 text-sm text-gray-700">Required field</span>
            </label>
        </div>
    `;
    
    // Add event listener for field type change in edit form
    document.getElementById('edit_field_type').addEventListener('change', function() {
        const editOptionsField = document.getElementById('edit_options_field');
        if (this.value === 'dropdown') {
            editOptionsField.classList.remove('hidden');
        } else {
            editOptionsField.classList.add('hidden');
        }
    });
}

// Close edit modal
function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
}

// Duplicate field function
function duplicateField(fieldId) {
    if (confirm('Are you sure you want to duplicate this field?')) {
        fetch(`{{ route('registration-fields.index', $event) }}/${fieldId}/duplicate`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error duplicating field');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error duplicating field');
        });
    }
}

// Export fields function
function exportFields() {
    window.location.href = `{{ route('registration-fields.export', $event) }}`;
}

// Delete selected fields function
function deleteSelectedFields() {
    const checkedBoxes = document.querySelectorAll('.field-checkbox:checked');
    const fieldIds = Array.from(checkedBoxes).map(cb => cb.getAttribute('data-field-id'));
    
    if (fieldIds.length === 0) {
        alert('Please select fields to delete');
        return;
    }
    
    if (confirm(`Are you sure you want to delete ${fieldIds.length} selected field(s)?`)) {
        fetch(`{{ route('registration-fields.bulk-delete', $event) }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                field_ids: fieldIds
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error deleting fields');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting fields');
        });
    }
}

// Handle edit form submission
document.getElementById('editFieldForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch(this.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error updating field');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating field');
    });
});

// Close modal when clicking outside
document.getElementById('editModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeEditModal();
    }
});
</script>
@endpush
@endsection