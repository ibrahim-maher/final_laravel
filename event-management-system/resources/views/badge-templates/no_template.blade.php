@extends('layouts.app')

@section('title', 'No Badge Template')
@section('page-title', 'Badge Template Required')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center space-x-4">
        <a href="{{ route('registrations.index') }}" class="text-gray-600 hover:text-gray-900">
            <i class="fas fa-arrow-left mr-2"></i>
            Back to Registrations
        </a>
    </div>

    <!-- No Template Message -->
    <div class="bg-white rounded-xl shadow-lg p-8 text-center">
        <div class="max-w-md mx-auto">
            <div class="bg-yellow-100 rounded-full p-6 w-24 h-24 mx-auto mb-6 flex items-center justify-center">
                <i class="fas fa-exclamation-triangle text-yellow-600 text-3xl"></i>
            </div>
            
            <h2 class="text-2xl font-bold text-gray-900 mb-4">No Badge Template Found</h2>
            
            <p class="text-gray-600 mb-6">
                A badge template hasn't been created for this ticket type yet. You need to create a template before printing badges.
            </p>

            <!-- Registration Details -->
            <div class="bg-gray-50 rounded-lg p-4 mb-6 text-left">
                <h3 class="font-semibold text-gray-900 mb-3">Registration Details:</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Participant:</span>
                        <span class="font-medium">{{ $registration->user->name ?? 'Unknown' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Event:</span>
                        <span class="font-medium">{{ $registration->ticketType->event->name ?? 'Unknown Event' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Ticket Type:</span>
                        <span class="font-medium">{{ $registration->ticketType->name ?? 'Unknown Ticket' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Registration Date:</span>
                        <span class="font-medium">{{ $registration->created_at->format('M d, Y') }}</span>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="space-y-4">
                <a href="{{ route('badge-templates.createOrEdit', ['ticket' => $registration->ticket_type_id]) }}" 
                   class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-lg font-medium">
                    <i class="fas fa-plus mr-3"></i>
                    Create Badge Template
                </a>
                
                <div class="text-sm text-gray-500">
                    This will take you to the template creation page for this ticket type.
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Setup Guide -->
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">
            <i class="fas fa-rocket text-blue-500 mr-2"></i>
            Quick Template Setup Guide
        </h3>
        
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="text-center">
                <div class="bg-blue-500 text-white rounded-full w-8 h-8 flex items-center justify-center mx-auto mb-2 text-sm font-bold">1</div>
                <h4 class="font-medium text-gray-900 mb-1">Create Template</h4>
                <p class="text-sm text-gray-600">Click the button above to start creating your badge template</p>
            </div>
            
            <div class="text-center">
                <div class="bg-blue-500 text-white rounded-full w-8 h-8 flex items-center justify-center mx-auto mb-2 text-sm font-bold">2</div>
                <h4 class="font-medium text-gray-900 mb-1">Design Layout</h4>
                <p class="text-sm text-gray-600">Set badge size, add background image, and configure content fields</p>
            </div>
            
            <div class="text-center">
                <div class="bg-blue-500 text-white rounded-full w-8 h-8 flex items-center justify-center mx-auto mb-2 text-sm font-bold">3</div>
                <h4 class="font-medium text-gray-900 mb-1">Preview & Adjust</h4>
                <p class="text-sm text-gray-600">Use the interactive preview to position elements perfectly</p>
            </div>
            
            <div class="text-center">
                <div class="bg-blue-500 text-white rounded-full w-8 h-8 flex items-center justify-center mx-auto mb-2 text-sm font-bold">4</div>
                <h4 class="font-medium text-gray-900 mb-1">Print Badges</h4>
                <p class="text-sm text-gray-600">Once saved, you can print badges for all registrations</p>
            </div>
        </div>
    </div>

    <!-- Available Fields Info -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Available Badge Fields</h3>
        <p class="text-gray-600 mb-4">When creating your template, you can include any of these fields:</p>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach(\App\Models\BadgeContent::FIELD_CHOICES as $value => $label)
            <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                <div class="w-2 h-2 bg-blue-500 rounded-full mr-3"></div>
                <div>
                    <p class="font-medium text-gray-900">{{ $label }}</p>
                    <p class="text-xs text-gray-500">{{ $value }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection