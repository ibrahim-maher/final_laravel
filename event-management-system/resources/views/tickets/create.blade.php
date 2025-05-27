@extends('layouts.app')

@section('title', 'Create Ticket')
@section('page-title', 'Create New Ticket')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Create New Ticket</h2>
            <p class="text-gray-600">Create a new ticket type for an event</p>
        </div>
        <a href="{{ route('tickets.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>
            Back to Tickets
        </a>
    </div>

    <!-- Create Form -->
    <div class="bg-white rounded-xl shadow-lg">
        <form method="POST" action="{{ route('tickets.store') }}" class="p-6 space-y-6">
            @csrf

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
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('event_id') border-red-500 @enderror">
                            <option value="">Select an event</option>
                            @foreach($events as $event)
                                <option value="{{ $event->id }}" {{ old('event_id') == $event->id ? 'selected' : '' }}>
                                    {{ $event->name }} - {{ $event->start_date->format('M d, Y') }}
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
                               value="{{ old('name') }}"
                               required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('name') border-red-500 @enderror"
                               placeholder="Enter ticket name (e.g., General Admission, VIP, Early Bird)">
                        @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                            Description (Optional)
                        </label>
                        <textarea id="description" 
                                  name="description" 
                                  rows="3"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('description') border-red-500 @enderror"
                                  placeholder="Enter ticket description...">{{ old('description') }}</textarea>
                        @error('description')
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
                                   value="{{ old('price', 0) }}"
                                   step="0.01"
                                   min="0"
                                   required
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
                                   value="{{ old('capacity') }}"
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

            <!-- Ticket Settings -->
            <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Ticket Settings</h3>
                
                <div class="space-y-4">
                    <!-- Active Status -->
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input id="is_active" 
                                   name="is_active" 
                                   type="checkbox" 
                                   value="1"
                                   {{ old('is_active', true) ? 'checked' : '' }}
                                   class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2">
                        </div>
                        <div class="ml-3">
                            <label for="is_active" class="text-sm font-medium text-gray-700">
                                Activate Ticket
                            </label>
                            <p class="text-xs text-gray-500">Check this box to make the ticket available for registration. Uncheck to disable it.</p>
                        </div>
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
                            <h4 class="font-semibold text-gray-900" id="preview-name">Ticket Name</h4>
                            <p class="text-sm text-gray-600 mt-1" id="preview-event">Select an event</p>
                            <p class="text-sm text-gray-500 mt-2">
                                <i class="fas fa-dollar-sign mr-1"></i>
                                Price: $<span id="preview-price">0.00</span>
                            </p>
                            <p class="text-sm text-gray-500 mt-1">
                                <i class="fas fa-ticket-alt mr-1"></i>
                                Capacity: <span id="preview-capacity">Unlimited</span>
                            </p>
                            <p class="text-sm mt-1">
                                <span id="preview-status" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <span class="w-2 h-2 mr-1 bg-green-400 rounded-full"></span>
                                    Active
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end space-x-4 pt-6">
                <a href="{{ route('tickets.index') }}" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-save mr-2"></i>
                    Create Ticket
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
    const activeCheckbox = document.getElementById('is_active');
    
    const previewEvent = document.getElementById('preview-event');
    const previewName = document.getElementById('preview-name');
    const previewPrice = document.getElementById('preview-price');
    const previewCapacity = document.getElementById('preview-capacity');
    const previewStatus = document.getElementById('preview-status');
    
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
        
        // Update status
        if (activeCheckbox.checked) {
            previewStatus.innerHTML = '<span class="w-2 h-2 mr-1 bg-green-400 rounded-full"></span>Active';
            previewStatus.className = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800';
        } else {
            previewStatus.innerHTML = '<span class="w-2 h-2 mr-1 bg-red-400 rounded-full"></span>Inactive';
            previewStatus.className = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800';
        }
    }
    
    eventSelect.addEventListener('change', updatePreview);
    nameInput.addEventListener('input', updatePreview);
    priceInput.addEventListener('input', updatePreview);
    capacityInput.addEventListener('input', updatePreview);
    activeCheckbox.addEventListener('change', updatePreview);
    
    // Initialize preview
    updatePreview();
});
</script>
@endpush
@endsection