@extends('layouts.app')

@section('title', 'Registration Details')

@section('content')
<div class=" mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
        <nav class="flex mb-4" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('registrations.index') }}" class="text-gray-700 hover:text-blue-600">Registrations</a>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="ml-1 text-gray-500 md:ml-2">Registration Details</span>
                    </div>
                </li>
            </ol>
        </nav>

        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Registration Details</h1>
                <p class="text-gray-600 mt-2">Registration ID: #{{ $registration->id }}</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('registrations.edit', $registration) }}" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Edit Registration
                </a>
                <form action="{{ route('registrations.destroy', $registration) }}" 
                      method="POST" 
                      class="inline-block"
                      onsubmit="return confirm('Are you sure you want to delete this registration?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        Delete Registration
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Messages -->
    @if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
        {{ session('success') }}
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Information -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Participant Information -->
            <div class="bg-white shadow-lg rounded-lg border border-gray-200">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-800">Participant Information</h2>
                </div>
                <div class="p-6">
                    <div class="flex items-start">
                        <div class="flex-shrink-0 h-16 w-16">
                            <div class="h-16 w-16 rounded-full bg-blue-500 flex items-center justify-center">
                                <span class="text-white font-bold text-xl">
                                    {{ strtoupper(substr($registration->user->name ?? 'U', 0, 2)) }}
                                </span>
                            </div>
                        </div>
                        <div class="ml-6 flex-1">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Full Name</label>
                                    <p class="mt-1 text-lg font-medium text-gray-900">
                                        {{ $registration->user->name ?? 'N/A' }}
                                    </p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Email</label>
                                    <p class="mt-1 text-lg text-gray-900">
                                        {{ $registration->user->email ?? 'N/A' }}
                                    </p>
                                </div>
                                @if($registration->user->phone)
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Phone</label>
                                    <p class="mt-1 text-lg text-gray-900">
                                        {{ $registration->user->phone }}
                                    </p>
                                </div>
                                @endif
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">User Role</label>
                                    <p class="mt-1 text-lg text-gray-900">
                                        {{ ucfirst($registration->user->role ?? 'visitor') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Event Information -->
            <div class="bg-white shadow-lg rounded-lg border border-gray-200">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-800">Event Information</h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Event Name</label>
                            <p class="mt-1 text-lg font-medium text-gray-900">
                                {{ $registration->event->name ?? 'N/A' }}
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Event Date</label>
                            <p class="mt-1 text-lg text-gray-900">
                                {{ $registration->event->start_date ? $registration->event->start_date->format('F d, Y') : 'TBD' }}
                                @if($registration->event->start_time)
                                    at {{ $registration->event->start_time->format('g:i A') }}
                                @endif
                            </p>
                        </div>
                        @if($registration->event->venue)
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Venue</label>
                            <p class="mt-1 text-lg text-gray-900">
                                {{ $registration->event->venue->name }}
                            </p>
                            @if($registration->event->venue->address)
                            <p class="text-sm text-gray-500">
                                {{ $registration->event->venue->address }}
                            </p>
                            @endif
                        </div>
                        @endif
                        @if($registration->ticketType)
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Ticket Type</label>
                            <p class="mt-1 text-lg text-gray-900">
                                {{ $registration->ticketType->name }}
                            </p>
                            <p class="text-sm text-green-600 font-medium">
                                ${{ $registration->ticketType->price }}
                            </p>
                        </div>
                        @endif
                    </div>
                    
                    @if($registration->event->description)
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-500">Event Description</label>
                        <p class="mt-1 text-gray-900">
                            {{ $registration->event->description }}
                        </p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Registration Data -->
            @if($registration->registration_data && count($registration->registration_data) > 0)
            <div class="bg-white shadow-lg rounded-lg border border-gray-200">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-800">Registration Details</h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($registration->registration_data as $field => $value)
                        <div>
                            <label class="block text-sm font-medium text-gray-500">{{ $field }}</label>
                            <p class="mt-1 text-lg text-gray-900">
                                @if(is_array($value))
                                    {{ implode(', ', $value) }}
                                @elseif(is_bool($value))
                                    {{ $value ? 'Yes' : 'No' }}
                                @else
                                    {{ $value ?: 'N/A' }}
                                @endif
                            </p>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Status Card -->
            <div class="bg-white shadow-lg rounded-lg border border-gray-200">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Registration Status</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Current Status</label>
                            <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full mt-1
                                {{ $registration->status === 'confirmed' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $registration->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                {{ $registration->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}">
                                {{ ucfirst($registration->status) }}
                            </span>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Registration Date</label>
                            <p class="mt-1 text-sm text-gray-900">
                                {{ $registration->created_at->format('F d, Y g:i A') }}
                            </p>
                        </div>
                        
                        @if($registration->updated_at && $registration->updated_at != $registration->created_at)
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Last Updated</label>
                            <p class="mt-1 text-sm text-gray-900">
                                {{ $registration->updated_at->format('F d, Y g:i A') }}
                            </p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- QR Code -->
            @if($registration->qrCode && $registration->qrCode->qr_image)
            <div class="bg-white shadow-lg rounded-lg border border-gray-200">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">QR Code</h3>
                    
                    <div class="text-center">
                        <div class="inline-block p-4 bg-white border-2 border-gray-200 rounded-lg">
                            <img src="{{ Storage::url($registration->qrCode->qr_image) }}" 
                                 alt="QR Code" 
                                 class="w-32 h-32 mx-auto">
                        </div>
                        
                        <p class="text-sm text-gray-500 mt-2 mb-4">
                            Scan this QR code for check-in
                        </p>
                        
                        <a href="{{ Storage::url($registration->qrCode->qr_image) }}" 
                           download="registration-{{ $registration->id }}-qr.png"
                           class="inline-flex items-center px-3 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Download QR Code
                        </a>
                    </div>
                </div>
            </div>
            @endif

            <!-- Quick Actions -->
            <div class="bg-white shadow-lg rounded-lg border border-gray-200">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Quick Actions</h3>
                    
                    <div class="space-y-3">
                        @if($registration->status !== 'confirmed')
                        <form action="{{ route('registrations.update', $registration) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="status" value="confirmed">
                            <input type="hidden" name="event_id" value="{{ $registration->event_id }}">
                            <input type="hidden" name="user_id" value="{{ $registration->user_id }}">
                            <input type="hidden" name="ticket_type_id" value="{{ $registration->ticket_type_id }}">
                            <button type="submit" 
                                    class="w-full px-4 py-2 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700 transition-colors">
                                Confirm Registration
                            </button>
                        </form>
                        @endif
                        
                      
                        
                        <a href="mailto:{{ $registration->user->email ?? '' }}" 
                           class="w-full inline-flex items-center justify-center px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            Email Participant
                        </a>
                    </div>
                </div>
            </div>

            <!-- Registration Summary -->
            <div class="bg-gray-50 rounded-lg p-4">
                <h4 class="font-medium text-gray-900 mb-2">Registration Summary</h4>
                <div class="space-y-1 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Registration ID:</span>
                        <span class="font-medium">#{{ $registration->id }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Participant:</span>
                        <span class="font-medium">{{ $registration->user->name ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Event:</span>
                        <span class="font-medium">{{ Str::limit($registration->event->name ?? 'N/A', 20) }}</span>
                    </div>
                    @if($registration->ticketType)
                    <div class="flex justify-between">
                        <span class="text-gray-600">Ticket:</span>
                        <span class="font-medium">${{ $registration->ticketType->price }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between pt-2 border-t border-gray-200">
                        <span class="text-gray-600">Status:</span>
                        <span class="font-medium {{ 
                            $registration->status === 'confirmed' ? 'text-green-600' : 
                            ($registration->status === 'cancelled' ? 'text-red-600' : 'text-yellow-600') 
                        }}">
                            {{ ucfirst($registration->status) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection