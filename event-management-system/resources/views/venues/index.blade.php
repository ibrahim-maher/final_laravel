@extends('layouts.app')

@section('title', 'Venues')
@section('page-title', 'Venues Management')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">All Venues</h2>
            <p class="text-gray-600">Manage venue locations and their details</p>
        </div>
        <div class="mt-4 md:mt-0">
            <a href="{{ route('venues.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                <i class="fas fa-plus mr-2"></i>
                Add New Venue
            </a>
        </div>
    </div>

    <!-- Search Section -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <form method="GET" action="{{ route('venues.index') }}" class="flex items-center space-x-4">
            <div class="flex-1">
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}"
                       placeholder="Search venues by name..." 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                <i class="fas fa-search mr-2"></i>
                Search
            </button>
            @if(request('search'))
            <a href="{{ route('venues.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">
                Clear
            </a>
            @endif
        </form>
    </div>

    <!-- Venues Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($venues as $venue)
        <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow">
            <!-- Venue Header -->
            <div class="h-32 bg-gradient-to-r from-green-500 to-blue-500 relative">
                <div class="flex items-center justify-center h-full">
                    <i class="fas fa-map-marker-alt text-6xl text-white opacity-50"></i>
                </div>
                
                <!-- Capacity Badge -->
                <div class="absolute top-4 right-4">
                    <span class="px-3 py-1 text-xs font-semibold bg-white bg-opacity-90 text-gray-800 rounded-full">
                        <i class="fas fa-users mr-1"></i>
                        {{ number_format($venue->capacity) }} capacity
                    </span>
                </div>
            </div>

            <!-- Venue Details -->
            <div class="p-6">
                <div class="mb-4">
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ $venue->name }}</h3>
                    <p class="text-gray-600 text-sm flex items-start">
                        <i class="fas fa-map-marker-alt mr-2 text-gray-400 mt-1"></i>
                        <span class="line-clamp-2">{{ $venue->address }}</span>
                    </p>
                </div>

                <!-- Venue Stats -->
                <div class="space-y-2 text-sm text-gray-600 mb-4">
                    <div class="flex items-center justify-between">
                        <span class="flex items-center">
                            <i class="fas fa-calendar-alt mr-2 text-gray-400"></i>
                            Events hosted
                        </span>
                        <span class="font-semibold">{{ $venue->events->count() }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="flex items-center">
                            <i class="fas fa-expand-arrows-alt mr-2 text-gray-400"></i>
                            Max capacity
                        </span>
                        <span class="font-semibold">{{ number_format($venue->capacity) }}</span>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                    <a href="{{ route('venues.show', $venue) }}" class="text-blue-600 hover:text-blue-800 font-medium text-sm">
                        View Details
                    </a>
                    <div class="flex items-center space-x-2">
                        <a href="{{ route('venues.edit', $venue) }}" class="p-2 text-gray-600 hover:text-blue-600 transition-colors">
                            <i class="fas fa-edit"></i>
                        </a>
                        @if($venue->events->count() == 0)
                        <form method="POST" action="{{ route('venues.destroy', $venue) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this venue?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-2 text-gray-600 hover:text-red-600 transition-colors">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                        @else
                        <span class="p-2 text-gray-400 cursor-not-allowed" title="Cannot delete venue with events">
                            <i class="fas fa-trash"></i>
                        </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full">
            <div class="text-center py-12">
                <i class="fas fa-map-marker-alt text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-600 mb-2">No Venues Found</h3>
                <p class="text-gray-500 mb-6">
                    @if(request('search'))
                        No venues match your search criteria.
                    @else
                        Get started by adding your first venue.
                    @endif
                </p>
                @if(!request('search'))
                <a href="{{ route('venues.create') }}" class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-plus mr-2"></i>
                    Add Your First Venue
                </a>
                @endif
            </div>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($venues->hasPages())
    <div class="bg-white rounded-xl shadow-lg p-6">
        {{ $venues->appends(request()->query())->links() }}
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