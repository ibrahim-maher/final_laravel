@extends('layouts.app')

@section('title', 'Create Ticket')
@section('page-title', 'Create New Ticket')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ route('tickets.store') }}" class="space-y-6">
            @csrf
            
            <!-- Event Selection -->
            <div>
                <label for="event_id" class="block text-sm font-medium text-gray-700 mb-2">Event</label>
                <select name="event_id" id="event_id" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Select Event</option>
                    @foreach($events as $event)
                    <option value="{{ $event->id }}" {{ old('event_id') == $event->id ? 'selected' : '' }}>
                        {{ $event->name }}
                    </option>
                    @endforeach
                </select>
                @error('event_id')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <!-- Ticket Name -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Ticket Name</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required
                       placeholder="e.g., General Admission, VIP, Early Bird"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                @error('name')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <!-- Price -->
            <div>
                <label for="price" class="block text-sm font-medium text-gray-700 mb-2">Price ($)</label>
                <input type="number" name="price" id="price" value="{{ old('price', 0) }}" min="0" step="0.01" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                @error('price')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <!-- Capacity -->
            <div>
                <label for="capacity" class="block text-sm font-medium text-gray-700 mb-2">Capacity (Optional)</label>
                <input type="number" name="capacity" id="capacity" value="{{ old('capacity') }}" min="1"
                       placeholder="Leave empty for unlimited"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                @error('capacity')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <!-- Buttons -->
            <div class="flex justify-end space-x-4">
                <a href="{{ route('tickets.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded-lg">
                    Cancel
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                    Create Ticket
                </button>
            </div>
        </form>
    </div>
</div>
@endsection