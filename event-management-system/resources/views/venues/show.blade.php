@extends('layouts.app')

@section('title', $venue->name)
@section('page-title', 'Venue Details')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
        <div class="flex items-center space-x-4">
            <a href="{{ route('venues.index') }}" class="p-2 text-gray-600 hover:text-blue-600 transition-colors">
                <i class="fas fa-arrow-left text-xl"></i>
            </a>
            <div>
                <h2 class="text-2xl font-bold text-gray-900">{{ $venue->name }}</h2>
                <p class="text-gray-600">Venue Information & Events</p>
            </div>
        </div>
        <div class="mt-4 md:mt-0 flex items-center space-x-3">
            <a href="{{ route('venues.edit', $venue) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                <i class="fas fa-edit mr-2"></i>
                Edit Venue
            </a>
            @if($venue->events->count() == 0)
            <form method="POST" action="{{ route('venues.destroy', $venue) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this venue?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                    <i class="fas fa-trash mr-2"></i>
                    Delete
                </button>
            </form>
            @endif
        </div>
    </div>

    <!-- Venue Overview -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="md:flex">
            <!-- Venue Image/Icon -->
            <div class="md:w-1/3">
                <div class="h-64 md:h-full bg-gradient-to-r from-green-500 to-blue-500 relative">
                    <div class="flex items-center justify-center h-full">
                        <i class="fas fa-map-marker-alt text-8xl text-white opacity-50"></i>
                    </div>
                    
                    <!-- Capacity Badge -->
                    <div class="absolute top-4 right-4">
                        <div class="px-4 py-2 bg-white bg-opacity-90 text-gray-800 rounded-lg font-semibold">
                            <i class="fas fa-users mr-2"></i>
                            {{ number_format($venue->capacity) }} capacity
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Venue Details -->
            <div class="md:w-2/3 p-6">
                <div class="space-y-4">
                    <div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ $venue->name }}</h3>
                        <div class="flex items-start text-gray-600">
                            <i class="fas fa-map-marker-alt mr-3 text-gray-400 mt-1"></i>
                            <p class="whitespace-pre-line">{{ $venue->address }}</p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-4">
                        <div class="text-center p-4 bg-blue-50 rounded-lg">
                            <div class="text-2xl font-bold text-blue-600">{{ number_format($venue->capacity) }}</div>
                            <div class="text-sm text-gray-600">Max Capacity</div>
                        </div>
                        
                        <div class="text-center p-4 bg-green-50 rounded-lg">
                            <div class="text-2xl font-bold text-green-600">{{ $venue->events->count() }}</div>
                            <div class="text-sm text-gray-600">Total Events</div>
                        </div>
                        
                        <div class="text-center p-4 bg-purple-50 rounded-lg">
                            <div class="text-2xl font-bold text-purple-600">{{ $venue->events->where('is_active', true)->count() }}</div>
                            <div class="text-sm text-gray-600">Active Events</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <a href="{{ route('events.create') }}?venue_id={{ $venue->id }}" class="flex flex-col items-center p-4 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors">
                <i class="fas fa-calendar-plus text-2xl text-blue-600 mb-2"></i>
                <span class="text-sm font-medium text-gray-700">Create Event</span>
            </a>
            <a href="{{ route('events.index') }}?venue={{ $venue->id }}" class="flex flex-col items-center p-4 bg-green-50 hover:bg-green-100 rounded-lg transition-colors">
                <i class="fas fa-calendar-alt text-2xl text-green-600 mb-2"></i>
                <span class="text-sm font-medium text-gray-700">View All Events</span>
            </a>
            <a href="{{ route('venues.edit', $venue) }}" class="flex flex-col items-center p-4 bg-orange-50 hover:bg-orange-100 rounded-lg transition-colors">
                <i class="fas fa-edit text-2xl text-orange-600 mb-2"></i>
                <span class="text-sm font-medium text-gray-700">Edit Details</span>
            </a>
            <div class="flex flex-col items-center p-4 bg-gray-50 rounded-lg">
                <i class="fas fa-chart-line text-2xl text-gray-400 mb-2"></i>
                <span class="text-sm font-medium text-gray-500">Analytics</span>
                <span class="text-xs text-gray-400">Coming Soon</span>
            </div>
        </div>
    </div>

    <!-- Events at this Venue -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900">Events at {{ $venue->name }}</h3>
            @if($venue->events->count() > 0)
            <a href="{{ route('events.index') }}?venue={{ $venue->id }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">View All</a>
            @endif
        </div>
        
        @if($venue->events->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($venue->events->take(6) as $event)
            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-2">
                    <h4 class="font-medium text-gray-900 truncate">{{ $event->name }}</h4>
                    @if($event->is_active)
                    <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">Active</span>
                    @endif
                </div>
                
                <p class="text-sm text-gray-600 mb-3 line-clamp-2">{{ Str::limit($event->description, 100) }}</p>
                
                <div class="space-y-2 text-xs text-gray-500">
                    <div class="flex items-center">
                        <i class="fas fa-calendar mr-2"></i>
                        <span>{{ $event->start_date->format('M d, Y') }}</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-users mr-2"></i>
                        <span>{{ $event->registrations->count() }} registered</span>
                    </div>
                    @php
                        $status = $event->status;
                        $statusColors = [
                            'upcoming' => 'text-blue-600',
                            'ongoing' => 'text-green-600',
                            'completed' => 'text-gray-600'
                        ];
                    @endphp
                    <div class="flex items-center">
                        <i class="fas fa-info-circle mr-2"></i>
                        <span class="{{ $statusColors[$status] ?? 'text-gray-600' }}">{{ ucfirst($status) }}</span>
                    </div>
                </div>
                
                <div class="mt-3 pt-3 border-t border-gray-200">
                    <a href="{{ route('events.show', $event) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                        View Event →
                    </a>
                </div>
            </div>
            @endforeach
        </div>
        
        @if($venue->events->count() > 6)
        <div class="mt-6 text-center">
            <a href="{{ route('events.index') }}?venue={{ $venue->id }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                View All {{ $venue->events->count() }} Events
                <i class="fas fa-arrow-right ml-2"></i>
            </a>
        </div>
        @endif
        
        @else
        <div class="text-center py-8">
            <i class="fas fa-calendar-alt text-4xl text-gray-300 mb-3"></i>
            <p class="text-gray-500 mb-4">No events scheduled at this venue yet</p>
            <a href="{{ route('events.create') }}?venue_id={{ $venue->id }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                <i class="fas fa-plus mr-2"></i>
                Create First Event
            </a>
        </div>
        @endif
    </div>

    <!-- Venue Statistics -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Venue Statistics</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="text-center p-4 bg-blue-50 rounded-lg">
                <div class="text-xl font-bold text-blue-600">{{ $venue->events->where('status', 'upcoming')->count() }}</div>
                <div class="text-sm text-gray-600">Upcoming Events</div>
            </div>
            <div class="text-center p-4 bg-green-50 rounded-lg">
                <div class="text-xl font-bold text-green-600">{{ $venue->events->where('status', 'ongoing')->count() }}</div>
                <div class="text-sm text-gray-600">Ongoing Events</div>
            </div>
            <div class="text-center p-4 bg-gray-50 rounded-lg">
                <div class="text-xl font-bold text-gray-600">{{ $venue->events->where('status', 'completed')->count() }}</div>
                <div class="text-sm text-gray-600">Completed Events</div>
            </div>
            <div class="text-center p-4 bg-purple-50 rounded-lg">
                <div class="text-xl font-bold text-purple-600">{{ $venue->events->sum(function($event) { return $event->registrations->count(); }) }}</div>
                <div class="text-sm text-gray-600">Total Registrations</div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<style>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
@endpush
@endsection