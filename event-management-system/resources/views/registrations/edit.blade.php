@extends('layouts.app')

@section('title', 'Edit Registration')

@section('content')
<div class=" mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- CSRF Token for JavaScript -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Header -->
    <div class="mb-6">
        <nav class="flex mb-4" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('registrations.index') }}" class="text-gray-700 hover:text-blue-600">Registrations</a>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <a href="{{ route('registrations.show', $registration) }}" class="ml-1 text-gray-700 hover:text-blue-600 md:ml-2">Registration #{{ $registration->id }}</a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="ml-1 text-gray-500 md:ml-2">Edit</span>
                    </div>
                </li>
            </ol>
        </nav>
        
        <h1 class="text-3xl font-bold text-gray-900">Edit Registration</h1>
        <p class="text-gray-600 mt-2">Update registration details for {{ $registration->user->name ?? 'this participant' }}</p>
    </div>

    <!-- Messages -->
    @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <h4 class="font-medium mb-2">Please fix the following errors:</h4>
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            {{ session('success') }}
        </div>
    @endif

    <!-- Registration Form -->
    <div class="bg-white shadow-lg rounded-lg border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-800">Registration Details</h2>
        </div>
        
        <form action="{{ route('registrations.update', $registration) }}" method="POST" class="p-6">
            @csrf
            @method('PUT')
            
            <!-- Basic Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Event Selection -->
                <div>
                    <label for="event_id" class="block text-sm font-medium text-gray-700 mb-2">Event *</label>
                    <select name="event_id" id="event_id" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                            required>
                        <option value="">Select an Event</option>
                        @foreach($events as $event)
                            <option value="{{ $event->id }}" 
                                    {{ (old('event_id', $registration->event_id) == $event->id) ? 'selected' : '' }}>
                                {{ $event->name }} - {{ $event->start_date->format('M d, Y') }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- User Selection -->
                <div>
                    <label for="user_id" class="block text-sm font-medium text-gray-700 mb-2">Participant *</label>
                    <select name="user_id" id="user_id" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            required>
                        <option value="">Select a Participant</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" 
                                    {{ (old('user_id', $registration->user_id) == $user->id) ? 'selected' : '' }}>
                                {{ $user->name }} ({{ $user->email }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Status and Ticket Type -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Status -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status *</label>
                    <select name="status" id="status" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                            required>
                        <option value="pending" {{ old('status', $registration->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="confirmed" {{ old('status', $registration->status) == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                        <option value="cancelled" {{ old('status', $registration->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>

                <!-- Ticket Type Selection -->
                <div id="ticket-types-container">
                    <label for="ticket_type_id" class="block text-sm font-medium text-gray-700 mb-2">Ticket Type</label>
                    <div id="ticket-options" class="space-y-3">
                        @if($tickets && $tickets->count() > 0)
                            @foreach($tickets as $ticket)
                            <div class="border rounded-lg p-3 {{ $ticket->is_available ? 'border-gray-200 hover:border-blue-500' : 'border-gray-100 bg-gray-50' }}">
                                <label class="flex items-center {{ $ticket->is_available ? 'cursor-pointer' : 'cursor-not-allowed' }}">
                                    <input type="radio" 
                                           name="ticket_type_id" 
                                           value="{{ $ticket->id }}" 
                                           class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                                           {{ old('ticket_type_id', $registration->ticket_type_id) == $ticket->id ? 'checked' : '' }}
                                           {{ !$ticket->is_available ? 'disabled' : '' }}>
                                    <div class="ml-3 flex-1">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <p class="font-medium text-gray-900">{{ $ticket->name }}</p>
                                                <p class="text-sm text-gray-600">${{ $ticket->price }}</p>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-sm {{ $ticket->is_available ? 'text-green-600' : 'text-red-600' }}">
                                                    {{ $ticket->is_available ? 'Available' : 'Not Available' }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                            @endforeach
                        @else
                            <p class="text-gray-500">No tickets available for this event</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Dynamic Registration Fields -->
            <div id="dynamic-fields" class="mb-6">
                <div class="border-t border-gray-200 pt-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Registration Information</h3>
                    <div id="dynamic-fields-container" class="space-y-4">
                        @if($registration->event && $registration->event->registrationFields)
                            @foreach($registration->event->registrationFields as $field)
                                @php
                                    $fieldKey = Str::slug($field->field_name, '_');
                                    $fieldValue = old($fieldKey, $registration->registration_data[$field->field_name] ?? '');
                                @endphp
                                <div class="mb-4">
                                    <label for="{{ $fieldKey }}" class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ $field->field_name }}{{ $field->is_required ? ' *' : '' }}
                                    </label>
                                    
                                    @switch($field->field_type)
                                        @case('text')
                                            <input type="text" name="{{ $fieldKey }}" id="{{ $fieldKey }}" 
                                                   value="{{ $fieldValue }}"
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                                   {{ $field->is_required ? 'required' : '' }}>
                                            @break
                                        
                                        @case('email')
                                            <input type="email" name="{{ $fieldKey }}" id="{{ $fieldKey }}" 
                                                   value="{{ $fieldValue }}"
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                                   {{ $field->is_required ? 'required' : '' }}>
                                            @break
                                        
                                        @case('number')
                                            <input type="number" name="{{ $fieldKey }}" id="{{ $fieldKey }}" 
                                                   value="{{ $fieldValue }}"
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                                   {{ $field->is_required ? 'required' : '' }}>
                                            @break
                                        
                                        @case('phone')
                                            <input type="tel" name="{{ $fieldKey }}" id="{{ $fieldKey }}" 
                                                   value="{{ $fieldValue }}"
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                                   {{ $field->is_required ? 'required' : '' }}>
                                            @break
                                        
                                        @case('date')
                                            <input type="date" name="{{ $fieldKey }}" id="{{ $fieldKey }}" 
                                                   value="{{ $fieldValue }}"
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                                   {{ $field->is_required ? 'required' : '' }}>
                                            @break
                                        
                                        @case('time')
                                            <input type="time" name="{{ $fieldKey }}" id="{{ $fieldKey }}" 
                                                   value="{{ $fieldValue }}"
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                                   {{ $field->is_required ? 'required' : '' }}>
                                            @break
                                        
                                        @case('url')
                                            <input type="url" name="{{ $fieldKey }}" id="{{ $fieldKey }}" 
                                                   value="{{ $fieldValue }}"
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                                   {{ $field->is_required ? 'required' : '' }}>
                                            @break
                                        
                                        @case('textarea')
                                            <textarea name="{{ $fieldKey }}" id="{{ $fieldKey }}" rows="3" 
                                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                                      {{ $field->is_required ? 'required' : '' }}>{{ $fieldValue }}</textarea>
                                            @break
                                        
                                        @case('dropdown')
                                            <select name="{{ $fieldKey }}" id="{{ $fieldKey }}" 
                                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                                    {{ $field->is_required ? 'required' : '' }}>
                                                <option value="">Select {{ $field->field_name }}</option>
                                                @if($field->options_array)
                                                    @foreach($field->options_array as $option)
                                                        <option value="{{ $option }}" {{ $fieldValue == $option ? 'selected' : '' }}>
                                                            {{ $option }}
                                                        </option>
                                                    @endforeach
                                                @endif
                                            </select>
                                            @break
                                        
                                        @case('radio')
                                            @if($field->options_array)
                                                <div class="space-y-2">
                                                    @foreach($field->options_array as $option)
                                                        <label class="flex items-center">
                                                            <input type="radio" name="{{ $fieldKey }}" value="{{ $option }}" 
                                                                   class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500" 
                                                                   {{ $fieldValue == $option ? 'checked' : '' }}
                                                                   {{ $field->is_required ? 'required' : '' }}>
                                                            <span class="ml-2 text-sm text-gray-700">{{ $option }}</span>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            @endif
                                            @break
                                        
                                        @case('checkbox')
                                            @if($field->options_array)
                                                <div class="space-y-2">
                                                    @foreach($field->options_array as $option)
                                                        <label class="flex items-center">
                                                            <input type="checkbox" name="{{ $fieldKey }}[]" value="{{ $option }}" 
                                                                   class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                                                   {{ is_array($fieldValue) && in_array($option, $fieldValue) ? 'checked' : '' }}>
                                                            <span class="ml-2 text-sm text-gray-700">{{ $option }}</span>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            @else
                                                <label class="flex items-center">
                                                    <input type="checkbox" name="{{ $fieldKey }}" value="1" 
                                                           class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" 
                                                           {{ $fieldValue ? 'checked' : '' }}
                                                           {{ $field->is_required ? 'required' : '' }}>
                                                    <span class="ml-2 text-sm text-gray-700">Yes</span>
                                                </label>
                                            @endif
                                            @break
                                        
                                        @default
                                            <input type="text" name="{{ $fieldKey }}" id="{{ $fieldKey }}" 
                                                   value="{{ $fieldValue }}"
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                                   {{ $field->is_required ? 'required' : '' }}>
                                    @endswitch
                                </div>
                            @endforeach
                        @else
                            <p class="text-gray-500">No additional fields for this registration</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                <a href="{{ route('registrations.show', $registration) }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Cancel
                </a>
                
                <button type="submit" 
                        class="inline-flex items-center px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Update Registration
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const eventSelect = document.getElementById('event_id');
    const ticketOptionsContainer = document.getElementById('ticket-options');
    const dynamicFieldsContainer = document.getElementById('dynamic-fields-container');
    
    // Get CSRF token
    function getCSRFToken() {
        return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    }

    // Event selection handler
    eventSelect.addEventListener('change', function() {
        const eventId = this.value;
        
        if (!eventId) {
            resetForm();
            return;
        }

        // Load tickets and fields for the selected event
        Promise.all([
            loadTickets(eventId),
            loadRegistrationFields(eventId)
        ]).catch(error => {
            console.error('Error loading event data:', error);
            alert('Error loading event data. Please try again.');
        });
    });

    function resetForm() {
        ticketOptionsContainer.innerHTML = '<p class="text-gray-500">Please select an event first</p>';
        dynamicFieldsContainer.innerHTML = '<p class="text-gray-500">Please select an event to see registration fields</p>';
    }

    function loadTickets(eventId) {
        return fetch(`/api/events/${eventId}/tickets`, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCSRFToken()
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to load tickets');
            }
            return response.json();
        })
        .then(tickets => {
            renderTickets(tickets);
        });
    }

    function loadRegistrationFields(eventId) {
        return fetch(`/api/events/${eventId}/registration-fields`, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCSRFToken()
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to load registration fields');
            }
            return response.json();
        })
        .then(fields => {
            renderRegistrationFields(fields);
        });
    }

    function renderTickets(tickets) {
        if (tickets.length === 0) {
            ticketOptionsContainer.innerHTML = '<p class="text-gray-500">No tickets available for this event</p>';
            return;
        }

        let html = '';
        tickets.forEach(ticket => {
            const isAvailable = ticket.is_available;
            html += `
                <div class="border rounded-lg p-3 ${isAvailable ? 'border-gray-200 hover:border-blue-500' : 'border-gray-100 bg-gray-50'}">
                    <label class="flex items-center ${isAvailable ? 'cursor-pointer' : 'cursor-not-allowed'}">
                        <input type="radio" 
                               name="ticket_type_id" 
                               value="${ticket.id}" 
                               class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                               ${!isAvailable ? 'disabled' : ''}>
                        <div class="ml-3 flex-1">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="font-medium text-gray-900">${ticket.name}</p>
                                    <p class="text-sm text-gray-600">$${ticket.price}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm ${isAvailable ? 'text-green-600' : 'text-red-600'}">
                                        ${isAvailable ? 'Available' : 'Not Available'}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </label>
                </div>
            `;
        });

        ticketOptionsContainer.innerHTML = html;
    }

    function renderRegistrationFields(fields) {
        if (fields.length === 0) {
            dynamicFieldsContainer.innerHTML = '<p class="text-gray-500">No additional fields required for this event</p>';
            return;
        }

        let html = '';
        fields.forEach(field => {
            const fieldKey = field.field_key;
            const isRequired = field.is_required;
            const requiredStar = isRequired ? ' *' : '';
            const requiredAttr = isRequired ? 'required' : '';

            html += `<div class="mb-4">`;
            html += `<label for="${fieldKey}" class="block text-sm font-medium text-gray-700 mb-2">${field.field_name}${requiredStar}</label>`;

            // Render different field types
            switch(field.field_type) {
                case 'text':
                case 'email':
                case 'number':
                case 'phone':
                case 'date':
                case 'time':
                case 'url':
                    html += `<input type="${field.field_type}" name="${fieldKey}" id="${fieldKey}" 
                             class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                             ${requiredAttr}>`;
                    break;

                case 'textarea':
                    html += `<textarea name="${fieldKey}" id="${fieldKey}" rows="3" 
                             class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                             ${requiredAttr}></textarea>`;
                    break;

                case 'dropdown':
                    html += `<select name="${fieldKey}" id="${fieldKey}" 
                             class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                             ${requiredAttr}>`;
                    html += `<option value="">Select ${field.field_name}</option>`;
                    if (field.options && field.options.length > 0) {
                        field.options.forEach(option => {
                            html += `<option value="${option}">${option}</option>`;
                        });
                    }
                    html += `</select>`;
                    break;

                case 'radio':
                    if (field.options && field.options.length > 0) {
                        html += `<div class="space-y-2">`;
                        field.options.forEach(option => {
                            html += `
                                <label class="flex items-center">
                                    <input type="radio" name="${fieldKey}" value="${option}" 
                                           class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500" 
                                           ${requiredAttr}>
                                    <span class="ml-2 text-sm text-gray-700">${option}</span>
                                </label>
                            `;
                        });
                        html += `</div>`;
                    }
                    break;

                case 'checkbox':
                    if (field.options && field.options.length > 0) {
                        html += `<div class="space-y-2">`;
                        field.options.forEach(option => {
                            html += `
                                <label class="flex items-center">
                                    <input type="checkbox" name="${fieldKey}[]" value="${option}" 
                                           class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-gray-700">${option}</span>
                                </label>
                            `;
                        });
                        html += `</div>`;
                    } else {
                        html += `
                            <label class="flex items-center">
                                <input type="checkbox" name="${fieldKey}" value="1" 
                                       class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" 
                                       ${requiredAttr}>
                                <span class="ml-2 text-sm text-gray-700">Yes</span>
                            </label>
                        `;
                    }
                    break;

                default:
                    html += `<input type="text" name="${fieldKey}" id="${fieldKey}" 
                             class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                             ${requiredAttr}>`;
            }

            html += `</div>`;
        });

        dynamicFieldsContainer.innerHTML = html;
    }
});
</script>
@endpush
@endsection