@extends('layouts.app')

@section('title', 'Event Registration')

@section('content')
<div class="min-h-screen bg-gray-50 py-12">
    <!-- CSRF Token for JavaScript -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-900 mb-2">Event Registration</h1>
            <p class="text-lg text-gray-600">Register for upcoming events</p>
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

        @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            {{ session('error') }}
        </div>
        @endif

        <!-- Event Selection -->
        <div class="bg-white shadow-lg rounded-lg border border-gray-200 p-6 mb-8">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Select Event</h2>
            
            @if($events->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($events as $event)
                <div class="event-card border border-gray-200 rounded-lg p-4 cursor-pointer hover:bg-blue-50 hover:border-blue-300 transition-all duration-200"
                     data-event-id="{{ $event->id }}"
                     data-event-name="{{ $event->name }}">
                    
                    <div class="mb-3">
                        <h3 class="font-semibold text-gray-900 mb-2">{{ $event->name }}</h3>
                        @if($event->description)
                        <p class="text-sm text-gray-600 mb-3">{{ Str::limit($event->description, 100) }}</p>
                        @endif
                    </div>
                    
                    <div class="space-y-2">
                        <div class="flex items-center text-sm text-gray-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            {{ $event->start_date->format('M d, Y') }}
                            @if($event->start_time)
                                at {{ $event->start_time->format('g:i A') }}
                            @endif
                        </div>
                        
                        @if($event->venue)
                        <div class="flex items-center text-sm text-gray-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            {{ $event->venue->name }}
                        </div>
                        @endif

                        @if($event->tickets && $event->tickets->count() > 0)
                        <div class="flex items-center text-sm text-green-600 font-medium">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 011-1V6a1 1 0 01-1-1h9a1 1 0 00-1-1H5z"></path>
                            </svg>
                            {{ $event->tickets->count() }} ticket type(s) available
                        </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-8">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No Events Available</h3>
                <p class="mt-1 text-sm text-gray-500">There are no events currently open for registration.</p>
            </div>
            @endif
        </div>

        <!-- Registration Form -->
        <div id="registration-form" class="bg-white shadow-lg rounded-lg border border-gray-200 p-6" style="display: none;">
            <div class="mb-6">
                <h2 class="text-xl font-semibold text-gray-800">Registration Details</h2>
                <p class="text-gray-600">Event: <span id="selected-event-name" class="font-medium text-blue-600"></span></p>
            </div>

            <form id="dynamic-registration-form" method="POST" action="{{ route('admin.registrations.public-register.store') }}">
                @csrf
                <input type="hidden" name="event_id" id="event_id">

                <!-- Ticket Selection -->
                <div id="ticket-selection" class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-3">Select Ticket Type *</label>
                    <div id="ticket-options" class="space-y-3">
                        <!-- Ticket options will be loaded here -->
                    </div>
                </div>

                <!-- Dynamic Fields -->
                <div id="dynamic-fields" class="space-y-6 mb-6">
                    <!-- Dynamic fields will be loaded here -->
                </div>

                <!-- Loading indicator -->
                <div id="loading-indicator" class="hidden text-center py-4">
                    <div class="inline-flex items-center">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Loading event details...
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="mt-8 pt-6 border-t border-gray-200">
                    <button type="submit" 
                            class="w-full px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors duration-200">
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Complete Registration
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const eventCards = document.querySelectorAll('.event-card');
    const registrationForm = document.getElementById('registration-form');
    const selectedEventName = document.getElementById('selected-event-name');
    const eventIdInput = document.getElementById('event_id');
    const ticketOptions = document.getElementById('ticket-options');
    const dynamicFields = document.getElementById('dynamic-fields');
    const loadingIndicator = document.getElementById('loading-indicator');

    // Get CSRF token
    function getCSRFToken() {
        return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    }

    // Event selection
    eventCards.forEach(card => {
        card.addEventListener('click', function() {
            // Remove previous selection
            eventCards.forEach(c => {
                c.classList.remove('bg-blue-50', 'border-blue-500', 'ring-2', 'ring-blue-200');
            });
            
            // Add selection to current card
            this.classList.add('bg-blue-50', 'border-blue-500', 'ring-2', 'ring-blue-200');
            
            const eventId = this.getAttribute('data-event-id');
            const eventName = this.getAttribute('data-event-name');
            
            // Update form
            eventIdInput.value = eventId;
            selectedEventName.textContent = eventName;
            
            // Show loading and load event data
            showLoading();
            loadEventData(eventId);
            
            // Show registration form
            registrationForm.style.display = 'block';
            registrationForm.scrollIntoView({ behavior: 'smooth' });
        });
    });

    function showLoading() {
        loadingIndicator.classList.remove('hidden');
        ticketOptions.innerHTML = '';
        dynamicFields.innerHTML = '';
    }

    function hideLoading() {
        loadingIndicator.classList.add('hidden');
    }

    function loadEventData(eventId) {
        Promise.all([
            loadTickets(eventId),
            loadRegistrationFields(eventId)
        ])
        .then(() => {
            hideLoading();
        })
        .catch(error => {
            hideLoading();
            console.error('Error loading event data:', error);
            showError('Failed to load event details. Please try again.');
        });
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
            renderTicketOptions(tickets);
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
            renderDynamicFields(fields);
        });
    }

    function renderTicketOptions(tickets) {
        if (tickets.length === 0) {
            ticketOptions.innerHTML = '<p class="text-red-500">No tickets available for this event.</p>';
            return;
        }

        let html = '';
        tickets.forEach((ticket, index) => {
            const isAvailable = ticket.is_available;
            const availabilityText = ticket.available_spaces !== null ? 
                `${ticket.available_spaces} spaces left` : 'Unlimited spaces';

            html += `
                <div class="border rounded-lg p-4 ${isAvailable ? 'border-gray-200 hover:border-blue-500 hover:bg-blue-50' : 'border-gray-100 bg-gray-50'} transition-colors">
                    <label class="flex items-center ${isAvailable ? 'cursor-pointer' : 'cursor-not-allowed'}">
                        <input type="radio" 
                               name="ticket_type_id" 
                               value="${ticket.id}" 
                               class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                               ${!isAvailable ? 'disabled' : ''}
                               ${index === 0 && isAvailable ? 'checked' : ''}
                               required>
                        <div class="ml-3 flex-1">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="font-medium text-gray-900">${ticket.name}</p>
                                    <p class="text-lg font-semibold text-green-600">$${ticket.price}</p>
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

        ticketOptions.innerHTML = html;
    }

    function renderDynamicFields(fields) {
        if (fields.length === 0) {
            dynamicFields.innerHTML = '<p class="text-gray-500">No additional information required for this event.</p>';
            return;
        }

        let html = '<h3 class="text-lg font-medium text-gray-900 mb-4">Registration Information</h3>';
        
        fields.forEach(field => {
            const fieldName = field.field_key;
            const isRequired = field.is_required;
            const requiredStar = isRequired ? ' *' : '';
            const requiredAttr = isRequired ? 'required' : '';

            html += `<div class="mb-4">`;
            html += `<label for="${fieldName}" class="block text-sm font-medium text-gray-700 mb-2">${field.field_name}${requiredStar}</label>`;
            
            switch(field.field_type) {
                case 'text':
                    html += `<input type="text" name="${fieldName}" id="${fieldName}" 
                             class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                             placeholder="Enter ${field.field_name.toLowerCase()}" 
                             ${requiredAttr}>`;
                    break;
                    
                case 'email':
                    html += `<input type="email" name="${fieldName}" id="${fieldName}" 
                             class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                             placeholder="Enter your email address" 
                             ${requiredAttr}>`;
                    break;
                    
                case 'number':
                    html += `<input type="number" name="${fieldName}" id="${fieldName}" 
                             class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                             placeholder="Enter a number"
                             ${requiredAttr}>`;
                    break;
                    
                case 'phone':
                    html += `<input type="tel" name="${fieldName}" id="${fieldName}" 
                             class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                             placeholder="Enter your phone number"
                             ${requiredAttr}>`;
                    break;
                    
                case 'date':
                    html += `<input type="date" name="${fieldName}" id="${fieldName}" 
                             class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                             ${requiredAttr}>`;
                    break;

                case 'time':
                    html += `<input type="time" name="${fieldName}" id="${fieldName}" 
                             class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                             ${requiredAttr}>`;
                    break;

                case 'url':
                    html += `<input type="url" name="${fieldName}" id="${fieldName}" 
                             class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                             placeholder="https://example.com"
                             ${requiredAttr}>`;
                    break;
                    
                case 'textarea':
                    html += `<textarea name="${fieldName}" id="${fieldName}" rows="3" 
                             class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                             placeholder="Enter ${field.field_name.toLowerCase()}"
                             ${requiredAttr}></textarea>`;
                    break;
                    
                case 'dropdown':
                    html += `<select name="${fieldName}" id="${fieldName}" 
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
                                    <input type="radio" name="${fieldName}" value="${option}" 
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
                                    <input type="checkbox" name="${fieldName}[]" value="${option}" 
                                           class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-gray-700">${option}</span>
                                </label>
                            `;
                        });
                        html += `</div>`;
                    } else {
                        html += `
                            <label class="flex items-center">
                                <input type="checkbox" name="${fieldName}" value="1" 
                                       class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                       ${requiredAttr}>
                                <span class="ml-2 text-sm text-gray-700">Yes</span>
                            </label>
                        `;
                    }
                    break;
                    
                default:
                    html += `<input type="text" name="${fieldName}" id="${fieldName}" 
                             class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                             ${requiredAttr}>`;
            }
            
            html += `</div>`;
        });
        
        dynamicFields.innerHTML = html;
    }

    function showError(message) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4';
        errorDiv.innerHTML = message;
        
        registrationForm.insertBefore(errorDiv, registrationForm.firstChild);
        
        setTimeout(() => {
            errorDiv.remove();
        }, 5000);
    }
});
</script>
@endpush
@endsection