@extends('layouts.app')

@section('title', 'Create Registration')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- CSRF Token for JavaScript -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Header -->
    <div class="mb-6">
        <nav class="flex mb-4" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('registrations.index') }}" class="text-gray-700 hover:text-blue-600">Registrations</a>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="ml-1 text-gray-500 md:ml-2">Create Registration</span>
                    </div>
                </li>
            </ol>
        </nav>
        
        <h1 class="text-3xl font-bold text-gray-900">Create New Registration</h1>
        <p class="text-gray-600 mt-2">Register a participant for an event</p>
    </div>

    <!-- Messages -->
    @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
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
        
        <form action="{{ route('registrations.store') }}" method="POST" class="p-6">
            @csrf
            
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
                            <option value="{{ $event->id }}" {{ old('event_id') == $event->id ? 'selected' : '' }}>
                                {{ $event->name }} - {{ $event->start_date->format('M d, Y') }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- User Selection -->
                <div>
                    <label for="user_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Existing User (Optional)
                    </label>
                    <select name="user_id" id="user_id" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Create New User</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }} ({{ $user->email }})
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Leave blank to create a new user from form data</p>
                </div>
            </div>

            <!-- Ticket Type Selection -->
            <div id="ticket-types-container" class="mb-6">
                <label for="ticket_type_id" class="block text-sm font-medium text-gray-700 mb-2">Ticket Type</label>
                <div id="ticket-options" class="space-y-3">
                    <p class="text-gray-500">Please select an event first</p>
                </div>
            </div>

            <!-- Dynamic Registration Fields -->
            <div id="dynamic-fields" class="mb-6">
                <div class="border-t border-gray-200 pt-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Registration Information</h3>
                    <div id="dynamic-fields-container" class="space-y-4">
                        <p class="text-gray-500">Please select an event to see registration fields</p>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                <a href="{{ route('registrations.index') }}" 
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
                    Create Registration
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

        // Load tickets and fields
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
        tickets.forEach((ticket, index) => {
            const isAvailable = ticket.is_available;
            const availabilityText = ticket.available_spaces !== null ? 
                `${ticket.available_spaces} spaces left` : 'Unlimited';

            html += `
                <div class="border rounded-lg p-4 ${isAvailable ? 'border-gray-200 hover:border-blue-500' : 'border-gray-100 bg-gray-50'}">
                    <label class="flex items-center ${isAvailable ? 'cursor-pointer' : 'cursor-not-allowed'}">
                        <input type="radio" 
                               name="ticket_type_id" 
                               value="${ticket.id}" 
                               class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                               ${!isAvailable ? 'disabled' : ''}
                               ${index === 0 && isAvailable ? 'checked' : ''}>
                        <div class="ml-3 flex-1">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="font-medium text-gray-900">${ticket.name}</p>
                                    <p class="text-sm text-gray-600">$${ticket.price}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm ${isAvailable ? 'text-green-600' : 'text-red-600'}">
                                        ${isAvailable ? availabilityText : 'Not Available'}
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

            switch(field.field_type) {
                case 'text':
                    html += `<input type="text" name="${fieldKey}" id="${fieldKey}" 
                             class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                             ${requiredAttr}>`;
                    break;

                case 'email':
                    html += `<input type="email" name="${fieldKey}" id="${fieldKey}" 
                             class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                             ${requiredAttr}>`;
                    break;

                case 'number':
                    html += `<input type="number" name="${fieldKey}" id="${fieldKey}" 
                             class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                             ${requiredAttr}>`;
                    break;

                case 'phone':
                    html += `<input type="tel" name="${fieldKey}" id="${fieldKey}" 
                             class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                             ${requiredAttr}>`;
                    break;

                case 'date':
                    html += `<input type="date" name="${fieldKey}" id="${fieldKey}" 
                             class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                             ${requiredAttr}>`;
                    break;

                case 'time':
                    html += `<input type="time" name="${fieldKey}" id="${fieldKey}" 
                             class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                             ${requiredAttr}>`;
                    break;

                case 'url':
                    html += `<input type="url" name="${fieldKey}" id="${fieldKey}" 
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
                        field.options.forEach((option, index) => {
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

    // Load data if event is pre-selected (for edit mode or validation errors)
    if (eventSelect.value) {
        Promise.all([
            loadTickets(eventSelect.value),
            loadRegistrationFields(eventSelect.value)
        ]).catch(error => {
            console.error('Error loading initial event data:', error);
        });
    }
});
</script>
@endpush
@endsection