
@extends('layouts.app')

@section('title', 'Edit Ticket')
@section('page-title', 'Edit Ticket')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Edit Ticket</h2>
            <p class="text-gray-600">Update ticket information and settings</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('tickets.show', $ticket) }}" class="inline-flex items-center px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">
                <i class="fas fa-eye mr-2"></i>
                View Ticket
            </a>
            <a href="{{ route('tickets.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Tickets
            </a>
        </div>
    </div>

    <!-- Edit Form -->
    <div class="bg-white rounded-xl shadow-lg">
        <form method="POST" action="{{ route('tickets.update', $ticket) }}" class="p-6 space-y-6">
            @csrf
            @method('PATCH')

            <!-- Ticket Information -->
            <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Ticket Information</h3>
                
                <div class="space-y-6">
                    <!-- Event Selection -->
                    <div>
                        <label for="event_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Event <span class="text-red-500">*</span>
                        </label>
                        <select id="event_id" 
                                name="event_id" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('event_id') border-red-500 @enderror">
                            <option value="">Select an event</option>
                            @foreach($events as $event)
                                <option value="{{ $event->id }}" {{ old('event_id', $ticket->event_id) == $event->id ? 'selected' : '' }}>
                                    {{ $event->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('event_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Ticket Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            Ticket Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               value="{{ old('name', $ticket->name) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('name') border-red-500 @enderror"
                               placeholder="Enter ticket name (e.g., General Admission)">
                        @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Price -->
                    <div>
                        <label for="price" class="block text-sm font-medium text-gray-700 mb-2">
                            Price <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500 text-sm">$</span>
                            <input type="number" 
                                   id="price" 
                                   name="price" 
                                   value="{{ old('price', $ticket->price) }}"
                                   step="0.01"
                                   min="0"
                                   class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('price') border-red-500 @enderror"
                                   placeholder="e.g., 50.00">
                        </div>
                        @error('price')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Capacity -->
                    <div>
                        <label for="capacity" class="block text-sm font-medium text-gray-700 mb-2">
                            Capacity (Optional)
                        </label>
                        <div class="relative">
                            <input type="number" 
                                   id="capacity" 
                                   name="capacity" 
                                   value="{{ old('capacity', $ticket->capacity) }}"
                                   min="1"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('capacity') border-red-500 @enderror"
                                   placeholder="e.g., 100">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 text-sm">tickets</span>
                            </div>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Leave blank for unlimited capacity</p>
                        @error('capacity')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Preview Section -->
            <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Preview</h3>
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="flex items-start space-x-4">
                        <div class="w-16 h-16 bg-gradient-to-r from-blue-500 to-indigo-500 rounded-lg flex items-center justify-center">
                            <i class="fas fa-ticket-alt text-2xl text-white"></i>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-gray-900" id="preview-name">{{ $ticket->name }}</h4>
                            <p class="text-sm text-gray-600 mt-1" id="preview-event">{{ $ticket->event->name ?? 'Select an event' }}</p>
                            <p class="text-sm text-gray-500 mt-2">
                                <i class="fas fa-dollar-sign mr-1"></i>
                                Price: $<span id="preview-price">{{ number_format($ticket->price, 2) }}</span>
                            </p>
                            <p class="text-sm text-gray-500 mt-1">
                                <i class="fas fa-ticket-alt mr-1"></i>
                                Capacity: <span id="preview-capacity">{{ $ticket->capacity ? number_format($ticket->capacity) : 'Unlimited' }}</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Current Statistics -->
            <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Current Statistics</h3>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    <div class="bg-blue-50 p-4 rounded-lg text-center">
                        <div class="text-2xl font-bold text-blue-600">{{ $ticket->registrations->count() }}</div>
                        <div class="text-sm text-gray-600">Tickets Sold</div>
                    </div>
                    <div class="bg-green-50 p-4 rounded-lg text-center">
                        <div class="text-2xl font-bold text-green-600">{{ $ticket->capacity ? ($ticket->capacity - $ticket->registrations->count()) : 'Unlimited' }}</div>
                        <div class="text-sm text-gray-600">Tickets Remaining</div>
                    </div>
                    <div class="bg-purple-50 p-4 rounded-lg text-center">
                        <div class="text-2xl font-bold text-purple-600">${{ number_format($ticket->registrations->sum(function($registration) { return $ticket->price; }), 2) }}</div>
                        <div class="text-sm text-gray-600">Total Revenue</div>
                    </div>
                </div>
                @if($ticket->registrations->count() > 0)
                <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <div class="flex items-start">
                        <i class="fas fa-info-circle text-yellow-600 mr-2 mt-0.5"></i>
                        <div class="text-sm text-yellow-800">
                            <strong>Note:</strong> This ticket has {{ $ticket->registrations->count() }} associated registrations. 
                            Changing the price or capacity will not affect existing registrations, but consider the impact on future registrations.
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Danger Zone -->
            <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-semibold text-red-600 mb-4">Danger Zone</h3>
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <i class="fas fa-exclamation-triangle text-red-600 mr-3 mt-0.5"></i>
                        <div>
                            <h4 class="font-medium text-red-800">Delete Ticket</h4>
                            <p class="text-sm text-red-700 mt-1">
                                Once you delete this ticket, there is no going back. This action cannot be undone.
                                @if($ticket->registrations->count() > 0)
                                    <br><strong>Warning:</strong> This ticket has {{ $ticket->registrations->count() }} associated registrations. Deleting it may cause data inconsistencies.
                                @endif
                            </p>
                            <form method="POST" action="{{ route('tickets.destroy', $ticket) }}" class="mt-3" onsubmit="return confirm('Are you absolutely sure you want to delete this ticket? This action cannot be undone.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-4 py-2 bg-red-600 text-white text-sm rounded-lg hover:bg-red-700 transition-colors">
                                    <i class="fas fa-trash mr-2"></i>
                                    Delete Ticket
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end space-x-4 pt-6">
                <a href="{{ route('tickets.show', $ticket) }}" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-save mr-2"></i>
                    Update Ticket
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form preview functionality
    const eventSelect = document.getElementById('event_id');
    const nameInput = document.getElementById('name');
    const priceInput = document.getElementById('price');
    const capacityInput = document.getElementById('capacity');
    
    const previewEvent = document.getElementById('preview-event');
    const previewName = document.getElementById('preview-name');
    const previewPrice = document.getElementById('preview-price');
    const previewCapacity = document.getElementById('preview-capacity');
    
    function updatePreview() {
        // Update event name
        const selectedEvent = eventSelect.options[eventSelect.selectedIndex];
        previewEvent.textContent = selectedEvent ? (selectedEvent.text || 'Select an event') : 'Select an event';
        
        // Update ticket name
        previewName.textContent = nameInput.value || 'Ticket Name';
        
        // Update price
        previewPrice.textContent = priceInput.value ? parseFloat(priceInput.value).toFixed(2) : '0.00';
        
        // Update capacity
        previewCapacity.textContent = capacityInput.value ? parseInt(capacityInput.value).toLocaleString() : 'Unlimited';
    }
    
    eventSelect.addEventListener('change', updatePreview);
    nameInput.addEventListener('input', updatePreview);
    priceInput.addEventListener('input', updatePreview);
    capacityInput.addEventListener('input', updatePreview);
    
    // Initialize preview
    updatePreview();
});
</script>
@endpush
@endsection
