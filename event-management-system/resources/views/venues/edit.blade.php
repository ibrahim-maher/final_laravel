@extends('layouts.app')

@section('title', 'Edit Venue')
@section('page-title', 'Edit Venue')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Edit Venue</h2>
            <p class="text-gray-600">Update venue information and settings</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('venues.show', $venue) }}" class="inline-flex items-center px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">
                <i class="fas fa-eye mr-2"></i>
                View Venue
            </a>
            <a href="{{ route('venues.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Venues
            </a>
        </div>
    </div>

    <!-- Edit Form -->
    <div class="bg-white rounded-xl shadow-lg">
        <form method="POST" action="{{ route('venues.update', $venue) }}" class="p-6 space-y-6">
            @csrf
            @method('PATCH')

            <!-- Venue Information -->
            <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Venue Information</h3>
                
                <div class="space-y-6">
                    <!-- Venue Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            Venue Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               value="{{ old('name', $venue->name) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('name') border-red-500 @enderror"
                               placeholder="Enter venue name (e.g., Grand Convention Center)">
                        @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Venue Address -->
                    <div>
                        <label for="address" class="block text-sm font-medium text-gray-700 mb-2">
                            Address <span class="text-red-500">*</span>
                        </label>
                        <textarea id="address" 
                                  name="address" 
                                  rows="3"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('address') border-red-500 @enderror"
                                  placeholder="Enter complete address including street, city, state, and postal code">{{ old('address', $venue->address) }}</textarea>
                        @error('address')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Capacity -->
                    <div>
                        <label for="capacity" class="block text-sm font-medium text-gray-700 mb-2">
                            Maximum Capacity <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="number" 
                                   id="capacity" 
                                   name="capacity" 
                                   value="{{ old('capacity', $venue->capacity) }}"
                                   min="1"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('capacity') border-red-500 @enderror"
                                   placeholder="e.g., 500">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 text-sm">people</span>
                            </div>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Enter the maximum number of people this venue can accommodate</p>
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
                        <div class="w-16 h-16 bg-gradient-to-r from-green-500 to-blue-500 rounded-lg flex items-center justify-center">
                            <i class="fas fa-map-marker-alt text-2xl text-white"></i>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-gray-900" id="preview-name">{{ $venue->name }}</h4>
                            <p class="text-sm text-gray-600 mt-1 whitespace-pre-line" id="preview-address">{{ $venue->address }}</p>
                            <p class="text-sm text-gray-500 mt-2">
                                <i class="fas fa-users mr-1"></i>
                                Capacity: <span id="preview-capacity">{{ number_format($venue->capacity) }}</span> people
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
                        <div class="text-2xl font-bold text-blue-600">{{ $venue->events->count() }}</div>
                        <div class="text-sm text-gray-600">Total Events</div>
                    </div>
                    <div class="bg-green-50 p-4 rounded-lg text-center">
                        <div class="text-2xl font-bold text-green-600">{{ $venue->events->where('is_active', true)->count() }}</div>
                        <div class="text-sm text-gray-600">Active Events</div>
                    </div>
                    <div class="bg-purple-50 p-4 rounded-lg text-center">
                        <div class="text-2xl font-bold text-purple-600">{{ $venue->events->where('status', 'upcoming')->count() }}</div>
                        <div class="text-sm text-gray-600">Upcoming Events</div>
                    </div>
                    <div class="bg-orange-50 p-4 rounded-lg text-center">
                        <div class="text-2xl font-bold text-orange-600">{{ $venue->events->sum(function($event) { return $event->registrations->count(); }) }}</div>
                        <div class="text-sm text-gray-600">Total Registrations</div>
                    </div>
                </div>
                @if($venue->events->count() > 0)
                <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <div class="flex items-start">
                        <i class="fas fa-info-circle text-yellow-600 mr-2 mt-0.5"></i>
                        <div class="text-sm text-yellow-800">
                            <strong>Note:</strong> This venue has {{ $venue->events->count() }} associated events. 
                            Changing the capacity will not affect existing events, but consider the impact on future events.
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Danger Zone -->
            @if($venue->events->count() == 0)
            <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-semibold text-red-600 mb-4">Danger Zone</h3>
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <i class="fas fa-exclamation-triangle text-red-600 mr-3 mt-0.5"></i>
                        <div>
                            <h4 class="font-medium text-red-800">Delete Venue</h4>
                            <p class="text-sm text-red-700 mt-1">
                                Once you delete this venue, there is no going back. This action cannot be undone.
                            </p>
                            <form method="POST" action="{{ route('venues.destroy', $venue) }}" class="mt-3" onsubmit="return confirm('Are you absolutely sure you want to delete this venue? This action cannot be undone.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-4 py-2 bg-red-600 text-white text-sm rounded-lg hover:bg-red-700 transition-colors">
                                    <i class="fas fa-trash mr-2"></i>
                                    Delete Venue
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Form Actions -->
            <div class="flex items-center justify-end space-x-4 pt-6">
                <a href="{{ route('venues.show', $venue) }}" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-save mr-2"></i>
                    Update Venue
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form preview functionality
    const nameInput = document.getElementById('name');
    const addressInput = document.getElementById('address');
    const capacityInput = document.getElementById('capacity');
    
    const previewName = document.getElementById('preview-name');
    const previewAddress = document.getElementById('preview-address');
    const previewCapacity = document.getElementById('preview-capacity');
    
    function updatePreview() {
        previewName.textContent = nameInput.value || 'Venue Name';
        previewAddress.textContent = addressInput.value || 'Venue address will appear here';
        previewCapacity.textContent = capacityInput.value ? parseInt(capacityInput.value).toLocaleString() : '0';
    }
    
    nameInput.addEventListener('input', updatePreview);
    addressInput.addEventListener('input', updatePreview);
    capacityInput.addEventListener('input', updatePreview);
});
</script>
@endpush
@endsection