<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registration Successful - {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <h1 class="text-xl font-bold text-gray-900">{{ config('app.name') }}</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('home') }}
" class="text-gray-600 hover:text-blue-600 transition-colors">
                        <i class="fas fa-calendar mr-2"></i>View More Events
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Success Message -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-green-100 rounded-full mb-6">
                <i class="fas fa-check-circle text-4xl text-green-600"></i>
            </div>
            <h1 class="text-4xl font-bold text-gray-900 mb-4">Registration Successful!</h1>
            <p class="text-xl text-gray-600">Thank you for registering for {{ $registration->event->name }}</p>
        </div>

        <!-- Registration Details -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden mb-8">
            <div class="bg-gradient-to-r from-green-600 to-blue-600 p-6">
                <h2 class="text-2xl font-bold text-white text-center">
                    <i class="fas fa-ticket-alt mr-3"></i>Registration Confirmation
                </h2>
            </div>

            <div class="p-8">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Event Information -->
                    <div class="space-y-6">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                <i class="fas fa-calendar-alt mr-3 text-blue-600"></i>Event Details
                            </h3>
                            
                            <div class="space-y-4">
                                <div class="border-l-4 border-blue-500 pl-4">
                                    <p class="text-sm text-gray-500">Event Name</p>
                                    <p class="text-lg font-semibold text-gray-900">{{ $registration->event->name }}</p>
                                </div>
                                
                                <div class="border-l-4 border-green-500 pl-4">
                                    <p class="text-sm text-gray-500">Date & Time</p>
                                    <p class="font-semibold text-gray-900">{{ $registration->event->start_date->format('F j, Y') }}</p>
                                    <p class="text-gray-600">{{ $registration->event->start_date->format('g:i A') }} - {{ $registration->event->end_date->format('g:i A') }}</p>
                                </div>
                                
                                @if($registration->event->venue)
                                <div class="border-l-4 border-purple-500 pl-4">
                                    <p class="text-sm text-gray-500">Venue</p>
                                    <p class="font-semibold text-gray-900">{{ $registration->event->venue->name }}</p>
                                    @if($registration->event->venue->address)
                                    <p class="text-gray-600">{{ $registration->event->venue->address }}</p>
                                    @endif
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Ticket Information -->
                        @if($registration->ticketType)
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                <i class="fas fa-ticket-alt mr-3 text-blue-600"></i>Ticket Information
                            </h3>
                            
                            <div class="bg-blue-50 rounded-lg p-4">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="font-semibold text-gray-900">{{ $registration->ticketType->name }}</p>
                                        @if($registration->ticketType->description)
                                        <p class="text-gray-600 text-sm mt-1">{{ $registration->ticketType->description }}</p>
                                        @endif
                                    </div>
                                    <div class="text-right">
                                        @if($registration->ticketType->price == 0)
                                            <span class="text-xl font-bold text-green-600">FREE</span>
                                        @else
                                            <span class="text-xl font-bold text-blue-600">${{ number_format($registration->ticketType->price, 2) }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Registration Info & QR Code -->
                    <div class="space-y-6">
                        <!-- Personal Information -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                <i class="fas fa-user mr-3 text-blue-600"></i>Attendee Information
                            </h3>
                            
                            <div class="space-y-3">
                                <div class="border-l-4 border-blue-500 pl-4">
                                    <p class="text-sm text-gray-500">Name</p>
                                    <p class="font-semibold text-gray-900">{{ $registration->user->name }}</p>
                                </div>
                                
                                <div class="border-l-4 border-green-500 pl-4">
                                    <p class="text-sm text-gray-500">Email</p>
                                    <p class="font-semibold text-gray-900">{{ $registration->user->email }}</p>
                                </div>
                                
                                @if($registration->user->phone)
                                <div class="border-l-4 border-purple-500 pl-4">
                                    <p class="text-sm text-gray-500">Phone</p>
                                    <p class="font-semibold text-gray-900">{{ $registration->user->phone }}</p>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- QR Code -->
                        @if($registration->qrCode && $registration->qrCode->qr_image)
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                <i class="fas fa-qrcode mr-3 text-blue-600"></i>Your Entry Ticket
                            </h3>
                            
                            <div class="bg-gray-50 rounded-lg p-6 text-center">
                                <img src="{{ Storage::url($registration->qrCode->qr_image) }}" 
                                     alt="QR Code for {{ $registration->user->name }}" 
                                     class="w-32 h-32 mx-auto mb-4 border-2 border-gray-300 rounded-lg">
                                <p class="text-sm text-gray-600 mb-4">Show this QR code at the event entrance</p>
                                <a href="{{ route('public.registration.download-qr', $registration) }}" 
                                   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                    <i class="fas fa-download mr-2"></i>Download QR Code
                                </a>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Registration Data -->
                @if($registration->registration_data && count($registration->registration_data) > 0)
                <div class="mt-8 pt-8 border-t border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-info-circle mr-3 text-blue-600"></i>Additional Information
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($registration->registration_data as $key => $value)
                            @if($value)
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-sm text-gray-500 mb-1">{{ $key }}</p>
                                @if(is_array($value))
                                    <p class="font-medium text-gray-900">{{ implode(', ', $value) }}</p>
                                @else
                                    <p class="font-medium text-gray-900">{{ $value }}</p>
                                @endif
                            </div>
                            @endif
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Next Steps -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden mb-8">
            <div class="p-6">
                <h3 class="text-xl font-semibold text-gray-900 mb-6 flex items-center">
                    <i class="fas fa-list-check mr-3 text-blue-600"></i>What's Next?
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="text-center p-6 bg-blue-50 rounded-xl">
                        <div class="w-12 h-12 bg-blue-600 text-white rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-envelope text-xl"></i>
                        </div>
                        <h4 class="font-semibold text-gray-900 mb-2">Check Your Email</h4>
                        <p class="text-gray-600 text-sm">We've sent a confirmation email with all the details and your QR code.</p>
                    </div>
                    
                    <div class="text-center p-6 bg-green-50 rounded-xl">
                        <div class="w-12 h-12 bg-green-600 text-white rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-calendar-plus text-xl"></i>
                        </div>
                        <h4 class="font-semibold text-gray-900 mb-2">Add to Calendar</h4>
                        <p class="text-gray-600 text-sm">Save the date! Don't forget to mark this event in your calendar.</p>
                    </div>
                    
                    <div class="text-center p-6 bg-purple-50 rounded-xl">
                        <div class="w-12 h-12 bg-purple-600 text-white rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-mobile-alt text-xl"></i>
                        </div>
                        <h4 class="font-semibold text-gray-900 mb-2">Save QR Code</h4>
                        <p class="text-gray-600 text-sm">Download and save your QR code on your phone for easy access.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Information -->
        <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-2xl shadow-xl text-white p-8 text-center">
            <h3 class="text-2xl font-bold mb-4">Need Help?</h3>
            <p class="text-blue-100 mb-6">If you have any questions about your registration or the event, don't hesitate to contact us.</p>
            
            <div class="flex flex-col sm:flex-row justify-center items-center space-y-4 sm:space-y-0 sm:space-x-6">
                <a href="mailto:support@{{ config('app.domain', 'example.com') }}" 
                   class="inline-flex items-center px-6 py-3 bg-white text-blue-600 rounded-lg hover:bg-gray-100 transition-colors">
                    <i class="fas fa-envelope mr-2"></i>Email Support
                </a>
                
                <a href="{{ route('home') }}
" 
                   class="inline-flex items-center px-6 py-3 border-2 border-white text-white rounded-lg hover:bg-white hover:text-blue-600 transition-colors">
                    <i class="fas fa-calendar mr-2"></i>Browse More Events
                </a>
            </div>
        </div>

        <!-- Registration ID for Reference -->
        <div class="text-center mt-8">
            <p class="text-sm text-gray-500">
                Registration ID: <span class="font-mono font-semibold">#{{ $registration->id }}</span>
                | Status: <span class="font-semibold text-green-600">{{ ucfirst($registration->status) }}</span>
            </p>
        </div>
    </div>

    <script>
        // Auto-hide success message after 5 seconds
        setTimeout(function() {
            const successDiv = document.querySelector('.bg-green-100');
            if (successDiv) {
                successDiv.style.transition = 'opacity 0.5s';
                successDiv.style.opacity = '0.7';
            }
        }, 5000);

        // Print functionality
        function printTicket() {
            window.print();
        }

        // Add print button if QR code exists
        document.addEventListener('DOMContentLoaded', function() {
            const qrSection = document.querySelector('img[alt*="QR Code"]');
            if (qrSection) {
                const printBtn = document.createElement('button');
                printBtn.innerHTML = '<i class="fas fa-print mr-2"></i>Print Ticket';
                printBtn.className = 'inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors ml-2';
                printBtn.onclick = printTicket;
                qrSection.parentNode.appendChild(printBtn);
            }
        });
    </script>
</body>
</html>