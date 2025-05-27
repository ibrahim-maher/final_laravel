@extends('layouts.app')

@section('title', 'Badge Preview')
@section('page-title', 'Badge Preview')

@push('styles')
<style>
    .badge-preview-container {
        transform-origin: top left;
        background-color: white;
        border: 3px solid #374151;
        border-radius: 12px;
        position: relative;
        overflow: hidden;
        box-shadow: 
            0 10px 25px rgba(0, 0, 0, 0.1),
            0 4px 10px rgba(0, 0, 0, 0.05),
            inset 0 1px 0 rgba(255, 255, 255, 0.9);
    }

    .badge-field {
        padding: 8px 12px;
        border-radius: 8px;
        background-color: rgba(255, 255, 255, 0.95);
        border: 2px solid transparent;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: move;
        user-select: none;
        min-width: 60px;
        text-align: center;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        z-index: 20;
        font-weight: 500;
    }

    .badge-field:hover {
        border-color: #3b82f6;
        background-color: rgba(59, 130, 246, 0.1);
        transform: translateY(-2px) scale(1.02);
        box-shadow: 0 8px 15px rgba(59, 130, 246, 0.2);
    }

    .badge-field.selected {
        border-color: #ef4444;
        box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.3);
        background-color: rgba(239, 68, 68, 0.1);
        z-index: 30;
        transform: scale(1.05);
    }

    .badge-field.dragging {
        z-index: 1000;
        transform: rotate(2deg) scale(1.08);
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
        border-color: #f59e0b;
        background-color: rgba(245, 158, 11, 0.2);
    }

    .grid-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-image: 
            linear-gradient(rgba(59, 130, 246, 0.4) 1px, transparent 1px),
            linear-gradient(90deg, rgba(59, 130, 246, 0.4) 1px, transparent 1px);
        background-size: 0.5cm 0.5cm;
        pointer-events: none;
        opacity: 0;
        transition: opacity 0.3s ease;
        z-index: 5;
        border-radius: 8px;
    }

    .grid-active .grid-overlay {
        opacity: 1;
    }

    .template-border {
        position: absolute;
        top: -3px;
        left: -3px;
        right: -3px;
        bottom: -3px;
        border: 2px dashed #6b7280;
        border-radius: 12px;
        pointer-events: none;
        z-index: 1;
    }

    .template-measurements {
        position: absolute;
        top: -30px;
        left: 0;
        right: 0;
        text-align: center;
        font-size: 12px;
        color: #6b7280;
        font-weight: 600;
    }

    .template-measurements::before {
        content: '{{ $badgeTemplate->width }} × {{ $badgeTemplate->height }} cm';
    }

    .zoom-controls {
        position: fixed;
        bottom: 24px;
        right: 24px;
        z-index: 100;
    }

    .zoom-controls button:hover {
        transform: scale(1.1);
    }

    .badge-preview-wrapper {
        overflow: auto;
        max-height: 75vh;
        border-radius: 16px;
        background: 
            radial-gradient(circle at 20% 20%, rgba(59, 130, 246, 0.1) 0%, transparent 50%),
            radial-gradient(circle at 80% 80%, rgba(139, 92, 246, 0.1) 0%, transparent 50%),
            linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        padding: 32px;
        position: relative;
    }

    /* Enhanced Print Styles */
    @media print {
        @page {
            size: {{ $badgeTemplate->width }}cm {{ $badgeTemplate->height }}cm;
            margin: 0;
        }
        
        body * {
            visibility: hidden;
        }
        
        .badge-preview-container,
        .badge-preview-container * {
            visibility: visible;
        }
        
        .badge-preview-container {
            position: absolute;
            left: 0;
            top: 0;
            transform: none !important;
            box-shadow: none !important;
            border: 1px solid #000 !important;
            border-radius: 0 !important;
            width: {{ $badgeTemplate->width }}cm !important;
            height: {{ $badgeTemplate->height }}cm !important;
        }
        
        .badge-field {
            box-shadow: none !important;
            border: none !important;
            background: transparent !important;
        }
        
        .grid-overlay,
        .template-border,
        .template-measurements,
        .zoom-controls,
        .no-print {
            display: none !important;
        }
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .badge-preview-wrapper {
            padding: 16px;
        }
        
        .badge-field {
            min-width: 50px;
            padding: 6px 10px;
        }
        
        .zoom-controls {
            bottom: 16px;
            right: 16px;
        }
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Enhanced Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8 space-y-4 sm:space-y-0">
            <div class="flex items-center space-x-4">
                <a href="{{ route('badge-templates.index') }}" 
                   class="no-print inline-flex items-center px-3 py-2 text-sm font-medium text-gray-600 bg-white rounded-lg border border-gray-300 hover:text-gray-900 hover:bg-gray-50 transition-colors shadow-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Back to Templates
                </a>
                <div class="hidden sm:block h-6 border-l border-gray-300"></div>
                <div>
                    <h1 class="text-2xl lg:text-3xl font-bold text-gray-900">{{ $badgeTemplate->name }}</h1>
                    <p class="text-sm text-gray-600 mt-1">
                        <span class="inline-flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            {{ $badgeTemplate->ticket->event->name ?? 'No Event' }}
                        </span>
                        <span class="mx-2">•</span>
                        <span class="inline-flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                            </svg>
                            {{ $badgeTemplate->ticket->name ?? 'No Ticket' }}
                        </span>
                    </p>
                </div>
            </div>
            
            <div class="flex items-center space-x-3 no-print">
                <a href="{{ route('badge-templates.createOrEdit', ['ticket' => $badgeTemplate->ticket_id]) }}" 
                   class="inline-flex items-center px-4 py-2 bg-yellow-500 text-white text-sm font-medium rounded-lg hover:bg-yellow-600 transition-colors shadow-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Edit Template
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-4 gap-8">
            <!-- Enhanced Preview Area -->
            <div class="xl:col-span-3">
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-200">
                    <!-- Control Header -->
                    <div class="bg-gradient-to-r from-gray-50 to-blue-50 px-6 py-4 border-b border-gray-200 no-print">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-3 sm:space-y-0">
                            <div class="flex items-center space-x-3">
                                <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                                <h2 class="text-lg font-semibold text-gray-900">Interactive Preview</h2>
                                <span class="text-sm text-gray-500 bg-white px-2 py-1 rounded-full" id="zoom-indicator">100%</span>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                              
                                <button id="resetPreview" 
                                        class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors" 
                                        aria-label="Reset Preview">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                    </svg>
                                    <span class="hidden sm:inline">Reset</span>
                                </button>
                                <button id="saveAllChanges" 
                                        class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors" 
                                        aria-label="Save All Changes">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                                    </svg>
                                    <span class="save-text">Save All</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Preview Container -->

                 <!-- Enhanced Zoom Controls -->
                    <div class="zoom-controls no-print">
                        <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-2 flex items-center space-x-2"
                            style="width: 300px; margin: 0 auto;">
                            <button id="zoomOut" 
                                    class="p-2 text-gray-600 hover:text-white hover:bg-blue-500 rounded-lg transition-colors" 
                                    aria-label="Zoom Out">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                </svg>
                            </button>
                            <div class="text-sm font-medium text-gray-700 px-2 py-1 bg-gray-100 rounded min-w-[60px] text-center" id="zoomLevel">100%</div>
                            <button id="resetZoom" 
                                    class="p-2 text-gray-600 hover:text-white hover:bg-gray-500 rounded-lg transition-colors" 
                                    aria-label="Reset Zoom">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                            </button>
                            <button id="zoomIn" 
                                    class="p-2 text-gray-600 hover:text-white hover:bg-blue-500 rounded-lg transition-colors" 
                                    aria-label="Zoom In">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                            </button>
                        </div>
                    </div>


                    <div class="p-6">
                       
                        <div class="badge-preview-wrapper">
                            <div class="relative flex justify-center">
                                <div id="badge-preview"
                                     class="badge-preview-container relative"
                                     style="width: {{ $badgeTemplate->width }}cm; height: {{ $badgeTemplate->height }}cm; border: 1px solid gray;"
                                     data-width="{{ $badgeTemplate->width }}"
                                     data-height="{{ $badgeTemplate->height }}">

                                    <!-- Template Border -->
                                    <div class="template-border no-print"></div>
                                    <div class="template-measurements no-print"></div>

                                    <!-- Background Image -->
                                    @if($badgeTemplate->background_image_url)
                                    <div class="absolute inset-0 bg-cover bg-center rounded-lg z-0"
                                         style="background-image: url('{{ $badgeTemplate->background_image_url }}');">
                                    </div>
                                    @endif

                                    <!-- Grid Overlay -->
                                    <div class="grid-overlay"></div>

                                    <!-- Badge Fields -->
                                    @foreach($badgeTemplate->contents as $content)
                                    <div class="badge-field"
                                         data-field-id="{{ $content->id }}"
                                         data-field-name="{{ $content->field_name }}"
                                         data-original-x="{{ $content->position_x }}"
                                         data-original-y="{{ $content->position_y }}"
                                         style="position: absolute;
                                                left: {{ $content->position_x }}cm;
                                                top: {{ $content->position_y }}cm;
                                                font-size: {{ $content->font_size }}pt;
                                                color: {{ $content->font_color }};
                                                font-family: {{ $content->font_family }};
                                                {{ $content->is_bold ? 'font-weight: bold;' : '' }}
                                                {{ $content->is_italic ? 'font-style: italic;' : '' }}">
                                        @if($content->isQrCodeField())
                                            <div class="bg-gray-200 border-2 border-dashed border-gray-400 rounded-lg flex items-center justify-center text-xs text-gray-600 font-mono" 
                                                 style="width: {{ $content->image_width ?? 2 }}cm; height: {{ $content->image_height ?? 2 }}cm;">
                                                <div class="text-center">
                                                    <svg class="w-6 h-6 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                                                    </svg>
                                                    <div class="text-xs">QR CODE</div>
                                                </div>
                                            </div>
                                        @else
                                            {{ $content->getFieldDisplayName() }}
                                        @endif
                                    </div>
                                    @endforeach

                                   
                                </div>
                            </div>
                        </div>

                        <!-- Enhanced Template Info Cards -->
                        <div class="mt-8 grid grid-cols-2 md:grid-cols-4 gap-4 no-print">
                            <div class="bg-gradient-to-br from-blue-50 to-blue-100 p-4 rounded-xl border border-blue-200">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-blue-600 font-medium text-sm">Dimensions</p>
                                        <p class="text-blue-900 font-bold">{{ $badgeTemplate->width }} × {{ $badgeTemplate->height }} cm</p>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gradient-to-br from-green-50 to-green-100 p-4 rounded-xl border border-green-200">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-green-500 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-green-600 font-medium text-sm">Fields</p>
                                        <p class="text-green-900 font-bold">{{ $badgeTemplate->contents->count() }} fields</p>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gradient-to-br from-purple-50 to-purple-100 p-4 rounded-xl border border-purple-200">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-purple-500 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-purple-600 font-medium text-sm">Default Font</p>
                                        <p class="text-purple-900 font-bold text-xs">{{ $badgeTemplate->default_font }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gradient-to-br from-orange-50 to-orange-100 p-4 rounded-xl border border-orange-200">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-orange-500 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-orange-600 font-medium text-sm">Background</p>
                                        <p class="text-orange-900 font-bold text-xs">{{ $badgeTemplate->hasBackgroundImage() ? 'Custom Image' : 'None' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Enhanced Field Configuration Panel -->
            <div class="xl:col-span-1 no-print">
                <div class="bg-white rounded-2xl shadow-xl border border-gray-200">
                    <div class="bg-gradient-to-r from-gray-50 to-purple-50 px-6 py-4 border-b border-gray-200 rounded-t-2xl">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            Field Settings
                        </h3>
                    </div>
                    
                    <div class="p-6 max-h-[70vh] overflow-y-auto" id="field-config-panel">
                        <div id="no-field-selected" class="text-center py-12">
                            <div class="relative mb-6">
                                <svg class="w-16 h-16 text-gray-300 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"></path>
                                </svg>
                                <div class="absolute -bottom-1 left-1/2 transform -translate-x-1/2">
                                    <div class="w-4 h-4 bg-blue-500 rounded-full opacity-20 animate-ping"></div>
                                </div>
                            </div>
                            <h4 class="text-lg font-medium text-gray-700 mb-3">Select a Field</h4>
                            <p class="text-gray-500 text-sm leading-relaxed">
                                Click on any field in the preview to configure its properties, styling, and positioning options.
                            </p>
                        </div>

                        <form id="field-config-form" style="display: none;" class="space-y-6">
                            @csrf
                            <div class="text-center ">
                              
                                <h4 class="font-medium text-gray-900">Editing Field</h4>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                    </svg>
                                    Field Name
                                </label>
                                <input type="text" id="field-name" class="w-full px-4 py-3 border border-gray-300 rounded-xl bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" readonly>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                        </svg>
                                        Font Size
                                    </label>
                                    <input type="number" id="font-size" min="6" max="72" 
                                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path>
                                        </svg>
                                        Color
                                    </label>
                                    <input type="color" id="font-color" 
                                           class="w-full h-12 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    Font Family
                                </label>
                                <select id="font-family" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    @foreach(\App\Models\BadgeTemplate::FONT_CHOICES as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-3">
                                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Text Style
                                </label>
                                <div class="flex items-center space-x-6">
                                    <label class="flex items-center cursor-pointer">
                                        <input type="checkbox" id="is-bold" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                        <span class="ml-2 text-sm font-bold">Bold</span>
                                    </label>
                                    <label class="flex items-center cursor-pointer">
                                        <input type="checkbox" id="is-italic" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                        <span class="ml-2 text-sm italic">Italic</span>
                                    </label>
                                </div>
                            </div>

                            <div class="pt-4 border-t border-gray-100 space-y-3">
                                <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-blue-700 text-white px-4 py-3 rounded-xl font-medium hover:from-blue-700 hover:to-blue-800 transition-all duration-200 transform hover:scale-105 shadow-lg">
                                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Apply Changes
                                </button>
                                <button type="button" id="reset-field" class="w-full bg-gray-100 text-gray-700 px-4 py-3 rounded-xl font-medium hover:bg-gray-200 transition-all duration-200">
                                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                    </svg>
                                    Reset Field
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Enhanced Quick Actions -->
                <div class="mt-6 bg-white rounded-2xl shadow-xl border border-gray-200">
                    <div class="bg-gradient-to-r from-gray-50 to-green-50 px-6 py-4 border-b border-gray-200 rounded-t-2xl">
                        <h4 class="font-semibold text-gray-900 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                            Quick Actions
                        </h4>
                    </div>
                    <div class="p-6 space-y-3">
                        <a href="{{ route('badge-templates.createOrEdit', ['ticket' => $badgeTemplate->ticket_id]) }}" 
                           class="w-full flex items-center justify-center px-4 py-3 bg-gradient-to-r from-yellow-50 to-orange-50 text-yellow-700 rounded-xl font-medium hover:from-yellow-100 hover:to-orange-100 transition-all duration-200 transform hover:scale-105 border border-yellow-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            Edit Template
                        </a>
                        <button id="printPreview" 
                                class="w-full flex items-center justify-center px-4 py-3 bg-gradient-to-r from-green-50 to-emerald-50 text-green-700 rounded-xl font-medium hover:from-green-100 hover:to-emerald-100 transition-all duration-200 transform hover:scale-105 border border-green-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                            </svg>
                            Print Preview
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/interact.js/1.10.17/interact.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Enhanced state management
    const state = {
        currentZoom: 1,
        selectedField: null,
        defaultPositions: new Map(),
        defaultStyles: new Map()
    };

    // Constants
    const ZOOM_STEP = 0.15;
    const MAX_ZOOM = 3;
    const MIN_ZOOM = 0.3;

    // DOM elements
    const elements = {
        previewContainer: document.getElementById('badge-preview'),
        toggleGridBtn: document.getElementById('toggleGrid'),
        resetPreviewBtn: document.getElementById('resetPreview'),
        saveAllChangesBtn: document.getElementById('saveAllChanges'),
        zoomInBtn: document.getElementById('zoomIn'),
        zoomOutBtn: document.getElementById('zoomOut'),
        resetZoomBtn: document.getElementById('resetZoom'),
        zoomLevel: document.getElementById('zoomLevel'),
        zoomIndicator: document.getElementById('zoom-indicator'),
        fieldConfigForm: document.getElementById('field-config-form'),
        noFieldSelected: document.getElementById('no-field-selected')
    };

    // Initialize
    init();

    function init() {
        storeDefaultStates();
        initializeEventListeners();
        initializeDragAndDrop();
        updateZoomDisplay();
    }

    // Store initial positions and styles
    function storeDefaultStates() {
        document.querySelectorAll('.badge-field').forEach(field => {
            const fieldId = field.dataset.fieldId;
            
            state.defaultPositions.set(fieldId, {
                x: parseFloat(field.dataset.originalX) || 0,
                y: parseFloat(field.dataset.originalY) || 0
            });

            state.defaultStyles.set(fieldId, {
                fontSize: field.style.fontSize || '12pt',
                color: field.style.color || '#000000',
                fontFamily: field.style.fontFamily.replace(/['"]/g, '') || 'Arial',
                fontWeight: field.style.fontWeight || 'normal',
                fontStyle: field.style.fontStyle || 'normal'
            });
        });
    }

    // Event listeners
    function initializeEventListeners() {
        // Grid toggle
        elements.toggleGridBtn.addEventListener('click', toggleGrid);
        
        // Reset functionality
        elements.resetPreviewBtn.addEventListener('click', resetPreview);

        // Save all changes
        elements.saveAllChangesBtn.addEventListener('click', saveAllChanges);

        // Zoom controls
        elements.zoomInBtn.addEventListener('click', () => updateZoom(state.currentZoom + ZOOM_STEP));
        elements.zoomOutBtn.addEventListener('click', () => updateZoom(state.currentZoom - ZOOM_STEP));
        elements.resetZoomBtn.addEventListener('click', () => updateZoom(1));

        // Mouse wheel zoom
        elements.previewContainer.addEventListener('wheel', handleWheelZoom);

        // Field selection
        document.querySelectorAll('.badge-field').forEach(field => {
            field.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                selectField(field);
            });
        });

        // Form submission
        elements.fieldConfigForm.addEventListener('submit', handleFormSubmit);

        // Reset individual field
        document.getElementById('reset-field').addEventListener('click', resetIndividualField);

        // Print preview
        document.getElementById('printPreview').addEventListener('click', () => window.print());

        // Deselect on outside click
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.badge-field') && !e.target.closest('#field-config-panel')) {
                deselectField();
            }
        });
    }

    // Drag and drop
    function initializeDragAndDrop() {
        interact('.badge-field').draggable({
            modifiers: [
                interact.modifiers.snap({
                    targets: [interact.createSnapGrid({ x: 0.1 * 37.795, y: 0.1 * 37.795 })],
                    range: 15,
                    relativePoints: [{ x: 0, y: 0 }]
                }),
                interact.modifiers.restrict({
                    restriction: elements.previewContainer,
                    endOnly: true
                })
            ],
            listeners: {
                start: (event) => {
                    event.target.classList.add('dragging');
                },
                move: dragMoveListener,
                end: handleDragEnd
            }
        });
    }

    function dragMoveListener(event) {
        const target = event.target;
        const x = (parseFloat(target.getAttribute('data-x')) || 0) + event.dx;
        const y = (parseFloat(target.getAttribute('data-y')) || 0) + event.dy;

        target.style.transform = `translate(${x}px, ${y}px)`;
        target.setAttribute('data-x', x);
        target.setAttribute('data-y', y);
    }

    function handleDragEnd(event) {
        const target = event.target;
        target.classList.remove('dragging');
        
        const fieldId = target.dataset.fieldId;
        const containerRect = elements.previewContainer.getBoundingClientRect();
        const pixelToCm = parseFloat(elements.previewContainer.style.width) / containerRect.width;

        // Get transform values
        const transform = new WebKitCSSMatrix(window.getComputedStyle(target).transform);
        const currentX = transform.m41;
        const currentY = transform.m42;

        // Calculate new positions
        const currentLeft = parseFloat(target.style.left) || 0;
        const currentTop = parseFloat(target.style.top) || 0;
        const newLeft = currentLeft + (currentX * pixelToCm / state.currentZoom);
        const newTop = currentTop + (currentY * pixelToCm / state.currentZoom);

        // Ensure positions stay within bounds
        const maxX = parseFloat(elements.previewContainer.dataset.width);
        const maxY = parseFloat(elements.previewContainer.dataset.height);
        const posX = Math.max(0, Math.min(newLeft, maxX - 1));
        const posY = Math.max(0, Math.min(newTop, maxY - 1));

        // Update styles
        target.style.transform = 'none';
        target.style.left = `${posX}cm`;
        target.style.top = `${posY}cm`;
        target.removeAttribute('data-x');
        target.removeAttribute('data-y');

        // Auto-save position
        updateFieldPosition(fieldId, posX, posY);
    }

    // Grid functionality
    function toggleGrid() {
        elements.previewContainer.classList.toggle('grid-active');
        const isActive = elements.previewContainer.classList.contains('grid-active');
        
        elements.toggleGridBtn.setAttribute('aria-pressed', isActive);
        
        if (isActive) {
            elements.toggleGridBtn.classList.remove('bg-blue-100', 'text-blue-700');
            elements.toggleGridBtn.classList.add('bg-blue-500', 'text-white');
        } else {
            elements.toggleGridBtn.classList.remove('bg-blue-500', 'text-white');
            elements.toggleGridBtn.classList.add('bg-blue-100', 'text-blue-700');
        }
    }

    // Zoom functionality
    function updateZoom(newZoom) {
        state.currentZoom = Math.min(Math.max(newZoom, MIN_ZOOM), MAX_ZOOM);
        elements.previewContainer.style.transform = `scale(${state.currentZoom})`;
        
        updateZoomDisplay();
        updateGridSize();
    }

    function updateZoomDisplay() {
        const percentage = Math.round(state.currentZoom * 100);
        elements.zoomLevel.textContent = `${percentage}%`;
        elements.zoomIndicator.textContent = `${percentage}%`;
    }

    function updateGridSize() {
        const gridSize = 0.5 / state.currentZoom;
        document.querySelector('.grid-overlay').style.backgroundSize = `${gridSize}cm ${gridSize}cm`;
    }

    function handleWheelZoom(event) {
        if (event.ctrlKey) {
            event.preventDefault();
            const delta = -event.deltaY * 0.001;
            updateZoom(state.currentZoom + delta);
        }
    }

    // Field selection
    function selectField(field) {
        if (state.selectedField) {
            state.selectedField.classList.remove('selected');
        }

        state.selectedField = field;
        field.classList.add('selected');

        elements.noFieldSelected.style.display = 'none';
        elements.fieldConfigForm.style.display = 'block';

        populateFieldForm(field);
    }

    function deselectField() {
        if (state.selectedField) {
            state.selectedField.classList.remove('selected');
            state.selectedField = null;
            elements.noFieldSelected.style.display = 'block';
            elements.fieldConfigForm.style.display = 'none';
        }
    }

    function populateFieldForm(field) {
        const fieldName = field.dataset.fieldName;
        const fieldChoices = @json(\App\Models\BadgeContent::FIELD_CHOICES);
        const fieldDisplayName = fieldChoices[fieldName] || fieldName;

        document.getElementById('field-name').value = fieldDisplayName;
        document.getElementById('font-size').value = parseInt(field.style.fontSize) || 12;
        document.getElementById('font-color').value = rgbToHex(field.style.color) || '#000000';
        document.getElementById('font-family').value = field.style.fontFamily.replace(/['"]/g, '') || 'Arial';
        document.getElementById('is-bold').checked = field.style.fontWeight === 'bold';
        document.getElementById('is-italic').checked = field.style.fontStyle === 'italic';
    }

    // Form handling
    function handleFormSubmit(event) {
        event.preventDefault();
        if (!state.selectedField) return;

        const fieldId = state.selectedField.dataset.fieldId;
        const formData = new FormData();
        
        formData.append('_token', document.querySelector('input[name="_token"]').value);
        formData.append('font_size', document.getElementById('font-size').value);
        formData.append('font_color', document.getElementById('font-color').value);
        formData.append('font_family', document.getElementById('font-family').value);
        formData.append('is_bold', document.getElementById('is-bold').checked ? 'true' : 'false');
        formData.append('is_italic', document.getElementById('is-italic').checked ? 'true' : 'false');

        // Apply changes immediately
        state.selectedField.style.fontSize = `${formData.get('font_size')}pt`;
        state.selectedField.style.color = formData.get('font_color');
        state.selectedField.style.fontFamily = formData.get('font_family');
        state.selectedField.style.fontWeight = formData.get('is_bold') === 'true' ? 'bold' : 'normal';
        state.selectedField.style.fontStyle = formData.get('is_italic') === 'true' ? 'italic' : 'normal';

        updateFieldStyle(fieldId, formData);
        showToast('Field updated successfully!');
    }

    // Reset functions
    function resetPreview() {
        if (!confirm('Are you sure you want to reset all fields to their default positions and styles?')) {
            return;
        }

        document.querySelectorAll('.badge-field').forEach(field => {
            const fieldId = field.dataset.fieldId;
            const defaultPosition = state.defaultPositions.get(fieldId);
            const defaultStyle = state.defaultStyles.get(fieldId);

            if (defaultPosition) {
                field.style.left = `${defaultPosition.x}cm`;
                field.style.top = `${defaultPosition.y}cm`;
            }

            if (defaultStyle) {
                Object.assign(field.style, defaultStyle);
            }

            field.style.transform = 'none';
            field.removeAttribute('data-x');
            field.removeAttribute('data-y');
        });

        updateZoom(1);
        elements.previewContainer.classList.remove('grid-active');
        elements.toggleGridBtn.classList.remove('bg-blue-500', 'text-white');
        elements.toggleGridBtn.classList.add('bg-blue-100', 'text-blue-700');
        elements.toggleGridBtn.setAttribute('aria-pressed', 'false');
        
        showToast('Preview reset successfully!');
    }

    function resetIndividualField() {
        if (!state.selectedField) return;
        if (!confirm('Are you sure you want to reset this field to its default settings?')) return;

        const fieldId = state.selectedField.dataset.fieldId;
        const defaultPosition = state.defaultPositions.get(fieldId);
        const defaultStyle = state.defaultStyles.get(fieldId);

        if (defaultPosition) {
            state.selectedField.style.left = `${defaultPosition.x}cm`;
            state.selectedField.style.top = `${defaultPosition.y}cm`;
        }

        if (defaultStyle) {
            Object.assign(state.selectedField.style, defaultStyle);
        }

        state.selectedField.style.transform = 'none';
        state.selectedField.removeAttribute('data-x');
        state.selectedField.removeAttribute('data-y');

        selectField(state.selectedField);
        showToast('Field reset successfully!');
    }

    // Save all changes
    async function saveAllChanges() {
        if (!confirm('Save all current changes?')) return;

        const saveButton = elements.saveAllChangesBtn;
        const saveText = saveButton.querySelector('.save-text');
        
        saveButton.disabled = true;
        saveText.textContent = 'Saving...';

        try {
            const updates = Array.from(document.querySelectorAll('.badge-field')).map(field => ({
                content_id: field.dataset.fieldId,
                position_x: parseFloat(field.style.left) || 0,
                position_y: parseFloat(field.style.top) || 0,
                font_size: parseInt(field.style.fontSize) || 12,
                font_color: rgbToHex(field.style.color) || '#000000',
                font_family: field.style.fontFamily.replace(/['"]/g, '') || 'Arial',
                is_bold: field.style.fontWeight === 'bold',
                is_italic: field.style.fontStyle === 'italic'
            }));

            const response = await fetch('{{ route('badge-templates.saveAllChanges') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                },
                body: JSON.stringify({ updates })
            });

            const result = await response.json();

            if (result.status === 'success') {
                showToast('All changes saved successfully!');
                // Update default states
                storeDefaultStates();
            } else {
                throw new Error(result.message || 'Failed to save changes');
            }
        } catch (error) {
            console.error('Save error:', error);
            showToast('Failed to save changes: ' + error.message, 'error');
        } finally {
            saveButton.disabled = false;
            saveText.textContent = 'Save All';
        }
    }

    // Utility functions
    function rgbToHex(rgb) {
        if (!rgb || rgb.indexOf('rgb') === -1) return rgb || '#000000';
        const rgbValues = rgb.match(/\d+/g);
        if (!rgbValues) return '#000000';
        return "#" + rgbValues.map(x => {
            const hex = parseInt(x).toString(16);
            return hex.length === 1 ? "0" + hex : hex;
        }).join('');
    }

    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        const bgColor = type === 'error' ? 'bg-red-100 border-red-400 text-red-700' : 'bg-green-100 border-green-400 text-green-700';
        const icon = type === 'error' ? 'fas fa-exclamation-circle' : 'fas fa-check-circle';
        
        toast.className = `fixed top-4 right-4 ${bgColor} px-6 py-4 rounded-xl z-50 shadow-lg border transform transition-transform duration-300 translate-x-full`;
        toast.innerHTML = `
            <div class="flex items-center">
                <i class="${icon} mr-2"></i>
                <span class="font-medium">${message}</span>
            </div>
        `;
        
        document.body.appendChild(toast);
        
        setTimeout(() => toast.classList.remove('translate-x-full'), 100);
        setTimeout(() => {
            toast.classList.add('translate-x-full');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    // AJAX functions
    async function updateFieldPosition(fieldId, x, y) {
        try {
            const formData = new FormData();
            formData.append('_token', document.querySelector('input[name="_token"]').value);
            formData.append('position_x', x.toFixed(2));
            formData.append('position_y', y.toFixed(2));

            const response = await fetch(`{{ url('badge-templates/content') }}/${fieldId}/update`, {
                method: 'POST',
                body: formData
            });

            if (!response.ok) throw new Error('Failed to update position');
        } catch (error) {
            console.error('Position update error:', error);
            showToast('Failed to update position', 'error');
        }
    }

    async function updateFieldStyle(fieldId, formData) {
        try {
            const response = await fetch(`{{ url('badge-templates/content') }}/${fieldId}/update`, {
                method: 'POST',
                body: formData
            });

            if (!response.ok) throw new Error('Failed to update style');
        } catch (error) {
            console.error('Style update error:', error);
            showToast('Failed to update style', 'error');
        }
    }
});
</script>
@endpush
@endsection