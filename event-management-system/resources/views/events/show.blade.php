@extends('layouts.app')

@section('title', $event->name)
@section('page-title', 'Event Details')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
        <div class="flex items-center space-x-4">
            <a href="{{ route('events.index') }}" class="p-2 text-gray-600 hover:text-blue-600 transition-colors">
                <i class="fas fa-arrow-left text-xl"></i>
            </a>
            <div>
                <h2 class="text-2xl font-bold text-gray-900">{{ $event->name }}</h2>
                <p class="text-gray-600">Event Details & Management</p>
            </div>
        </div>
        <div class="mt-4 md:mt-0 flex items-center space-x-3">
            <a href="{{ route('events.edit', $event) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                <i class="fas fa-edit mr-2"></i>
                Edit Event
            </a>
            <form method="POST" action="{{ route('events.destroy', $event) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this event?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                    <i class="fas fa-trash mr-2"></i>
                    Delete
                </button>
            </form>
        </div>
    </div>

    <!-- Event Overview -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="md:flex">
            <!-- Event Image -->
            <div class="md:w-1/3">
                <div class="h-64 md:h-full bg-gradient-to-r from-blue-500 to-purple-500 relative">
                    @if($event->logo)
                    <img src="{{ Storage::url($event->logo) }}" alt="{{ $event->name }}" class="w-full h-full object-cover">
                    @else
                    <div class="flex items-center justify-center h-full">
                        <i class="fas fa-calendar-alt text-6xl text-white opacity-50"></i>
                    </div>
                    @endif
                    
                    <!-- Status Badges -->
                    <div class="absolute top-4 right-4 space-y-2">
                        @if($event->is_active)
                        <div class="px-3 py-1 text-xs font-semibold bg-green-100 text-green-800 rounded-full">Active Event</div>
                        @endif
                        
                        @php
                            $status = $event->status;
                            $statusColors = [
                                'upcoming' => 'bg-blue-100 text-blue-800',
                                'ongoing' => 'bg-green-100 text-green-800',
                                'completed' => 'bg-gray-100 text-gray-800'
                            ];
                        @endphp
                        <div class="px-3 py-1 text-xs font-semibold {{ $statusColors[$status] ?? 'bg-gray-100 text-gray-800' }} rounded-full">
                            {{ ucfirst($status) }}
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Event Details -->
            <div class="md:w-2/3 p-6">
                <div class="space-y-4">
                    <div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ $event->name }}</h3>
                        <p class="text-gray-600">{{ $event->description }}</p>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div class="flex items-center text-gray-600">
                            <i class="fas fa-map-marker-alt mr-3 text-gray-400"></i>
                            <div>
                                <span class="font-medium">Venue:</span>
                                <span class="ml-1">{{ $event->venue->name ?? 'No venue assigned' }}</span>
                            </div>
                        </div>
                        
                        <div class="flex items-center text-gray-600">
                            <i class="fas fa-tag mr-3 text-gray-400"></i>
                            <div>
                                <span class="font-medium">Category:</span>
                                <span class="ml-1">{{ $event->category->name ?? 'No category' }}</span>
                            </div>
                        </div>
                        
                        <div class="flex items-center text-gray-600">
                            <i class="fas fa-calendar-start mr-3 text-gray-400"></i>
                            <div>
                                <span class="font-medium">Start:</span>
                                <span class="ml-1">{{ $event->start_date->format('M d, Y - g:i A') }}</span>
                            </div>
                        </div>
                        
                        <div class="flex items-center text-gray-600">
                            <i class="fas fa-calendar-times mr-3 text-gray-400"></i>
                            <div>
                                <span class="font-medium">End:</span>
                                <span class="ml-1">{{ $event->end_date->format('M d, Y - g:i A') }}</span>
                            </div>
                        </div>
                        
                        <div class="flex items-center text-gray-600">
                            <i class="fas fa-clock mr-3 text-gray-400"></i>
                            <div>
                                <span class="font-medium">Duration:</span>
                                <span class="ml-1">{{ $event->start_date->diffInHours($event->end_date) }} hours</span>
                            </div>
                        </div>
                        
                        <div class="flex items-center text-gray-600">
                            <i class="fas fa-calendar-plus mr-3 text-gray-400"></i>
                            <div>
                                <span class="font-medium">Created:</span>
                                <span class="ml-1">{{ $event->created_at->format('M d, Y') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Total Registrations -->
        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Registrations</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $event->registrations->count() }}</p>
                </div>
                <div class="bg-blue-100 p-3 rounded-full">
                    <i class="fas fa-user-plus text-2xl text-blue-600"></i>
                </div>
            </div>
        </div>

        <!-- Available Tickets -->
        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Ticket Types</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $event->tickets->count() }}</p>
                </div>
                <div class="bg-green-100 p-3 rounded-full">
                    <i class="fas fa-ticket-alt text-2xl text-green-600"></i>
                </div>
            </div>
        </div>

        <!-- Registration Fields -->
        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Registration Fields</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $event->registrationFields->count() }}</p>
                </div>
                <div class="bg-purple-100 p-3 rounded-full">
                    <i class="fas fa-list text-2xl text-purple-600"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <a href="{{ route('tickets.create') }}?event_id={{ $event->id }}" class="flex flex-col items-center p-4 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors">
                <i class="fas fa-ticket-alt text-2xl text-blue-600 mb-2"></i>
                <span class="text-sm font-medium text-gray-700">Manage Tickets</span>
            </a>
            <a href="{{ route('registrations.index') }}?event_id={{ $event->id }}" class="flex flex-col items-center p-4 bg-green-50 hover:bg-green-100 rounded-lg transition-colors">
                <i class="fas fa-users text-2xl text-green-600 mb-2"></i>
                <span class="text-sm font-medium text-gray-700">View Registrations</span>
            </a>
            <a href="{{ route('registration-fields.index', $event) }}" class="flex flex-col items-center p-4 bg-purple-50 hover:bg-purple-100 rounded-lg transition-colors">
                <i class="fas fa-list text-2xl text-purple-600 mb-2"></i>
                <span class="text-sm font-medium text-gray-700">Registration Fields</span>
            </a>
            <a href="{{ route('checkin.index') }}?event_id={{ $event->id }}" class="flex flex-col items-center p-4 bg-orange-50 hover:bg-orange-100 rounded-lg transition-colors">
                <i class="fas fa-qrcode text-2xl text-orange-600 mb-2"></i>
                <span class="text-sm font-medium text-gray-700">Check-in</span>
            </a>
        </div>
    </div>

    <!-- Recent Registrations -->
    @if($event->registrations->count() > 0)
    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900">Recent Registrations</h3>
            <a href="{{ route('registrations.index') }}?event_id={{ $event->id }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">View All</a>
        </div>
        <div class="space-y-4">
            @foreach($event->registrations->take(5) as $registration)
            <div class="flex items-center justify-between p-3 hover:bg-gray-50 rounded-lg transition-colors">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-500 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-user text-white text-sm"></i>
                    </div>
                    <div>
                        <h4 class="font-medium text-gray-900">{{ $registration->user->name ?? 'Guest User' }}</h4>
                        <p class="text-sm text-gray-500">{{ $registration->created_at->format('M d, Y - g:i A') }}</p>
                    </div>
                </div>
                <div class="text-right">
                    <span class="px-2 py-1 text-xs rounded-full {{ $registration->status == 'confirmed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                        {{ ucfirst($registration->status ?? 'pending') }}
                    </span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Tickets Information -->
    @if($event->tickets->count() > 0)
    <div class="bg-white rounded-xl shadow-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Available Tickets</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($event->tickets as $ticket)
            <div class="border border-gray-200 rounded-lg p-4">
                <div class="flex items-center justify-between mb-2">
                    <h4 class="font-medium text-gray-900">{{ $ticket->name }}</h4>
                    <span class="text-lg font-bold text-blue-600">${{ number_format($ticket->price, 2) }}</span>
                </div>
                <p class="text-sm text-gray-600 mb-2">{{ $ticket->description }}</p>
                <div class="flex items-center justify-between text-xs text-gray-500">
                    <span>Available: {{ $ticket->quantity ?? 'Unlimited' }}</span>
                    <span class="px-2 py-1 bg-gray-100 rounded">{{ ucfirst($ticket->type ?? 'regular') }}</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection