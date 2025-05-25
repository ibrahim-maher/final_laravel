@extends('layouts.app')

@section('title', 'Events')
@section('page-title', 'Events Management')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">All Events</h2>
            <p class="text-gray-600">Manage your events and their details</p>
        </div>
        <div class="mt-4 md:mt-0">
            <a href="{{ route('events.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                <i class="fas fa-plus mr-2"></i>
                Create Event
            </a>
        </div>
    </div>

    <!-- Search Section -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <form method="GET" action="{{ route('events.index') }}" class="flex items-center space-x-4">
            <div class="flex-1">
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}"
                       placeholder="Search events..." 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                <i class="fas fa-search mr-2"></i>
                Search
            </button>
            @if(request('search'))
            <a href="{{ route('events.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">
                Clear
            </a>
            @endif
        </form>
    </div>

    <!-- Events Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($events as $event)
        <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow">
            <!-- Event Logo/Image -->
            <div class="h-48 bg-gradient-to-r from-blue-500 to-purple-500 relative">
                @if($event->logo)
                <img src="{{ Storage::url($event->logo) }}" alt="{{ $event->name }}" class="w-full h-full object-cover">
                @else
                <div class="flex items-center justify-center h-full">
                    <i class="fas fa-calendar-alt text-6xl text-white opacity-50"></i>
                </div>
                @endif
                
                <!-- Status Badge -->
                <div class="absolute top-4 right-4">
                    @if($event->is_active)
                    <span class="px-3 py-1 text-xs font-semibold bg-green-100 text-green-800 rounded-full">Active</span>
                    @else
                    <span class="px-3 py-1 text-xs font-semibold bg-gray-100 text-gray-800 rounded-full">Inactive</span>
                    @endif
                </div>

                <!-- Event Status -->
                <div class="absolute bottom-4 left-4">
                    @php
                        $status = $event->status;
                        $statusColors = [
                            'upcoming' => 'bg-blue-100 text-blue-800',
                            'ongoing' => 'bg-green-100 text-green-800',
                            'completed' => 'bg-gray-100 text-gray-800'
                        ];
                    @endphp
                    <span class="px-3 py-1 text-xs font-semibold {{ $statusColors[$status] ?? 'bg-gray-100 text-gray-800' }} rounded-full">
                        {{ ucfirst($status) }}
                    </span>
                </div>
            </div>

            <!-- Event Details -->
            <div class="p-6">
                <div class="mb-4">
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ $event->name }}</h3>
                    <p class="text-gray-600 text-sm line-clamp-2">{{ Str::limit($event->description, 100) }}</p>
                </div>

                <!-- Event Info -->
                <div class="space-y-2 text-sm text-gray-600 mb-4">
                    <div class="flex items-center">
                        <i class="fas fa-map-marker-alt mr-2 text-gray-400"></i>
                        <span>{{ $event->venue->name ?? 'No venue' }}</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-tag mr-2 text-gray-400"></i>
                        <span>{{ $event->category->name ?? 'No category' }}</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-calendar mr-2 text-gray-400"></i>
                        <span>{{ $event->start_date->format('M d, Y') }}</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-users mr-2 text-gray-400"></i>
                        <span>{{ $event->registrations->count() }} registrations</span>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                    <a href="{{ route('events.show', $event) }}" class="text-blue-600 hover:text-blue-800 font-medium text-sm">
                        View Details
                    </a>
                    <div class="flex items-center space-x-2">
                        <a href="{{ route('events.edit', $event) }}" class="p-2 text-gray-600 hover:text-blue-600 transition-colors">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form method="POST" action="{{ route('events.destroy', $event) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this event?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-2 text-gray-600 hover:text-red-600 transition-colors">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full">
            <div class="text-center py-12">
                <i class="fas fa-calendar-alt text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-600 mb-2">No Events Found</h3>
                <p class="text-gray-500 mb-6">
                    @if(request('search'))
                        No events match your search criteria.
                    @else
                        Get started by creating your first event.
                    @endif
                </p>
                @if(!request('search'))
                <a href="{{ route('events.create') }}" class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-plus mr-2"></i>
                    Create Your First Event
                </a>
                @endif
            </div>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($events->hasPages())
    <div class="bg-white rounded-xl shadow-lg p-6">
        {{ $events->appends(request()->query())->links() }}
    </div>
    @endif
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