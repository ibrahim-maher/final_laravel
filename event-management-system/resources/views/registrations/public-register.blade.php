{{-- resources/views/registrations/public-register.blade.php --}}
@extends('layouts.app')

@section('title', 'Event Registration')

@section('content')
<div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Event Registration</h1>
            <p class="mt-2 text-gray-600">Register for upcoming events</p>
        </div>

        <!-- Event Selection -->
        <div class="bg-white shadow-lg rounded-lg border border-gray-200 p-6 mb-8">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Select Event</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($events as $event)
                <div class="event-card border border-gray-200 rounded-lg p-4 cursor-pointer hover:bg-blue-50 transition-colors"
                     data-event-id="{{ $event->id }}"
                     data-event-name="{{ $event->name }}">
                    <h3 class="font-semibold text-gray-900 mb-2">{{ $event->name }}</h3>
                    <p class="text-sm text-gray-600 mb-3">{{ Str::limit($event->description, 100) }}</p>
                    
                    <div class="flex items-center text-sm text-gray-500 mb-2">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        {{ $event->start_date->format('M d, Y') }}
                    </div>
                    
                    <div class="flex items-center text-sm text-gray-500 mb-3">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        {{ $event->venue ? $event->venue->name : 'TBD' }}
                    </div>

                    @if($event->tickets->count() > 0)
                    <div class="text-sm text-green-600 font-medium">
                        {{ $event->tickets->count() }} ticket type(s) available
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>

        <!-- Registration Form -->
        <div id="registration-form" class="bg-white shadow-lg rounded-lg border border-gray-200 p-6" style="display: none;">
            <div class="mb-6">
                <h2 class="text-xl font-semibold text-gray-800">Registration Details</h2>
                <p class="text-gray-600">Event: <span id="selected-event-name" class="font-medium"></span></p>
            </div>

            @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form id="dynamic-registration-form" method="POST" action="{{ route('registrations.public-register') }}">
                @csrf
                <input type="hidden" name="event_id" id="event_id">

                <!-- Ticket Selection -->
                <div id="ticket-selection" class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-3">Select Ticket Type</label>
                    <div id="ticket-options" class="space-y-3">
                        <!-- Ticket options will be loaded here -->
                    </div>
                </div>

                <!-- Dynamic Fields -->
                <div id="dynamic-fields" class="space-y-6">
                    <!-- Dynamic fields will be loaded here -->
                </div>

                <!-- Submit Button -->
                <div class="mt-8">
                    <button type="submit" 
                            class="w-full px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
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

    // Event selection
    eventCards.forEach(card => {
        card.addEventListener('click', function() {
            // Remove previous selection
            eventCards.forEach(c => c.classList.remove('bg-blue-50', 'border-blue-500'));
            
            // Add selection to current card
            this.classList.add('bg-blue-50', 'border-blue-500');
            
            const eventId = this.getAttribute('data-event-id');
            const eventName = this.getAttribute('data-event-name');
            
            // Update form
            eventIdInput.value = eventId;
            selectedEventName.textContent = eventName;
            
            // Load event data
            loadEventData(eventId);
            
            // Show registration form
            registrationForm.style.display = 'block';
            registrationForm.scrollIntoView({ behavior: 'smooth' });
        });
    });

    function loadEventData(eventId) {
        // Load tickets
        fetch(`/api/events/${eventId}/tickets`)
            .then(response => response.json())
            .then(tickets => {
                renderTicketOptions(tickets);
            })
            .catch(error => {
                console.error('Error loading tickets:', error);
            });

        // Load registration fields
        fetch(`/api/events/${eventId}/registration-fields`)
            .then(response => response.json())
            .then(fields => {
                renderDynamicFields(fields);
            })
            .catch(error => {
                console.error('Error loading registration fields:', error);
            });
    }

    function renderTicketOptions(tickets) {
        ticketOptions.innerHTML = '';
        
        if (tickets.length === 0) {
            ticketOptions.innerHTML = '<p class="text-gray-500">No tickets available for this event.</p>';
            return;
        }

        tickets.forEach((ticket, index) => {
            const isAvailable = ticket.is_available;
            const availabilityText = ticket.available_spaces !== null ? 
                `${ticket.available_spaces} spaces left` : 'Unlimited';

            const ticketDiv = document.createElement('div');
            ticketDiv.className = `border rounded-lg p-4 ${isAvailable ? 'border-gray-200 hover:border-blue-500 cursor-pointer' : 'border-gray-100 bg-gray-50 cursor-not-allowed'}`;
            
            ticketDiv.innerHTML = `
                <label class="flex items-center ${isAvailable ? 'cursor-pointer' : 'cursor-not-allowed'}">
                    <input type="radio" 
                           name="ticket_type_id" 
                           value="${ticket.id}" 
                           class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                           ${!isAvailable ? 'disabled' : ''}
                           ${index === 0 ? 'checked' : ''}>
                    <div class="ml-3 flex-1">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="font-medium text-gray-900">${ticket.name}</p>
                                <p class="text-sm text-gray-600">${ticket.formatted_price}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm ${isAvailable ? 'text-green-600' : 'text-red-600'}">
                                    ${isAvailable ? availabilityText : 'Not Available'}
                                </p>
                            </div>
                        </div>
                    </div>
                </label>
            `;
            
            ticketOptions.appendChild(ticketDiv);
        });
    }

    function renderDynamicFields(fields) {
        dynamicFields.innerHTML = '';
        
        fields.forEach(field => {
            const fieldDiv = document.createElement('div');
            fieldDiv.className = 'mb-4';
            
            const label = document.createElement('label');
            label.className = 'block text-sm font-medium text-gray-700 mb-2';
            label.textContent = field.field_name + (field.is_required ? ' *' : '');
            
            let input;
            const fieldName = field.field_key;
            
            switch(field.field_type) {
                case 'text':
                    input = document.createElement('input');
                    input.type = 'text';
                    input.className = 'w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent';
                    break;
                    
                case 'email':
                    input = document.createElement('input');
                    input.type = 'email';
                    input.className = 'w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent';
                    break;
                    
                case 'number':
                    input = document.createElement('input');
                    input.type = 'number';
                    input.className = 'w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent';
                    break;
                    
                case 'phone':
                    input = document.createElement('input');
                    input.type = 'tel';
                    input.className = 'w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent';
                    break;
                    
                case 'date':
                    input = document.createElement('input');
                    input.type = 'date';
                    input.className = 'w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent';
                    break;
                    
                case 'textarea':
                    input = document.createElement('textarea');
                    input.rows = 3;
                    input.className = 'w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent';
                    break;
                    
                case 'dropdown':
                    input = document.createElement('select');
                    input.className = 'w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent';
                    
                    // Add default option
                    const defaultOption = document.createElement('option');
                    defaultOption.value = '';
                    defaultOption.textContent = 'Select...';
                    input.appendChild(defaultOption);
                    
                    // Add options
                    field.options.forEach(option => {
                        const optionElement = document.createElement('option');
                        optionElement.value = option;
                        optionElement.textContent = option;
                        input.appendChild(optionElement);
                    });
                    break;
                    
                case 'checkbox':
                    input = document.createElement('input');
                    input.type = 'checkbox';
                    input.className = 'h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500';
                    break;
                    
                default:
                    input = document.createElement('input');
                    input.type = 'text';
                    input.className = 'w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent';
            }
            
            input.name = fieldName;
            input.id = fieldName;
            
            if (field.is_required) {
                input.required = true;
            }
            
            fieldDiv.appendChild(label);
            
            if (field.field_type === 'checkbox') {
                const checkboxDiv = document.createElement('div');
                checkboxDiv.className = 'flex items-center';
                checkboxDiv.appendChild(input);
                
                const checkboxLabel = document.createElement('label');
                checkboxLabel.className = 'ml-2 text-sm text-gray-700';
                checkboxLabel.textContent = 'Yes';
                checkboxLabel.setAttribute('for', fieldName);
                checkboxDiv.appendChild(checkboxLabel);
                
                fieldDiv.appendChild(checkboxDiv);
            } else {
                fieldDiv.appendChild(input);
            }
            
            dynamicFields.appendChild(fieldDiv);
        });
    }
});
</script>
@endpush
@endsection