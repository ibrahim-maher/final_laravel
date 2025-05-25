@extends('layouts.app')

@section('title', 'Create Badge Template')
@section('page-title', 'Create Badge Template')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center space-x-4">
        <a href="{{ route('badge-templates.index') }}" class="text-gray-600 hover:text-gray-900">
            <i class="fas fa-arrow-left mr-2"></i>
            Back to Templates
        </a>
    </div>

    <!-- Event Selection -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-6">Select Event and Ticket</h2>
        
        <form method="GET" action="{{ route('badge-templates.createOrEdit') }}" id="eventTicketForm">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Event Selection -->
                <div>
                    <label for="event_id" class="block text-sm font-medium text-gray-700 mb-2">Event</label>
                    <select name="event_id" id="event_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                        <option value="">Select an event...</option>
                        @foreach($events as $event)
                        <option value="{{ $event->id }}">{{ $event->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Ticket Selection -->
                <div>
                    <label for="ticket_id" class="block text-sm font-medium text-gray-700 mb-2">Ticket Type</label>
                    <select name="ticket" id="ticket_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" disabled required>
                        <option value="">Select a ticket type...</option>
                    </select>
                </div>
            </div>

            <div class="mt-6">
                <button type="submit" id="continueBtn" class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                    <i class="fas fa-arrow-right mr-2"></i>
                    Continue to Template Creation
                </button>
            </div>
        </form>
    </div>

    <!-- Quick Start Guide -->
    <div class="bg-gradient-to-r from-blue-50 to-purple-50 rounded-xl p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">
            <i class="fas fa-lightbulb text-yellow-500 mr-2"></i>
            Quick Start Guide
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
            <div class="flex items-start space-x-3">
                <div class="w-6 h-6 bg-blue-500 text-white rounded-full flex items-center justify-center text-xs font-bold">1</div>
                <div>
                    <p class="font-medium text-gray-900">Select Event & Ticket</p>
                    <p class="text-gray-600">Choose which event and ticket type this badge template will be used for.</p>
                </div>
            </div>
            <div class="flex items-start space-x-3">
                <div class="w-6 h-6 bg-blue-500 text-white rounded-full flex items-center justify-center text-xs font-bold">2</div>
                <div>
                    <p class="font-medium text-gray-900">Design Template</p>
                    <p class="text-gray-600">Set badge dimensions, background image, and add content fields.</p>
                </div>
            </div>
            <div class="flex items-start space-x-3">
                <div class="w-6 h-6 bg-blue-500 text-white rounded-full flex items-center justify-center text-xs font-bold">3</div>
                <div>
                    <p class="font-medium text-gray-900">Preview & Adjust</p>
                    <p class="text-gray-600">Use the interactive preview to fine-tune field positions and styling.</p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const eventSelect = document.getElementById('event_id');
    const ticketSelect = document.getElementById('ticket_id');
    const continueBtn = document.getElementById('continueBtn');

    eventSelect.addEventListener('change', async function() {
        const eventId = this.value;
        
        // Reset ticket selection
        ticketSelect.innerHTML = '<option value="">Select a ticket type...</option>';
        ticketSelect.disabled = !eventId;
        continueBtn.disabled = true;

        if (eventId) {
            try {
                const response = await fetch(`{{ route('badge-templates.getTickets') }}?event_id=${eventId}`);
                const tickets = await response.json();
                
                tickets.forEach(ticket => {
                    const option = new Option(ticket.name, ticket.id);
                    ticketSelect.add(option);
                });
                
                ticketSelect.disabled = false;
            } catch (error) {
                console.error('Error fetching tickets:', error);
                alert('Failed to load tickets. Please try again.');
            }
        }
    });

    ticketSelect.addEventListener('change', function() {
        continueBtn.disabled = !this.value;
    });
});
</script>
@endpush
@endsection