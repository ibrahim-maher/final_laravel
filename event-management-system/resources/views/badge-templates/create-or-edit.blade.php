@extends('layouts.app')

@section('title', $template ? 'Edit Badge Template' : 'Create Badge Template')
@section('page-title', $template ? 'Edit Badge Template' : 'Create Badge Template')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center space-x-4">
            <a href="{{ route('badge-templates.index') }}" class="flex items-center text-gray-600 hover:text-gray-900">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Templates
            </a>
        </div>
        @if($template)
        <a href="{{ route('badge-templates.preview', $template) }}" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
            <i class="fas fa-eye mr-2"></i>
            Preview Template
        </a>
        @endif
    </div>

    <form method="POST" action="{{ route('badge-templates.createOrEdit') }}" enctype="multipart/form-data" id="badgeTemplateForm">
        @csrf
        
        <!-- Event and Ticket Selection -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Event & Ticket Information</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="event_id" class="block text-sm font-medium text-gray-700 mb-2">Select Event</label>
                    <select name="event_id" id="event_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Select an event...</option>
                        @foreach($events as $event)
                        <option value="{{ $event->id }}" {{ ($ticket && $ticket->event_id == $event->id) ? 'selected' : '' }}>
                            {{ $event->name }}
                        </option>
                        @endforeach
                    </select>
                    @error('event_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="ticket" class="block text-sm font-medium text-gray-700 mb-2">Select Ticket Type</label>
                    <select name="ticket" id="ticket" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                        <option value="">Select a ticket type...</option>
                        @if($ticket)
                        <option value="{{ $ticket->id }}" selected>{{ $ticket->name }}</option>
                        @endif
                    </select>
                    @error('ticket')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Template Settings -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-6" id="templateFormContainer">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Template Settings</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="md:col-span-1">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Template Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $template->name ?? '') }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                    @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-1">
                    <label for="width" class="block text-sm font-medium text-gray-700 mb-2">Width (cm)</label>
                    <input type="number" name="width" id="width" step="0.1" min="1" max="50" value="{{ old('width', $template->width ?? '8.5') }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                    @error('width')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-1">
                    <label for="height" class="block text-sm font-medium text-gray-700 mb-2">Height (cm)</label>
                    <input type="number" name="height" id="height" step="0.1" min="1" max="50" value="{{ old('height', $template->height ?? '5.4') }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                    @error('height')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="background_image" class="block text-sm font-medium text-gray-700 mb-2">Background Image</label>
                    <input type="file" name="background_image" id="background_image" accept="image/*"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    @if($template && $template->background_image_url)
                    <div class="mt-2">
                        <p class="text-sm text-gray-600">Current background:</p>
                        <img src="{{ $template->background_image_url }}" alt="Current background"
                             class="mt-1 w-20 h-20 object-cover rounded">
                    </div>
                    @endif
                    @error('background_image')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="default_font"
                           class="block text-sm font-medium text-gray-700 mb-2">Default Font</label>
                    <select name="default_font"
                            id="default_font"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            required>
                        @foreach(\App\Models\BadgeTemplate::FONT_CHOICES as $value => $label)
                        <option value="{{ $value }}"
                                {{ old('default_font', $template->default_font ?? 'Arial') == $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                        @endforeach
                    </select>
                    @error('default_font')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Badge Content Fields -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Badge Content Fields</h3>
                <button type="button"
                        id="addField"
                        class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                    <i class="fas fa-plus mr-2"></i>
                    Add Field
                </button>
            </div>

            <div id="badgeFields"
                 class="space-y-4">
                @if($contents && $contents->count() > 0)
                @foreach($contents as $index => $content)
                <div class="badge-field-item bg-white rounded-lg shadow p-4 mb-4">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="font-medium text-gray-900">Field {{ $index + 1 }}</h4>
                        <button type="button"
                                class="remove-field text-red-600 hover:text-red-800">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1">Field Type</label>
                            <select name="field_name[]"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    required>
                                <option value="">Select field...</option>
                                @foreach(\App\Models\BadgeContent::FIELD_CHOICES as $value => $label)
                                <option value="{{ $value }}"
                                        {{ $content->field_name == $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1">X Position (cm)</label>
                                <input type="number"
                                       name="position_x[]"
                                       step="0.1"
                                       min="0"
                                       value="{{ $content->position_x }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       required>
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1">Y Position (cm)</label>
                                <input type="number"
                                       name="position_y[]"
                                       step="0.1"
                                       min="0"
                                       value="{{ $content->position_y }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       required>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1">Font Size (pt)</label>
                            <input type="number"
                                   name="font_size[]"
                                   min="6"
                                   max="72"
                                   value="{{ $content->font_size }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   required>
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1">Font Color</label>
                            <input type="color"
                                   name="font_color[]"
                                   value="{{ $content->font_color }}"
                                   class="w-full h-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1">Font Family</label>
                            <select name="font_family[]"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    required>
                                @foreach(\App\Models\BadgeTemplate::FONT_CHOICES as $value => $label)
                                <option value="{{ $value }}"
                                        {{ $content->font_family == $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mt-4 flex items-center space-x-4">
                        <div class="flex items-center">
                            <input type="checkbox"
                                   name="is_bold[]"
                                   value="1"
                                   {{ $content->is_bold ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label
                                class="ml-2 text-sm text-gray-700">Bold</label>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox"
                                   name="is_italic[]"
                                   value="1"
                                   {{ $content->is_italic ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label
                                class="ml-2 text-sm text-gray-700">Italic</label>
                        </div>
                    </div>
                </div>
                @endforeach
                @endif

                @if(!$contents || $contents->count() == 0)
                <div id="noFields"
                     class="text-center py-8 text-gray-500">
                    <i class="fas fa-plus-circle text-4xl mb-3"></i>
                    <p class="text-lg">No fields added yet. Click "Add Field" to get started.</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex items-center justify-between mt-6">
            <a href="{{ route('badge-templates.index') }}"
               class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                <i class="fas fa-times mr-2"></i>
                Cancel
            </a>
            
            <button type="submit"
                    class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                <i class="fas fa-save mr-2"></i>
                {{ $template ? 'Update Template' : 'Create Template' }}
            </button>
        </div>
    </form>
</div>

<!-- Field Template (Hidden) -->
<template id="fieldTemplate">
    <div class="badge-field-item bg-white rounded-lg shadow p-4 mb-4">
        <div class="flex items-center justify-between mb-4">
            <h4 class="font-medium text-gray-900">New Field</h4>
            <button type="button"
                    class="remove-field text-red-600 hover:text-red-800">
                <i class="fas fa-trash"></i>
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label
                    class="block text-sm font-medium text-gray-700 mb-1">Field Type</label>
                <select name="field_name[]"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        required>
                    <option value="">Select field...</option>
                    @foreach(\App\Models\BadgeContent::FIELD_CHOICES as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label
                        class="block text-sm font-medium text-gray-700 mb-1">X Position (cm)</label>
                    <input type="number"
                           name="position_x[]"
                           step="0.1"
                           min="0"
                           value="1"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           required>
                </div>
                <div>
                    <label
                        class="block text-sm font-medium text-gray-700 mb-1">Y Position (cm)</label>
                    <input type="number"
                           name="position_y[]"
                           step="0.1"
                           min="0"
                           value="1"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           required>
                </div>
            </div>
        </div>

        <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label
                    class="block text-sm font-medium text-gray-700 mb-1">Font Size (pt)</label>
                <input type="number"
                       name="font_size[]"
                       min="6"
                       max="72"
                       value="12"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       required>
            </div>
            <div>
                <label
                    class="block text-sm font-medium text-gray-700 mb-1">Font Color</label>
                <input type="color"
                       name="font_color[]"
                       value="#000000"
                       class="w-full h-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label
                    class="block text-sm font-medium text-gray-700 mb-1">Font Family</label>
                <select name="font_family[]"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        required>
                    @foreach(\App\Models\BadgeTemplate::FONT_CHOICES as $value => $label)
                    <option value="{{ $value }}"
                            {{ $value == 'Arial' ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="mt-4 flex items-center space-x-4">
            <div class="flex items-center">
                <input type="checkbox"
                       name="is_bold[]"
                       value="1"
                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                <label
                    class="ml-2 text-sm text-gray-700">Bold</label>
            </div>
            <div class="flex items-center">
                <input type="checkbox"
                       name="is_italic[]"
                       value="1"
                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                <label
                    class="ml-2 text-sm text-gray-700">Italic</label>
            </div>
        </div>
    </div>
</template>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const eventSelect = document.getElementById('event_id');
    const ticketSelect = document.getElementById('ticket');
    const addFieldBtn = document.getElementById('addField');
    const badgeFields = document.getElementById('badgeFields');
    const noFields = document.getElementById('noFields');
    const fieldTemplate = document.getElementById('fieldTemplate');

    // Event selection handler
    eventSelect.addEventListener('change', async function() {
        const eventId = this.value;
        
        // Reset ticket selection
        ticketSelect.innerHTML = '<option value="">Select a ticket type...</option>';
        
        if (eventId) {
            try {
                const response = await fetch(`{{ route('badge-templates.getTickets') }}?event_id=${eventId}`);
                const tickets = await response.json();
                
                tickets.forEach(ticket => {
                    const option = new Option(ticket.name, ticket.id);
                    ticketSelect.add(option);
                });
            } catch (error) {
                console.error('Error fetching tickets:', error);
                alert('Failed to load tickets. Please try again.');
            }
        }
    });

    // Add field functionality
    addFieldBtn.addEventListener('click', function() {
        const newField = fieldTemplate.content.cloneNode(true);
        badgeFields.appendChild(newField);
        
        if (noFields) {
            noFields.style.display = 'none';
        }
        
        updateFieldNumbers();
    });

    // Remove field functionality
    badgeFields.addEventListener('click', function(e) {
        if (e.target.closest('.remove-field')) {
            e.target.closest('.badge-field-item').remove();
            updateFieldNumbers();
            
            if (badgeFields.children.length === 0 && noFields) {
                noFields.style.display = 'block';
            }
        }
    });

    // Update field numbers
    function updateFieldNumbers() {
        const fieldItems = badgeFields.querySelectorAll('.badge-field-item');
        fieldItems.forEach((item, index) => {
            const title = item.querySelector('h4');
            if (title) {
                title.textContent = `Field ${index + 1}`;
            }
        });
    }

    // Form validation
    document.getElementById('badgeTemplateForm').addEventListener('submit', function(e) {
        const fieldItems = badgeFields.querySelectorAll('.badge-field-item');
        
        if (fieldItems.length === 0) {
            e.preventDefault();
            alert('Please add at least one badge content field.');
            return;
        }

        // Check for duplicate field names
        const fieldNames = new Set();
        let hasDuplicates = false;
        
        fieldItems.forEach((item, index) => {
            const fieldNameSelect = item.querySelector('select[name="field_name[]"]');
            const fieldName = fieldNameSelect.value;
            
            if (fieldName) {
                if (fieldNames.has(fieldName)) {
                    hasDuplicates = true;
                    fieldNameSelect.classList.add('border-red-500');
                    alert(`Duplicate field found: ${fieldNameSelect.options[fieldNameSelect.selectedIndex].text} (Field ${index + 1})`);
                } else {
                    fieldNameSelect.classList.remove('border-red-500');
                    fieldNames.add(fieldName);
                }
            }
        });

        if (hasDuplicates) {
            e.preventDefault();
        }
    });

    // Initialize field numbers on page load
    updateFieldNumbers();
});
</script>
@endpush
@endsection