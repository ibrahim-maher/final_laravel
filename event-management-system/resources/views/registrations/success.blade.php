@extends('layouts.app')

@section('title', 'Registration Successful')

@section('content')
<div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Success Header -->
        <div class="text-center mb-8">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4">
                <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Registration Successful!</h1>
            <p class="text-lg text-gray-600">Thank you for registering. Your registration has been confirmed.</p>
        </div>

        <!-- Registration Details Card -->
        <div class="bg-white shadow-lg rounded-lg border border-gray-200 overflow-hidden">
            <div class="p-6 bg-gradient-to-r from-blue-600 to-blue-700 text-white">
                <h2 class="text-xl font-semibold mb-2">Registration Details</h2>
                <p class="text-blue-100">Registration ID: #{{ $registration->id }}</p>
            </div>

            <div class="p-6">
                <!-- Event Information -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Event Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Event Name</label>
                            <p class="mt-1 text-lg font-medium text-gray-900">{{ $registration->event->name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Date & Time</label>
                            <p class="mt-1 text-lg text-gray-900">
                                {{ $registration->event->start_date->format('F d, Y') }}
                                @if($registration->event->start_time)
                                    at {{ $registration->event->start_time->format('g:i A') }}
                                @endif
                            </p>
                        </div>
                        @if($registration->event->venue)
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Venue</label>
                            <p class="mt-1 text-lg text-gray-900">{{ $registration->event->venue->name }}</p>
                            @if($registration->event->venue->address)
                            <p class="text-sm text-gray-600">{{ $registration->event->venue->address }}</p>
                            @endif
                        </div>
                        @endif
                        @if($registration->ticketType)
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Ticket Type</label>
                            <p class="mt-1 text-lg text-gray-900">{{ $registration->ticketType->name }}</p>
                            <p class="text-sm font-medium text-green-600">${{ $registration->ticketType->price }}</p>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Participant Information -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Participant Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Name</label>
                            <p class="mt-1 text-lg text-gray-900">{{ $registration->user->name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Email</label>
                            <p class="mt-1 text-lg text-gray-900">{{ $registration->user->email }}</p>
                        </div>
                        @if($registration->user->phone)
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Phone</label>
                            <p class="mt-1 text-lg text-gray-900">{{ $registration->user->phone }}</p>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Registration Data -->
                @if($registration->registration_data && count($registration->registration_data) > 0)
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Additional Information</h3>
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
                @endif

                <!-- QR Code Section -->
                @if($registration->qrCode && $registration->qrCode->qr_image)
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Your QR Code</h3>
                    <div class="flex flex-col md:flex-row items-center md:items-start space-y-4 md:space-y-0 md:space-x-6">
                        <div class="flex-shrink-0">
                            <div class="p-4 bg-white border-2 border-gray-200 rounded-lg shadow-sm">
                                <img src="{{ Storage::url($registration->qrCode->qr_image) }}" 
                                     alt="Registration QR Code" 
                                     class="w-32 h-32">
                            </div>
                        </div>
                        <div class="flex-1 text-center md:text-left">
                            <p class="text-gray-700 mb-4">
                                Present this QR code at the event for quick check-in. 
                                Save it to your phone or print it out.
                            </p>
                            <a href="{{ Storage::url($registration->qrCode->qr_image) }}" 
                               download="registration-{{ $registration->id }}-qr.png"
                               class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Download QR Code
                            </a>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Important Information -->
        <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-lg font-medium text-blue-800">Important Information</h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <ul class="list-disc list-inside space-y-1">
                            <li>A confirmation email has been sent to {{ $registration->user->email }}</li>
                            <li>Please arrive 15 minutes before the event start time</li>
                            <li>Bring a valid ID for verification</li>
                            <li>Present your QR code at the registration desk</li>
                            @if($registration->ticketType && $registration->ticketType->price > 0)
                            <li>Your ticket payment of ${{ $registration->ticketType->price }} has been processed</li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="mt-8 flex flex-col sm:flex-row justify-center space-y-3 sm:space-y-0 sm:space-x-4">
            <a href="{{ route('public.events.index') }}" 
               class="inline-flex items-center justify-center px-6 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Browse More Events
            </a>
            
            <button onclick="window.print()" 
                    class="inline-flex items-center justify-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
                Print Registration
            </button>
            
            <a href="mailto:{{ $registration->user->email }}?subject=Registration Confirmation - {{ $registration->event->name }}&body=Thank you for registering for {{ $registration->event->name }} on {{ $registration->event->start_date->format('F d, Y') }}." 
               class="inline-flex items-center justify-center px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
                Email Confirmation
            </a>
        </div>

        <!-- Footer -->
        <div class="mt-12 text-center">
            <p class="text-sm text-gray-500">
                If you have any questions, please contact our support team.
            </p>
        </div>
    </div>
</div>

@push('styles')
<style>
    @media print {
        .no-print {
            display: none !important;
        }
        
        body {
            background: white !important;
        }
        
        .bg-gradient-to-r {
            background: #1e40af !important;
            -webkit-print-color-adjust: exact;
        }
    }
</style>
@endpush
@endsection