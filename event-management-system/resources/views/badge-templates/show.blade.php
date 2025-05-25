@extends('layouts.app')

@section('title', 'Badge Template Details')
@section('page-title', 'Badge Template Details')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <a href="{{ route('badge-templates.index') }}" class="text-gray-600 hover:text-gray-900">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Templates
            </a>
            <div class="h-6 border-l border-gray-300"></div>
            <div>
                <h2 class="text-2xl font-semibold text-gray-900">{{ $badgeTemplate->name }}</h2>
                <p class="text-gray-600">{{ $badgeTemplate->ticket->event->name ?? 'No Event' }} • {{ $badgeTemplate->ticket->name ?? 'No Ticket' }}</p>
            </div>
        </div>
        
        <div class="flex items-center space-x-3">
            <a href="{{ route('badge-templates.preview', $badgeTemplate) }}" 
               class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                <i class="fas fa-eye mr-2"></i>
                Preview
            </a>
            <a href="{{ route('badge-templates.createOrEdit', ['ticket' => $badgeTemplate->ticket_id]) }}" 
               class="inline-flex items-center px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700">
                <i class="fas fa-edit mr-2"></i>
                Edit
            </a>
            <form method="POST" action="{{ route('badge-templates.destroy', $badgeTemplate) }}" 
                  class="inline" onsubmit="return confirm('Are you sure you want to delete this template?')">
                @csrf
                @method('DELETE')
                <button type="submit" 
                        class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                    <i class="fas fa-trash mr-2"></i>
                    Delete
                </button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Template Information -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Basic Information -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Template Information</h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Template Name</label>
                        <p class="text-gray-900">{{ $badgeTemplate->name }}</p>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Width</label>
                            <p class="text-gray-900">{{ $badgeTemplate->width }} cm</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Height</label>
                            <p class="text-gray-900">{{ $badgeTemplate->height }} cm</p>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Default Font</label>
                        <p class="text-gray-900">{{ $badgeTemplate->default_font }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Background Image</label>
                        @if($badgeTemplate->background_image_url)
                            <div class="mt-2">
                                <img src="{{ $badgeTemplate->background_image_url }}" 
                                     alt="Background Image" 
                                     class="w-full h-32 object-cover rounded-lg border border-gray-200">
                            </div>
                        @else
                            <p class="text-gray-500 italic">No background image</p>
                        @endif
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Created By</label>
                        <p class="text-gray-900">{{ $badgeTemplate->creator->name ?? 'Unknown' }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Created Date</label>
                        <p class="text-gray-900">{{ $badgeTemplate->created_at->format('M d, Y \a\t g:i A') }}</p>
                    </div>
                    
                    @if($badgeTemplate->updated_at != $badgeTemplate->created_at)
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Last Updated</label>
                        <p class="text-gray-900">{{ $badgeTemplate->updated_at->format('M d, Y \a\t g:i A') }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Event & Ticket Information -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Associated Event & Ticket</h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Event</label>
                        <div class="mt-1">
                            <p class="text-gray-900 font-medium">{{ $badgeTemplate->ticket->event->name ?? 'No Event' }}</p>
                            @if($badgeTemplate->ticket->event)
                            <p class="text-sm text-gray-600">{{ $badgeTemplate->ticket->event->start_date->format('M d, Y') }} - {{ $badgeTemplate->ticket->event->end_date->format('M d, Y') }}</p>
                            <p class="text-sm text-gray-600">{{ $badgeTemplate->ticket->event->venue->name ?? 'No Venue' }}</p>
                            @endif
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Ticket Type</label>
                        <div class="mt-1">
                            <p class="text-gray-900 font-medium">{{ $badgeTemplate->ticket->name ?? 'No Ticket' }}</p>
                            @if($badgeTemplate->ticket->price)
                            <p class="text-sm text-gray-600">${{ number_format($badgeTemplate->ticket->price, 2) }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Template Statistics</h3>
                
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Total Fields</span>
                        <span class="font-semibold text-gray-900">{{ $badgeTemplate->contents->count() }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Text Fields</span>
                        <span class="font-semibold text-gray-900">{{ $badgeTemplate->contents->where('field_name', '!=', 'qr_code__qr_image')->count() }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">QR Code Fields</span>
                        <span class="font-semibold text-gray-900">{{ $badgeTemplate->contents->where('field_name', 'qr_code__qr_image')->count() }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Badge Preview and Content Fields -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Badge Preview -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Badge Preview</h3>
                
                <div class="flex justify-center">
                    <div class="relative border-2 border-dashed border-gray-300 rounded-lg p-4" 
                         style="width: {{ min($badgeTemplate->width * 2, 400) }}px; height: {{ min($badgeTemplate->height * 2, 250) }}px;">
                        
                        @if($badgeTemplate->background_image_url)
                        <div class="absolute inset-4 bg-cover bg-center rounded" 
                             style="background-image: url('{{ $badgeTemplate->background_image_url }}');"></div>
                        @else
                        <div class="absolute inset-4 bg-gradient-to-br from-gray-100 to-gray-200 rounded"></div>
                        @endif
                        
                        <!-- Content Fields Preview -->
                        @foreach($badgeTemplate->contents as $content)
                        <div class="absolute text-xs font-medium text-gray-800 bg-white bg-opacity-90 px-1 rounded shadow-sm"
                             style="left: {{ min(($content->position_x / $badgeTemplate->width) * 100, 90) }}%; 
                                    top: {{ min(($content->position_y / $badgeTemplate->height) * 100, 90) }}%;">
                            @if($content->isQrCodeField())
                                <div class="w-4 h-4 bg-gray-300 border border-gray-400 rounded flex items-center justify-center text-xs">QR</div>
                            @else
                                {{ Str::limit($content->getFieldDisplayName(), 15) }}
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
                
                <div class="mt-4 text-center text-sm text-gray-600">
                    Preview scaled to fit • Actual size: {{ $badgeTemplate->width }} × {{ $badgeTemplate->height }} cm
                </div>
            </div>

            <!-- Content Fields -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Content Fields ({{ $badgeTemplate->contents->count() }})</h3>
                    <a href="{{ route('badge-templates.preview', $badgeTemplate) }}" 
                       class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                        Edit in Preview Mode →
                    </a>
                </div>
                
                @if($badgeTemplate->contents->count() > 0)
                <div class="space-y-4">
                    @foreach($badgeTemplate->contents as $index => $content)
                    <div class="border border-gray-200 rounded-lg p-4 hover:border-gray-300 transition-colors">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center space-x-3 mb-2">
                                    <span class="inline-flex items-center justify-center w-6 h-6 bg-blue-100 text-blue-800 rounded-full text-xs font-bold">
                                        {{ $index + 1 }}
                                    </span>
                                    <h4 class="font-medium text-gray-900">{{ $content->getFieldDisplayName() }}</h4>
                                    @if($content->isQrCodeField())
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-purple-100 text-purple-800">
                                        QR Code
                                    </span>
                                    @endif
                                </div>
                                
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                                    <div>
                                        <span class="text-gray-500">Position:</span>
                                        <span class="text-gray-900">{{ $content->position_x }}, {{ $content->position_y }} cm</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Font Size:</span>
                                        <span class="text-gray-900">{{ $content->font_size }}pt</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Font:</span>
                                        <span class="text-gray-900">{{ $content->font_family }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Style:</span>
                                        <span class="text-gray-900">
                                            {{ $content->is_bold ? 'Bold' : '' }}
                                            {{ $content->is_bold && $content->is_italic ? ', ' : '' }}
                                            {{ $content->is_italic ? 'Italic' : '' }}
                                            {{ !$content->is_bold && !$content->is_italic ? 'Normal' : '' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="ml-4">
                                <div class="w-4 h-4 rounded border-2" 
                                     style="background-color: {{ $content->font_color }}; border-color: {{ $content->font_color }};"
                                     title="Font Color: {{ $content->font_color }}">
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-8">
                    <i class="fas fa-plus-circle text-4xl text-gray-300 mb-3"></i>
                    <p class="text-gray-500">No content fields defined yet</p>
                    <a href="{{ route('badge-templates.createOrEdit', ['ticket' => $badgeTemplate->ticket_id]) }}" 
                       class="mt-4 inline-flex items-center text-blue-600 hover:text-blue-800">
                        Add fields to this template →
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection