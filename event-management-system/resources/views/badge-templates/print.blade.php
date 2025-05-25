@extends('layouts.app')

@section('title', 'Print Badge')
@section('page-title', 'Print Badge')

@push('styles')
<style>
    @media print {
        body * {
            visibility: hidden;
        }
        .printable-badge, .printable-badge * {
            visibility: visible;
        }
        .printable-badge {
            position: absolute;
            left: 0;
            top: 0;
            background: white !important;
        }
        
        .no-print {
            display: none !important;
        }
        
        .print-page {
            margin: 0;
            padding: 20px;
            background: white;
        }

        @page {
            margin: 1cm;
            size: auto;
        }
    }

    .badge-print-container {
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        border: 1px solid #e5e7eb;
        background-color: white;
        position: relative;
        margin: 0 auto;
    }

    .badge-content {
        position: absolute;
        white-space: nowrap;
    }

    .qr-code-image {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }

    .badge-field-value {
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
    }
</style>
@endpush

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between no-print">
        <div class="flex items-center space-x-4">
            <a href="{{ route('registrations.index') }}" class="text-gray-600 hover:text-gray-900">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Registrations
            </a>
            <div class="h-6 border-l border-gray-300"></div>
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Print Badge</h2>
                <p class="text-sm text-gray-600">{{ $registration->user->name ?? 'Unknown User' }} • {{ $badgeTemplate->ticket->event->name ?? 'Unknown Event' }}</p>
            </div>
        </div>
        
        <div class="flex items-center space-x-3">
            <a href="{{ route('badge-templates.preview', $badgeTemplate) }}" 
               class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                <i class="fas fa-eye mr-2"></i>
                Preview Template
            </a>
            <button onclick="window.print()" 
                    class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                <i class="fas fa-print mr-2"></i>
                Print Badge
            </button>
        </div>
    </div>

    <!-- Registration Information -->
    <div class="bg-white rounded-xl shadow-lg p-6 no-print">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Registration Details</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 text-sm">
            <div>
                <p class="text-gray-600">Participant Name</p>
                <p class="font-medium">{{ $registration->user->name ?? 'Unknown' }}</p>
            </div>
            <div>
                <p class="text-gray-600">Email</p>
                <p class="font-medium">{{ $registration->user->email ?? 'Unknown' }}</p>
            </div>
            <div>
                <p class="text-gray-600">Ticket Type</p>
                <p class="font-medium">{{ $registration->ticketType->name ?? 'Unknown' }}</p>
            </div>
            <div>
                <p class="text-gray-600">Event</p>
                <p class="font-medium">{{ $badgeTemplate->ticket->event->name ?? 'Unknown' }}</p>
            </div>
            <div>
                <p class="text-gray-600">Registration Date</p>
                <p class="font-medium">{{ $registration->created_at->format('M d, Y') }}</p>
            </div>
            <div>
                <p class="text-gray-600">Status</p>
                <p class="font-medium">
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                        {{ $registration->status === 'confirmed' ? 'bg-green-100 text-green-800' : 
                           ($registration->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                        {{ ucfirst($registration->status ?? 'unknown') }}
                    </span>
                </p>
            </div>
        </div>
    </div>

    <!-- Badge Preview -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex items-center justify-between mb-6 no-print">
            <h3 class="text-lg font-semibold text-gray-900">Badge Preview</h3>
            <div class="text-sm text-gray-600">
                Template: {{ $badgeTemplate->name }} ({{ $badgeTemplate->width }} × {{ $badgeTemplate->height }} cm)
            </div>
        </div>

        <!-- Badge Container -->
        <div class="print-page">
            <div class="badge-print-container printable-badge mx-auto"
                 style="width: {{ $badgeTemplate->width }}cm; height: {{ $badgeTemplate->height }}cm;">

                @if($badgeTemplate->background_image_url)
                <div class="absolute inset-0 w-full h-full"
                     style="background-image: url('{{ $badgeTemplate->background_image_url }}');
                            background-size: cover;
                            background-position: center;
                            border-radius: 8px;
                            z-index: 0;">
                </div>
                @endif

                @foreach($badgeTemplate->contents as $content)
                <div class="badge-content badge-field-value"
                     style="left: {{ $content->position_x }}cm;
                            top: {{ $content->position_y }}cm;
                            font-size: {{ $content->font_size }}pt;
                            color: {{ $content->font_color }};
                            font-family: {{ $content->font_family }};
                            {{ $content->is_bold ? 'font-weight: bold;' : '' }}
                            {{ $content->is_italic ? 'font-style: italic;' : '' }}
                            z-index: 10;">
                    
                    @if($content->isQrCodeField())
                        @if(isset($badgeData[$content->field_name]) && $badgeData[$content->field_name])
                            <img src="{{ $badgeData[$content->field_name] }}" 
                                 alt="QR Code" 
                                 class="qr-code-image"
                                 style="width: {{ $content->image_width ?? 2 }}cm; height: {{ $content->image_height ?? 2 }}cm;">
                        @else
                            <div class="bg-gray-200 border-2 border-dashed border-gray-400 rounded flex items-center justify-center text-xs text-gray-600"
                                 style="width: {{ $content->image_width ?? 2 }}cm; height: {{ $content->image_height ?? 2 }}cm;">
                                QR CODE
                            </div>
                        @endif
                    @else
                        {{ $badgeData[$content->field_name] ?? $content->getFieldDisplayName() }}
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Badge Data Summary -->
    <div class="bg-gray-50 rounded-xl p-6 no-print">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Badge Data Fields</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($badgeTemplate->contents as $content)
            <div class="bg-white p-4 rounded-lg border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $content->getFieldDisplayName() }}</p>
                        <p class="text-sm text-gray-600">{{ $content->field_name }}</p>
                    </div>
                    <div class="text-right">
                        @if($content->isQrCodeField())
                            <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">QR Code</span>
                        @else
                            <p class="text-sm font-medium text-gray-900">
                                {{ Str::limit($badgeData[$content->field_name] ?? 'N/A', 30) }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Print Instructions -->
    <div class="bg-blue-50 rounded-xl p-6 no-print">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-info-circle text-blue-500 text-xl"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-lg font-medium text-blue-900">Printing Instructions</h3>
                <div class="mt-2 text-sm text-blue-700">
                    <ul class="list-disc list-inside space-y-1">
                        <li>Make sure your printer is set to the correct paper size</li>
                        <li>For best results, use cardstock or heavy paper (200-300 GSM)</li>
                        <li>Ensure "Fit to page" is disabled in your print settings</li>
                        <li>Check that margins are set to minimum</li>
                        <li>Consider laminating badges for durability</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection