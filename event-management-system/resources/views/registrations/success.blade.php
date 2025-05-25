{{-- resources/views/registrations/success.blade.php --}}
@extends('layouts.app')

@section('title', 'Registration Successful')

@section('content')
<div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Success Message -->
        <div class="text-center mb-8">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4">
                <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-gray-900">Registration Successful!</h1>
            <p class="mt-2 text-lg text-gray-600">Thank you for registering for {{ $registration->event->name }}</p>
        </div>

        <!-- Registration Details Card -->
        <div class="bg-white shadow-lg rounded-lg border border-gray-200 overflow-hidden mb-8">
            <!-- Header -->
            <div class="bg-gradient-to-r from-green-500 to-green-600 px-6 py-4">
                <h2 class="text-xl font-semibold text-white">Registration Details</h2>
            </div>

            <!-- Content -->
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Event Information -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-3">Event Information</h3>
                        <dl class="space-y-2">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Event Name</dt>
                                <dd class="text-sm text-gray-900">{{ $registration->event->name }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Date</dt>
                                <dd class="text-sm text-gray-900">
                                    {{ $registration->event->start_date->format('F d, Y') }}
                                    @if($registration->event->start_date->format('Y-m-d') !== $registration->event->end_date->format('Y-m-d'))
                                        - {{ $registration->event->end_date->format('F d, Y') }}
                                    @endif
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Time</dt>
                                <dd class="text-sm text-gray-900">
                                    {{ $registration->event->start_date->format('g:i A') }}
                                    @if($registration->event->start_date->format('H:i') !== $registration->event->end_date->format('H:i'))
                                        - {{ $registration->event->end_date->format('g:i A') }}
                                    @endif
                                </dd>
                            </div>
                            @if($registration->event->venue)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Venue</dt>
                                <dd class="text-sm text-gray-900">
                                    {{ $registration->event->venue->name }}
                                    @if($registration->event->venue->address)
                                        <br><span class="text-gray-600">{{ $registration->event->venue->address }}</span>
                                    @endif
                                </dd>
                            </div>
                            @endif
                        </dl>
                    </div>

                    <!-- Registration Information -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-3">Your Registration</h3>
                        <dl class="space-y-2">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Registration ID</dt>
                                <dd class="text-sm text-gray-900 font-mono">{{ $registration->id }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Name</dt>
                                <dd class="text-sm text-gray-900">{{ $registration->user->name }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Email</dt>
                                <dd class="text-sm text-gray-900">{{ $registration->user->email }}</dd>
                            </div>
                            @if($registration->ticketType)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Ticket Type</dt>
                                <dd class="text-sm text-gray-900">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $registration->ticketType->name }}
                                    </span>
                                </dd>
                            </div>
                            @endif
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Status</dt>
                                <dd class="text-sm text-gray-900">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        {{ $registration->status_display }}
                                    </span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Registered At</dt>
                                <dd class="text-sm text-gray-900">{{ $registration->created_at->format('F d, Y g:i A') }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Additional Registration Data -->
                @if($registration->registration_data && count($registration->registration_data) > 0)
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-3">Additional Information</h3>
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($registration->registration_data as $field => $value)
                            @if($value)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">{{ $field }}</dt>
                                <dd class="text-sm text-gray-900">{{ $value }}</dd>
                            </div>
                            @endif
                        @endforeach
                    </dl>
                </div>
                @endif
            </div>
        </div>

        <!-- QR Code Section -->
        @if($registration->qrCode && $registration->qrCode->qr_image_path)
        <div class="bg-white shadow-lg rounded-lg border border-gray-200 overflow-hidden mb-8">
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-800">Your QR Code</h2>
                <p class="text-sm text-gray-600 mt-1">Show this QR code at the event for quick check-in</p>
            </div>
            
            <div class="p-6 text-center">
                <div class="inline-block bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                    <img src="{{ $registration->qrCode->qr_image_url }}" 
                         alt="Registration QR Code" 
                         class="w-48 h-48 mx-auto">
                    <p class="text-xs text-gray-500 mt-2">Registration ID: {{ $registration->id }}</p>
                </div>
                
                <div class="mt-4 space-x-3">
                    <a href="{{ route('qr-codes.download', $registration) }}" 
                       class="inline-flex items-center px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Download QR Code
                    </a>
                    
                    <button onclick="printQRCode()" 
                            class="inline-flex items-center px-4 py-2 bg-gray-600 text-white font-medium rounded-lg hover:bg-gray-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Print QR Code
                    </button>
                </div>
            </div>
        </div>
        @endif

        <!-- Important Notes -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-8">
            <div class="flex items-start">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600 mt-0.5 mr-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                    <h3 class="text-lg font-medium text-blue-800">Important Information</h3>
                    <div class="text-blue-700 mt-2 space-y-2">
                        <p>• Please arrive at least 15 minutes before the event starts</p>
                        <p>• Bring your QR code (digital or printed) for quick check-in</p>
                        <p>• A confirmation email has been sent to {{ $registration->user->email }}</p>
                        <p>• If you need to make changes, contact the event organizer</p>
                        @if($registration->event->venue && $registration->event->venue->address)
                        <p>• Event location: {{ $registration->event->venue->address }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="text-center space-y-4">
            <div class="space-x-4">
                <a href="{{ route('events.show', $registration->event) }}" 
                   class="inline-flex items-center px-6 py-3 bg-gray-600 text-white font-medium rounded-lg hover:bg-gray-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    View Event Details
                </a>
                
                <a href="{{ route('registrations.public-register') }}" 
                   class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Register for Another Event
                </a>
            </div>
            
            <p class="text-sm text-gray-500">
                Need help? Contact us at 
                <a href="mailto:support@example.com" class="text-blue-600 hover:text-blue-800">support@example.com</a>
            </p>
        </div>
    </div>
</div>

@push('scripts')
<script>
function printQRCode() {
    const qrCodeSection = document.querySelector('.bg-white.p-4.rounded-lg.shadow-sm.border.border-gray-200');
    if (qrCodeSection) {
        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <html>
                <head>
                    <title>QR Code - Registration {{ $registration->id }}</title>
                    <style>
                        body { 
                            margin: 0; 
                            padding: 20px; 
                            text-align: center; 
                            font-family: Arial, sans-serif; 
                        }
                        img { 
                            max-width: 300px; 
                            height: auto; 
                        }
                        .event-info {
                            margin-top: 20px;
                            font-size: 14px;
                            color: #666;
                        }
                        .registration-id {
                            margin-top: 10px;
                            font-size: 12px;
                            color: #999;
                        }
                        @media print {
                            body { margin: 0; }
                        }
                    </style>
                </head>
                <body>
                    <h2>{{ $registration->event->name }}</h2>
                    <img src="{{ $registration->qrCode->qr_image_url }}" alt="QR Code">
                    <div class="event-info">
                        <p><strong>{{ $registration->user->name }}</strong></p>
                        <p>{{ $registration->event->start_date->format('F d, Y g:i A') }}</p>
                        @if($registration->ticketType)
                        <p>{{ $registration->ticketType->name }}</p>
                        @endif
                    </div>
                    <div class="registration-id">
                        Registration ID: {{ $registration->id }}
                    </div>
                    <script>
                        window.onload = function() {
                            setTimeout(function() {
                                window.print();
                                window.close();
                            }, 500);
                        }
                    </script>
                </body>
            </html>
        `);
        printWindow.document.close();
    }
}
</script>
@endpush
@endsection