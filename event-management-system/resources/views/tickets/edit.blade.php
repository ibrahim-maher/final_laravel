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
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('event_id') border-red-500 @enderror">
                            <option value="">Select an event</option>
                            @foreach($events as $event)
                                <option value="{{ $event->id }}" {{ old('event_id', $ticket->event_id) == $event->id ? 'selected' : '' }}>
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
                               value="{{ old('name', $ticket->name) }}"
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
                                  placeholder="Enter ticket description...">{{ old('description', $ticket->description ?? '') }}</textarea>
                        @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Describe what's included with this ticket type</p>
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
                                   {{ old('is_active', $ticket->is_active ?? true) ? 'checked' : '' }}
                                   class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2">
                        </div>
                        <div class="ml-3">
                            <label for="is_active" class="text-sm font-medium text-gray-700">
                                Activate Ticket
                            </label>
                            <p class="text-xs text-gray-500">Check this box to make the ticket available for registration. Uncheck to disable it.</p>
                        </div>
                    </div>

                    @if($ticket->registrations && $ticket->registrations->count() > 0)
                    <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg">
                        <div class="flex items-start">
                            <i class="fas fa-info-circle text-blue-600 mr-2 mt-0.5"></i>
                            <div class="text-sm text-blue-800">
                                <strong>Info:</strong> This ticket has {{ $ticket->registrations->count() }} existing registrations. 
                                Deactivating it will prevent new registrations but won't affect existing ones.
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Enhanced Preview Section -->
            <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Live Preview</h3>
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="flex items-start space-x-4">
                        <div class="w-16 h-16 bg-gradient-to-r from-blue-500 to-indigo-500 rounded-lg flex items-center justify-center">
                            <i class="fas fa-ticket-alt text-2xl text-white"></i>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-gray-900" id="preview-name">{{ $ticket->name }}</h4>
                            <p class="text-sm text-gray-600 mt-1" id="preview-event">{{ $ticket->event->name ?? 'Select an event' }}</p>
                            
                            @if($ticket->description ?? old('description'))
                            <p class="text-sm text-gray-500 mt-1" id="preview-description">{{ old('description', $ticket->description) }}</p>
                            @else
                            <p class="text-sm text-gray-500 mt-1" id="preview-description" style="display: none;"></p>
                            @endif
                            
                            <div class="flex items-center space-x-4 mt-2">
                                <p class="text-sm text-gray-500">
                                    <i class="fas fa-dollar-sign mr-1"></i>
                                    Price: $<span id="preview-price">{{ number_format($ticket->price, 2) }}</span>
                                </p>
                                <p class="text-sm text-gray-500">
                                    <i class="fas fa-ticket-alt mr-1"></i>
                                    Capacity: <span id="preview-capacity">{{ $ticket->capacity ? number_format($ticket->capacity) : 'Unlimited' }}</span>
                                </p>
                            </div>
                            
                            <p class="text-sm mt-2">
                                <span id="preview-status" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ ($ticket->is_active ?? true) ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    <span class="w-2 h-2 mr-1 {{ ($ticket->is_active ?? true) ? 'bg-green-400' : 'bg-red-400' }} rounded-full"></span>
                                    {{ ($ticket->is_active ?? true) ? 'Active' : 'Inactive' }}
                                </span>
                                
                                @if($ticket->registrations && $ticket->registrations->count() > 0)
                                <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    <i class="fas fa-users mr-1"></i>
                                    {{ $ticket->registrations->count() }} registered
                                </span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Current Statistics -->
            <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Current Statistics</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-blue-50 p-4 rounded-lg text-center">
                        <div class="text-2xl font-bold text-blue-600">{{ $ticket->registrations->count() ?? 0 }}</div>
                        <div class="text-sm text-gray-600">Tickets Sold</div>
                    </div>
                    <div class="bg-green-50 p-4 rounded-lg text-center">
                        <div class="text-2xl font-bold text-green-600">
                            {{ $ticket->capacity ? ($ticket->capacity - ($ticket->registrations->count() ?? 0)) : 'Unlimited' }}
                        </div>
                        <div class="text-sm text-gray-600">Remaining</div>
                    </div>
                    <div class="bg-purple-50 p-4 rounded-lg text-center">
                        <div class="text-2xl font-bold text-purple-600">
                            ${{ number_format(($ticket->registrations->count() ?? 0) * $ticket->price, 2) }}
                        </div>
                        <div class="text-sm text-gray-600">Total Revenue</div>
                    </div>
                    <div class="bg-orange-50 p-4 rounded-lg text-center">
                        <div class="text-2xl font-bold text-orange-600">
                            {{ $ticket->capacity ? number_format((($ticket->registrations->count() ?? 0) / $ticket->capacity) * 100, 1) : '0' }}%
                        </div>
                        <div class="text-sm text-gray-600">Capacity Used</div>
                    </div>
                </div>
                
                @if($ticket->registrations && $ticket->registrations->count() > 0)
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

            <!-- Availability Check -->
            <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Availability Status</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="p-4 border border-gray-200 rounded-lg">
                        <h4 class="font-medium text-gray-900 mb-2">Registration Availability</h4>
                        @php
                            $isAvailable = ($ticket->is_active ?? true) && 
                                          (!$ticket->capacity || ($ticket->capacity > ($ticket->registrations->count() ?? 0)));
                        @endphp
                        
                        @if($isAvailable)
                            <div class="flex items-center text-green-600">
                                <i class="fas fa-check-circle mr-2"></i>
                                <span class="text-sm font-medium">Available for registration</span>
                            </div>
                        @else
                            <div class="flex items-center text-red-600">
                                <i class="fas fa-times-circle mr-2"></i>
                                <span class="text-sm font-medium">
                                    @if(!($ticket->is_active ?? true))
                                        Inactive - not available for registration
                                    @elseif($ticket->capacity && $ticket->capacity <= ($ticket->registrations->count() ?? 0))
                                        Sold out - capacity reached
                                    @else
                                        Not available for registration
                                    @endif
                                </span>
                            </div>
                        @endif
                    </div>
                    
                    <div class="p-4 border border-gray-200 rounded-lg">
                        <h4 class="font-medium text-gray-900 mb-2">Capacity Status</h4>
                        @if($ticket->capacity)
                            @php
                                $percentage = ($ticket->registrations->count() ?? 0) / $ticket->capacity * 100;
                            @endphp
                            <div class="w-full bg-gray-200 rounded-full h-2 mb-2">
                                <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: {{ min($percentage, 100) }}%"></div>
                            </div>
                            <p class="text-sm text-gray-600">
                                {{ $ticket->registrations->count() ?? 0 }} of {{ number_format($ticket->capacity) }} sold
                                ({{ number_format($percentage, 1) }}%)
                            </p>
                        @else
                            <div class="flex items-center text-blue-600">
                                <i class="fas fa-infinity mr-2"></i>
                                <span class="text-sm font-medium">Unlimited capacity</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Danger Zone -->
            <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-semibold text-red-600 mb-4">Danger Zone</h3>
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <i class="fas fa-exclamation-triangle text-red-600 mr-3 mt-0.5"></i>
                        <div class="flex-1">
                            <h4 class="font-medium text-red-800">Delete Ticket</h4>
                            <p class="text-sm text-red-700 mt-1">
                                Once you delete this ticket, there is no going back. This action cannot be undone.
                                @if($ticket->registrations && $ticket->registrations->count() > 0)
                                    <br><strong>Warning:</strong> This ticket has {{ $ticket->registrations->count() }} associated registrations. Deleting it may cause data inconsistencies.
                                @endif
                            </p>
                            <div class="mt-3 flex space-x-3">
                                <form method="POST" action="{{ route('tickets.destroy', $ticket) }}" class="inline" onsubmit="return confirm('Are you absolutely sure you want to delete this ticket? This action cannot be undone and may affect {{ $ticket->registrations->count() ?? 0 }} existing registrations.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-4 py-2 bg-red-600 text-white text-sm rounded-lg hover:bg-red-700 transition-colors">
                                        <i class="fas fa-trash mr-2"></i>
                                        Delete Ticket
                                    </button>
                                </form>
                                
                                @if($ticket->registrations && $ticket->registrations->count() > 0)
                                <a href="{{ route('tickets.show', $ticket) }}" class="px-4 py-2 bg-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-400 transition-colors">
                                    <i class="fas fa-users mr-2"></i>
                                    View Registrations First
                                </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-between pt-6">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('tickets.show', $ticket) }}" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                        Cancel
                    </a>
                    <a href="{{ route('tickets.index') }}" class="px-6 py-2 text-gray-500 hover:text-gray-700 transition-colors">
                        <i class="fas fa-list mr-2"></i>
                        Back to List
                    </a>
                </div>
                
                <div class="flex items-center space-x-4">
                    <button type="button" id="save-and-continue" class="px-6 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                        <i class="fas fa-save mr-2"></i>
                        Save & Continue Editing
                    </button>
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-check mr-2"></i>
                        Update Ticket
                    </button>
                </div>
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
    const descriptionInput = document.getElementById('description');
    const priceInput = document.getElementById('price');
    const capacityInput = document.getElementById('capacity');
    const activeCheckbox = document.getElementById('is_active');
    
    const previewEvent = document.getElementById('preview-event');
    const previewName = document.getElementById('preview-name');
    const previewDescription = document.getElementById('preview-description');
    const previewPrice = document.getElementById('preview-price');
    const previewCapacity = document.getElementById('preview-capacity');
    const previewStatus = document.getElementById('preview-status');
    
    function updatePreview() {
        // Update event name
        const selectedEvent = eventSelect.options[eventSelect.selectedIndex];
        if (selectedEvent && selectedEvent.value) {
            previewEvent.textContent = selectedEvent.text;
        } else {
            previewEvent.textContent = 'Select an event';
        }
        
        // Update ticket name
        previewName.textContent = nameInput.value || 'Ticket Name';
        
        // Update description
        if (descriptionInput.value.trim()) {
            previewDescription.textContent = descriptionInput.value;
            previewDescription.style.display = 'block';
        } else {
            previewDescription.style.display = 'none';
        }
        
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
    
    // Add event listeners
    eventSelect.addEventListener('change', updatePreview);
    nameInput.addEventListener('input', updatePreview);
    descriptionInput.addEventListener('input', updatePreview);
    priceInput.addEventListener('input', updatePreview);
    capacityInput.addEventListener('input', updatePreview);
    activeCheckbox.addEventListener('change', updatePreview);
    
    // Save and Continue button functionality
    const saveAndContinueBtn = document.getElementById('save-and-continue');
    const form = document.querySelector('form');
    
    saveAndContinueBtn.addEventListener('click', function(e) {
        e.preventDefault();
        
        // Add a hidden input to indicate this is a "save and continue" action
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'save_and_continue';
        hiddenInput.value = '1';
        form.appendChild(hiddenInput);
        
        // Submit the form
        form.submit();
    });
    
    // Form validation feedback
    const requiredFields = [eventSelect, nameInput, priceInput];
    
    function validateForm() {
        let isValid = true;
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.classList.add('border-red-500');
                isValid = false;
            } else {
                field.classList.remove('border-red-500');
            }
        });
        
        return isValid;
    }
    
    // Real-time validation
    requiredFields.forEach(field => {
        field.addEventListener('blur', validateForm);
        field.addEventListener('input', validateForm);
    });
    
    // Initialize preview
    updatePreview();
    
    // Auto-save functionality (optional)
    let autoSaveTimeout;
    const autoSaveDelay = 30000; // 30 seconds
    
    function scheduleAutoSave() {
        clearTimeout(autoSaveTimeout);
        autoSaveTimeout = setTimeout(() => {
            if (validateForm()) {
                // You can implement auto-save here if needed
                console.log('Auto-save would trigger here');
            }
        }, autoSaveDelay);
    }
    
    // Schedule auto-save on input changes
    [nameInput, descriptionInput, priceInput, capacityInput].forEach(field => {
        field.addEventListener('input', scheduleAutoSave);
    });
    
    [eventSelect, activeCheckbox].forEach(field => {
        field.addEventListener('change', scheduleAutoSave);
    });
});
</script>
@endpush
@endsection