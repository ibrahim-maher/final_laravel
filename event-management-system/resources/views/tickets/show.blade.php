@extends('layouts.app')

@section('title', 'Ticket Details')
@section('page-title', 'Ticket Details')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">{{ $ticket->name }}</h2>
            <p class="text-gray-600">{{ $ticket->event->name }}</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('tickets.edit', $ticket) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                <i class="fas fa-edit mr-2"></i>
                Edit Ticket
            </a>
            <a href="{{ route('tickets.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Tickets
            </a>
        </div>
    </div>

    <!-- Ticket Information -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Ticket Information</h3>
        </div>
        
        <div class="p-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Basic Info -->
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Ticket Name</label>
                        <p class="text-lg font-semibold text-gray-900">{{ $ticket->name }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Event</label>
                        <p class="text-gray-900">{{ $ticket->event->name }}</p>
                        <p class="text-sm text-gray-500">{{ $ticket->event->start_date->format('M d, Y') }} - {{ $ticket->event->end_date->format('M d, Y') }}</p>
                    </div>

                    @if($ticket->description)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <p class="text-gray-900">{{ $ticket->description }}</p>
                    </div>
                    @endif
                </div>

                <!-- Price & Capacity -->
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Price</label>
                        <p class="text-2xl font-bold text-gray-900">${{ number_format($ticket->price, 2) }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Capacity</label>
                        <p class="text-lg text-gray-900">{{ $ticket->capacity ? number_format($ticket->capacity) : 'Unlimited' }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        @if($ticket->is_active)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                <span class="w-2 h-2 mr-2 bg-green-400 rounded-full"></span>
                                Active
                            </span>
                        @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                <span class="w-2 h-2 mr-2 bg-red-400 rounded-full"></span>
                                Inactive
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Sales Statistics</h3>
        </div>
        
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-blue-50 p-4 rounded-lg text-center">
                    <div class="text-3xl font-bold text-blue-600">{{ $ticket->registrations->count() }}</div>
                    <div class="text-sm text-gray-600 mt-1">Total Sold</div>
                </div>
                
                <div class="bg-green-50 p-4 rounded-lg text-center">
                    <div class="text-3xl font-bold text-green-600">
                        {{ $ticket->capacity ? ($ticket->capacity - $ticket->registrations->count()) : '∞' }}
                    </div>
                    <div class="text-sm text-gray-600 mt-1">Remaining</div>
                </div>
                
                <div class="bg-purple-50 p-4 rounded-lg text-center">
                    <div class="text-3xl font-bold text-purple-600">
                        ${{ number_format($ticket->registrations->count() * $ticket->price, 2) }}
                    </div>
                    <div class="text-sm text-gray-600 mt-1">Total Revenue</div>
                </div>
                
                <div class="bg-orange-50 p-4 rounded-lg text-center">
                    <div class="text-3xl font-bold text-orange-600">
                        {{ $ticket->capacity ? number_format(($ticket->registrations->count() / $ticket->capacity) * 100, 1) : '0' }}%
                    </div>
                    <div class="text-sm text-gray-600 mt-1">Sold Percentage</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Registrations -->
    @if($ticket->registrations->count() > 0)
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Recent Registrations</h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Participant</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registered</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($ticket->registrations->take(10) as $registration)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $registration->user->name }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $registration->user->email }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                @if($registration->status === 'confirmed') bg-green-100 text-green-800
                                @elseif($registration->status === 'pending') bg-yellow-100 text-yellow-800
                                @else bg-red-100 text-red-800 @endif">
                                {{ ucfirst($registration->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $registration->created_at->format('M d, Y') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        @if($ticket->registrations->count() > 10)
        <div class="px-6 py-3 bg-gray-50 text-center">
            <p class="text-sm text-gray-500">
                Showing 10 of {{ $ticket->registrations->count() }} registrations
            </p>
        </div>
        @endif
    </div>
    @endif

    <!-- Metadata -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Metadata</h3>
        </div>
        
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="font-medium text-gray-700">Created by:</span>
                    <span class="text-gray-900">{{ $ticket->creator->name ?? 'Unknown' }}</span>
                </div>
                <div>
                    <span class="font-medium text-gray-700">Created at:</span>
                    <span class="text-gray-900">{{ $ticket->created_at->format('M d, Y \a\t g:i A') }}</span>
                </div>
                <div>
                    <span class="font-medium text-gray-700">Last updated:</span>
                    <span class="text-gray-900">{{ $ticket->updated_at->format('M d, Y \a\t g:i A') }}</span>
                </div>
                <div>
                    <span class="font-medium text-gray-700">Ticket ID:</span>
                    <span class="text-gray-900 font-mono">#{{ $ticket->id }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection