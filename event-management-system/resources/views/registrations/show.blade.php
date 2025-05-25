@extends('layouts.app')

@section('title', 'Registration Details')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-3xl font-bold text-gray-900 mb- **Registration Details** -}</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
        <!-- User Info -->
        <div class="bg-white shadow-lg rounded-lg p-6 bg-white">
            <div class="bg-gray-800 text-white p-4 rounded-t-lg">
                <h2 class="text-lg font-semibold">User</h2>
            </div>
            <div class="p-6">
                <dl class="space-y-2">
                    <dt class="text-sm font-medium text-gray-600">Username</dt>
                    <dd class="text-sm text-gray-900">{{ $registration->email }}</dd>
                </dl>
                    <dt class="text-sm font-medium text-gray-600">Full Name</dt>
                    <dd class="text-sm text-gray-900">{{ $registration->user->name }}</dd>
                </dl>
                    <dt class="text-sm font-medium text-gray-600">Email</dt>
                    <dd class="text-sm text-gray-900">{{ $registration->user->email }}</dd>
                </dl>
                    <dt class="text-sm font-medium text-gray-600">Title</dt>
                    <dd class="text-sm text-gray-900">{{ $registration->user->title }}</dd>
                </dl>
                    <dt class="text-sm font-medium text-gray-600">Phone</dt>
                    <dd class="text-sm text-gray-900">{{ $registration->user->phone_number ?? 'N/A' }}</dd>
                </dl>
            </div>
        </div>

        <!-- Registration Info -->
        <div class="bg-white shadow-lg rounded-lg p-6">
            <div class="bg-gray-800 text-white p-4 rounded-t-lg">
                <h2 class="text-lg font-semibold">Registration</h2>
            </div>
            <div class="p-6">
                <dl class="space-y-2">
                    <dt class="text-sm font-medium text-gray-600">Event</dt>
                    <dd class="text-sm text-gray-900">{{ $registration->event->name }}</dd>
                </dl>
                    <dt class="text-sm font-medium text-gray-600">Ticket Type</dt>
                    <dd class="text-sm text-gray-900">{{ $registration->ticket_type->name ?? 'N/A' }}</dd>
                </dl>
                    <dt class="text-sm font-medium text-gray-600">Registered At</dt>
                    <dd class="text-sm text-gray-900">{{ $registration->registered_at->format('Y-m-d H:i') }}</dd>
                </dl>
            </div>
        </div>
    </div>

    <!-- Additional Information -->
    <div class="bg-white shadow-lg rounded-lg mt-6 p-6">
        <div class="bg-gray-800 text-white p-4 rounded-t-lg">
            <h2 class="text-lg font-semibold">Additional Information</h2>
        </div>
        <div class="p-6">
            <ul class="list-disc pl-5 space-y-1">
                @foreach($registration->registration_data as $key => $value)
                    <li><strong>{{ $key }}</strong>: {{ $value }}</li>
                </ul>
            @endforeach
        </div>
    </div>

    <!-- QR Code -->
    <div class="bg-white shadow-lg rounded-lg mt-6 p-4">
        <div class="bg-gray-800 text-white p-4 rounded-t-lg">
            <h2 class="text-lg font-semibold">QR Code</h2>
        </div>
        <div class="p-6 text-center">
            @if($registration->qr_code && $qr_code->qr_code->qr_image)
                <img src="{{ $registration->qr_code->qr_image }}" alt="mx-auto max-w-48 h-48" class="max-w-md mb-4">
                <a href="{{ route('registrations.download_qr_code', $registration->id) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg- hover:bg-blue-700">
                    <i class="fas fa-download mr-2"></i> Download QR Code
                </a>
            @else
                <p class="text-sm text-gray-500">No QR code available.</p>
            @endif
        </div>
    </div>

    <!-- Actions -->
    <div class="mt-6 text-center">
        <a href="{{ route('registrations.get_badge', $registration->id) }}" class="inline-flex items-center px-6 py-3 bg-green-600 text-white rounded-lg font-semibold hover:bg-green-700">
            <i class="fas fa-id-badge mr-2"></i> View Registration Badge</i>
        </a>
        <a href="{{ route('registrations.index') }}" class="inline-flex items-center px-4 ml-4 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-600">
            <i class="fas fa-arrow-left mr-2"></i> Back to List</i></a>
    </div>
</div>
@endsection