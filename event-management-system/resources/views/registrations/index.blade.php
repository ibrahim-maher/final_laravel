@extends('layouts.app')

@section('title', 'Registrations')

@section('content')
<div class="mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- CSRF Token for JavaScript -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Header with enhanced design -->
    <div class="mb-8">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-4xl font-bold text-gray-900">Registrations</h1>
                <p class="text-lg text-gray-600 mt-2">Manage and track event registrations</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('registrations.create') }}" 
                   class="inline-flex items-center px-4 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    New Registration
                </a>
                <a href="{{ route('registrations.public-register') }}" 
                   class="inline-flex items-center px-4 py-2.5 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-lg hover:from-green-700 hover:to-green-800 shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                    </svg>
                    Public Registration
                </a>
                <button onclick="showImportModal()" 
                        class="inline-flex items-center px-4 py-2.5 bg-gradient-to-r from-purple-600 to-purple-700 text-white rounded-lg hover:from-purple-700 hover:to-purple-800 shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                    </svg>
                    Import CSV
                </button>
                <button onclick="showBadgeTemplateManager()" 
                        class="inline-flex items-center px-4 py-2.5 bg-gradient-to-r from-orange-600 to-orange-700 text-white rounded-lg hover:from-orange-700 hover:to-orange-800 shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    Badge Manager
                </button>
            </div>
        </div>
    </div>

    <!-- Import Modal -->
    <div id="import-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
        <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4 transform transition-all duration-300 scale-95" id="import-modal-content">
            <h3 class="text-xl font-bold mb-4">Import Registrations</h3>
            <form action="{{ route('registrations.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-4">
                    <label for="event_id" class="block text-sm font-semibold text-gray-700 mb-2">Select Event</label>
                    <select name="event_id" id="event_id" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Choose an event...</option>
                        @foreach($events as $event)
                            <option value="{{ $event->id }}">{{ $event->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-4">
                    <label for="csv_file" class="block text-sm font-semibold text-gray-700 mb-2">CSV File</label>
                    <input type="file" name="csv_file" id="csv_file" accept=".csv" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <p class="text-xs text-gray-500 mt-1">CSV should contain: Name, Email, Ticket Type (optional)</p>
                </div>
                <div class="flex gap-3">
                    <button type="submit" 
                            class="flex-1 inline-flex justify-center items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                        </svg>
                        Import
                    </button>
                    <button type="button" onclick="hideImportModal()"
                            class="flex-1 inline-flex justify-center items-center px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Messages with enhanced styling -->
    @if(session('success'))
    <div class="bg-gradient-to-r from-green-50 to-green-100 border-l-4 border-green-500 text-green-700 px-6 py-4 rounded-lg mb-6 shadow-md">
        <div class="flex items-center">
            <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
            </svg>
            {{ session('success') }}
        </div>
    </div>
    @endif

    @if(session('error'))
    <div class="bg-gradient-to-r from-red-50 to-red-100 border-l-4 border-red-500 text-red-700 px-6 py-4 rounded-lg mb-6 shadow-md">
        <div class="flex items-center">
            <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
            </svg>
            {{ session('error') }}
        </div>
    </div>
    @endif

    <!-- Enhanced Filters -->
    <div class="bg-white shadow-xl rounded-2xl border border-gray-100 p-8 mb-8">
        <div class="flex items-center mb-6">
            <svg class="w-6 h-6 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.207A1 1 0 013 6.5V4z"></path>
            </svg>
            <h2 class="text-xl font-bold text-gray-800">Filters & Search</h2>
        </div>
        
        <form method="GET" action="{{ route('registrations.index') }}" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Search -->
                <div>
                    <label for="search" class="block text-sm font-semibold text-gray-700 mb-2">Search</label>
                    <div class="relative">
                        <input type="text" 
                               id="search" 
                               name="search" 
                               value="{{ request('search') }}"
                               placeholder="Name, email, or event..."
                               class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                        <svg class="absolute left-3 top-3.5 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                </div>

                <!-- Event Filter -->
                <div>
                    <label for="event_id" class="block text-sm font-semibold text-gray-700 mb-2">Event</label>
                    <select name="event_id" id="event_id" 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                        <option value="">All Events</option>
                        @foreach($events as $event)
                            <option value="{{ $event->id }}" {{ request('event_id') == $event->id ? 'selected' : '' }}>
                                {{ $event->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Filter -->
                <div>
                    <label for="status" class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                    <select name="status" id="status" 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                        <option value="">All Statuses</option>
                        <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>

                <!-- Date Range -->
                <div>
                    <label for="date_from" class="block text-sm font-semibold text-gray-700 mb-2">Date Range</label>
                    <div class="flex space-x-2">
                        <input type="date" 
                               name="date_from" 
                               value="{{ request('date_from') }}"
                               class="flex-1 px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                        <input type="date" 
                               name="date_to" 
                               value="{{ request('date_to') }}"
                               class="flex-1 px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3 pt-4 border-t border-gray-200">
                <button type="submit" 
                        class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    Apply Filters
                </button>
                <a href="{{ route('registrations.index') }}" 
                   class="inline-flex items-center px-6 py-3 bg-gray-500 text-white rounded-lg hover:bg-gray-600 shadow-lg hover:shadow-xl transition-all duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Clear All
                </a>
                <a href="{{ route('registrations.export', request()->query()) }}" 
                   class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-lg hover:from-green-700 hover:to-green-800 shadow-lg hover:shadow-xl transition-all duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Export All
                </a>
            </div>
        </form>
    </div>

    <!-- Hidden form for bulk actions -->
    <form id="bulk-action-form" method="POST" action="{{ route('registrations.bulk-action') }}" style="display: none;">
        @csrf
        <input type="hidden" name="action" id="bulk-action-input">
        <div id="bulk-registration-ids"></div>
    </form>

    <!-- Enhanced Bulk Actions -->
    <div id="bulk-actions" class="bg-gradient-to-r from-indigo-50 to-blue-50 border border-indigo-200 shadow-lg rounded-xl p-6 mb-8 transform transition-all duration-300" style="display: none;">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-indigo-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                </svg>
                <span id="selected-count" class="text-lg font-semibold text-indigo-800">0 selected</span>
            </div>
            <div class="flex flex-wrap gap-3">
                <button onclick="bulkAction('confirm')" 
                        class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 shadow-md hover:shadow-lg transition-all duration-200 transform hover:scale-105">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Confirm
                </button>
                <button onclick="bulkAction('cancel')" 
                        class="inline-flex items-center px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 shadow-md hover:shadow-lg transition-all duration-200 transform hover:scale-105">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    Cancel
                </button>
                <button onclick="exportSelected()" 
                        class="inline-flex items-center px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 shadow-md hover:shadow-lg transition-all duration-200 transform hover:scale-105">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Export Selected
                </button>
                <button onclick="printSelectedBadges()" 
                        class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 shadow-md hover:shadow-lg transition-all duration-200 transform hover:scale-105">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                    </svg>
                    Print Badges
                </button>
                <button onclick="generateQrCodes()" 
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 shadow-md hover:shadow-lg transition-all duration-200 transform hover:scale-105">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                    </svg>
                    Generate QR Codes
                </button>
                <button onclick="bulkAction('delete')" 
                        class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 shadow-md hover:shadow-lg transition-all duration-200 transform hover:scale-105">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    Delete
                </button>
            </div>
        </div>
    </div>

    <!-- Enhanced Registrations Table -->
    <div class="bg-white shadow-2xl rounded-2xl border border-gray-100 overflow-hidden">
        <div class="bg-gradient-to-r from-gray-50 to-gray-100 p-8 border-b border-gray-200">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">
                        Registrations ({{ $registrations->total() }})
                    </h2>
                    <p class="text-gray-600 mt-1">Manage and track all event registrations</p>
                </div>
                @if($registrations->count() > 0)
                <label class="flex items-center bg-white px-4 py-2 rounded-lg shadow-sm border border-gray-200 hover:bg-gray-50 transition-colors cursor-pointer">
                    <input type="checkbox" id="select-all" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <span class="ml-3 text-sm font-medium text-gray-700">Select All</span>
                </label>
                @endif
            </div>
        </div>

        @if($registrations->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                            <input type="checkbox" class="select-all-header h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                            Participant
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                            Event
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                            Ticket Type
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                            Badge Status
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                            Registered
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @foreach($registrations as $registration)
                    @php
                        $hasTemplate = $registration->ticketType && 
                                     App\Models\BadgeTemplate::where('ticket_id', $registration->ticket_type_id)->exists();
                        $hasQrCode = $registration->qrCode !== null;
                        $canPrint = $hasTemplate && $hasQrCode;
                    @endphp
                    <tr class="hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 transition-all duration-200">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <input type="checkbox" 
                                   class="registration-checkbox h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 focus:ring-offset-2"
                                   data-registration-id="{{ $registration->id }}"
                                   data-participant-name="{{ $registration->user->name ?? 'N/A' }}"
                                   data-event-name="{{ $registration->event->name ?? 'N/A' }}"
                                   data-event-date="{{ $registration->event->start_date ? $registration->event->start_date->format('M d, Y') : 'TBD' }}"
                                   data-ticket-type="{{ $registration->ticketType->name ?? 'N/A' }}"
                                   data-ticket-id="{{ $registration->ticket_type_id }}"
                                   data-status="{{ $registration->status }}"
                                   data-can-print="{{ $canPrint ? 'true' : 'false' }}">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-12 w-12">
                                    <div class="h-12 w-12 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow-lg">
                                        <span class="text-white font-bold text-sm">
                                            {{ strtoupper(substr($registration->user->name ?? 'U', 0, 2)) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-semibold text-gray-900">
                                        {{ $registration->user->name ?? 'N/A' }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ $registration->user->email ?? 'N/A' }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-semibold text-gray-900">
                                {{ $registration->event->name ?? 'N/A' }}
                            </div>
                            <div class="text-sm text-gray-500 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                {{ $registration->event->start_date ? $registration->event->start_date->format('M d, Y') : 'TBD' }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                {{ $registration->ticketType->name ?? 'N/A' }}
                            </div>
                            @if($registration->ticketType)
                            <div class="text-sm font-semibold text-green-600">
                                ${{ number_format($registration->ticketType->price, 2) }}
                            </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full shadow-sm
                                {{ $registration->status === 'confirmed' ? 'bg-green-100 text-green-800 border border-green-200' : '' }}
                                {{ $registration->status === 'pending' ? 'bg-yellow-100 text-yellow-800 border border-yellow-200' : '' }}
                                {{ $registration->status === 'cancelled' ? 'bg-red-100 text-red-800 border border-red-200' : '' }}">
                                {{ ucfirst($registration->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($canPrint)
                            <span class="inline-flex items-center px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                Ready
                            </span>
                            @elseif(!$hasTemplate)
                            <span class="inline-flex items-center px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                </svg>
                                No Template
                            </span>
                            @else
                            <span class="inline-flex items-center px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                                No QR
                            </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                {{ $registration->created_at->format('M d, Y') }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('registrations.show', $registration) }}" 
                                   class="inline-flex items-center justify-center w-8 h-8 text-blue-600 hover:text-blue-900 hover:bg-blue-100 rounded-full transition-all duration-200" title="View Details">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </a>
                                <a href="{{ route('registrations.edit', $registration) }}" 
                                   class="inline-flex items-center justify-center w-8 h-8 text-indigo-600 hover:text-indigo-900 hover:bg-indigo-100 rounded-full transition-all duration-200" title="Edit Registration">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </a>
                                <button onclick="previewBadge({{ $registration->id }})" 
                                        class="inline-flex items-center justify-center w-8 h-8 text-purple-600 hover:text-purple-900 hover:bg-purple-100 rounded-full transition-all duration-200 {{ !$hasTemplate ? 'opacity-50 cursor-not-allowed' : '' }}" 
                                        title="Preview Badge" {{ !$hasTemplate ? 'disabled' : '' }}>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                </button>
                                <button onclick="printSingleBadge({{ $registration->id }})" 
                                        class="inline-flex items-center justify-center w-8 h-8 text-green-600 hover:text-green-900 hover:bg-green-100 rounded-full transition-all duration-200 {{ !$canPrint ? 'opacity-50 cursor-not-allowed' : '' }}" 
                                        title="Print Badge" {{ !$canPrint ? 'disabled' : '' }}>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                    </svg>
                                </button>
                                <form action="{{ route('registrations.destroy', $registration) }}" 
                                      method="POST" 
                                      class="inline-block"
                                      onsubmit="return confirm('Are you sure you want to delete this registration?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="inline-flex items-center justify-center w-8 h-8 text-red-600 hover:text-red-900 hover:bg-red-100 rounded-full transition-all duration-200" 
                                            title="Delete Registration">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Enhanced Pagination -->
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
            {{ $registrations->appends(request()->query())->links() }}
        </div>
        @else
        <!-- Enhanced Empty State -->
        <div class="text-center py-16">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-gray-400 to-gray-500 rounded-full mb-4">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">No registrations found</h3>
            <p class="text-gray-600 mb-6">Get started by creating your first registration or adjust your filters.</p>
            <a href="{{ route('registrations.create') }}" 
               class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Create New Registration
            </a>
        </div>
        @endif
    </div>
</div>

@push('scripts')
<!-- Badge Printing System -->
<script src="{{ asset('js/badge-printing.js') }}"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAllMain = document.getElementById('select-all');
    const selectAllHeader = document.querySelector('.select-all-header');
    const registrationCheckboxes = document.querySelectorAll('.registration-checkbox');
    const bulkActionsDiv = document.getElementById('bulk-actions');
    const selectedCountSpan = document.getElementById('selected-count');

    // Get CSRF token
    function getCSRFToken() {
        return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    }

    // Select all functionality
    function handleSelectAll(checked) {
        registrationCheckboxes.forEach(checkbox => {
            checkbox.checked = checked;
        });
        updateBulkActions();
    }

    if (selectAllMain) {
        selectAllMain.addEventListener('change', function() {
            handleSelectAll(this.checked);
            if (selectAllHeader) selectAllHeader.checked = this.checked;
        });
    }

    if (selectAllHeader) {
        selectAllHeader.addEventListener('change', function() {
            handleSelectAll(this.checked);
            if (selectAllMain) selectAllMain.checked = this.checked;
        });
    }

    // Individual checkbox change
    registrationCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateBulkActions();
            
            // Update select all checkboxes
            const allChecked = Array.from(registrationCheckboxes).every(cb => cb.checked);
            const someChecked = Array.from(registrationCheckboxes).some(cb => cb.checked);
            
            if (selectAllMain) {
                selectAllMain.checked = allChecked;
                selectAllMain.indeterminate = someChecked && !allChecked;
            }
            if (selectAllHeader) {
                selectAllHeader.checked = allChecked;
                selectAllHeader.indeterminate = someChecked && !allChecked;
            }
        });
    });

    function updateBulkActions() {
        const checkedBoxes = document.querySelectorAll('.registration-checkbox:checked');
        const count = checkedBoxes.length;
        
        selectedCountSpan.textContent = `${count} selected`;
        
        if (count > 0) {
            bulkActionsDiv.style.display = 'block';
            bulkActionsDiv.classList.add('scale-100');
            bulkActionsDiv.classList.remove('scale-95');
        } else {
            bulkActionsDiv.style.display = 'none';
            bulkActionsDiv.classList.add('scale-95');
            bulkActionsDiv.classList.remove('scale-100');
        }
    }

    // Export selected registrations
    window.exportSelected = function() {
        const checkedBoxes = document.querySelectorAll('.registration-checkbox:checked');
        const registrationIds = Array.from(checkedBoxes).map(cb => cb.getAttribute('data-registration-id'));
        
        if (registrationIds.length === 0) {
            showNotification('Please select registrations to export', 'warning');
            return;
        }

        // Create form to submit registration IDs
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("registrations.export-selected") }}';
        form.style.display = 'none';
        
        // Add CSRF token
        const tokenInput = document.createElement('input');
        tokenInput.type = 'hidden';
        tokenInput.name = '_token';
        tokenInput.value = getCSRFToken();
        form.appendChild(tokenInput);
        
        // Add registration IDs
        registrationIds.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'registration_ids[]';
            input.value = id;
            form.appendChild(input);
        });
        
        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    };

    // Bulk actions - Fixed to use form submission
    window.bulkAction = function(action) {
        const checkedBoxes = document.querySelectorAll('.registration-checkbox:checked');
        const registrationIds = Array.from(checkedBoxes).map(cb => cb.getAttribute('data-registration-id'));
        
        if (registrationIds.length === 0) {
            showNotification('Please select registrations first', 'warning');
            return;
        }

        let actionText = action;
        if (action === 'confirm') actionText = 'confirm';
        if (action === 'cancel') actionText = 'cancel';
        if (action === 'delete') actionText = 'delete';

        if (!confirm(`Are you sure you want to ${actionText} ${registrationIds.length} registration(s)?`)) {
            return;
        }

        // Use form submission instead of fetch
        const form = document.getElementById('bulk-action-form');
        const actionInput = document.getElementById('bulk-action-input');
        const idsContainer = document.getElementById('bulk-registration-ids');
        
        // Set action
        actionInput.value = action;
        
        // Clear previous IDs
        idsContainer.innerHTML = '';
        
        // Add registration IDs as hidden inputs
        registrationIds.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'registration_ids[]';
            input.value = id;
            idsContainer.appendChild(input);
        });
        
        // Show loading
        showLoadingOverlay(`Processing ${actionText} action...`);
        
        // Submit form
        form.submit();
    };

    // Badge printing functions
    window.printSingleBadge = function(registrationId) {
        const checkbox = document.querySelector(`[data-registration-id="${registrationId}"]`);
        if (!checkbox) return;

        const canPrint = checkbox.getAttribute('data-can-print') === 'true';
        
        if (!canPrint) {
            showNotification('This registration cannot be printed. Missing template or QR code.', 'warning');
            return;
        }

        // Use the badge printing system
        if (typeof badgePrinting !== 'undefined') {
            badgePrinting.printSingleBadge(registrationId);
        } else {
            // Fallback
            window.open(`/registrations/${registrationId}/print-badge`, '_blank', 'width=900,height=700');
        }
    };

    window.previewBadge = function(registrationId) {
        const checkbox = document.querySelector(`[data-registration-id="${registrationId}"]`);
        if (!checkbox) return;

        const hasTemplate = checkbox.getAttribute('data-can-print') !== 'false';
        
        if (!hasTemplate) {
            showNotification('No badge template available for this registration.', 'warning');
            return;
        }

        // Use the badge printing system
        if (typeof badgePrinting !== 'undefined') {
            badgePrinting.previewBadge(registrationId);
        } else {
            showNotification('Badge preview system not loaded', 'error');
        }
    };

    // Badge bulk printing
    window.printSelectedBadges = function() {
        const checkedBoxes = document.querySelectorAll('.registration-checkbox:checked');
        
        if (checkedBoxes.length === 0) {
            showNotification('Please select registrations to print badges for', 'warning');
            return;
        }

        const printableBoxes = Array.from(checkedBoxes).filter(cb => cb.getAttribute('data-can-print') === 'true');
        
        if (printableBoxes.length === 0) {
            showNotification('None of the selected registrations can be printed', 'warning');
            return;
        }

        if (printableBoxes.length < checkedBoxes.length) {
            const proceed = confirm(`Only ${printableBoxes.length} out of ${checkedBoxes.length} registrations can be printed. Continue?`);
            if (!proceed) return;
        }

        // Use the badge printing system
        if (typeof badgePrinting !== 'undefined') {
            badgePrinting.printSelectedBadges();
        } else {
            // Fallback - bulk print form
            const registrationIds = printableBoxes.map(cb => cb.getAttribute('data-registration-id'));
            
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/registrations/bulk-print-badges';
            form.target = '_blank';
            form.style.display = 'none';
            
            const tokenInput = document.createElement('input');
            tokenInput.type = 'hidden';
            tokenInput.name = '_token';
            tokenInput.value = getCSRFToken();
            form.appendChild(tokenInput);
            
            registrationIds.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'registration_ids[]';
                input.value = id;
                form.appendChild(input);
            });
            
            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
        }
    };

    // Generate QR codes
    window.generateQrCodes = function() {
        const checkedBoxes = document.querySelectorAll('.registration-checkbox:checked');
        const registrationIds = Array.from(checkedBoxes).map(cb => cb.getAttribute('data-registration-id'));
        
        if (registrationIds.length === 0) {
            showNotification('Please select registrations to generate QR codes for', 'warning');
            return;
        }

        // Use the badge printing system
        if (typeof badgePrinting !== 'undefined') {
            badgePrinting.generateMissingQrCodes();
        } else {
            showNotification('Badge printing system not loaded', 'error');
        }
    };

    // Badge template manager
    window.showBadgeTemplateManager = function() {
        if (typeof badgePrinting !== 'undefined') {
            badgePrinting.showBadgeTemplateManager();
        } else {
            showNotification('Badge printing system not loaded', 'error');
        }
    };

    // Modal functions
    window.showImportModal = function() {
        const modal = document.getElementById('import-modal');
        const content = document.getElementById('import-modal-content');
        modal.classList.remove('hidden');
        setTimeout(() => {
            content.classList.remove('scale-95');
            content.classList.add('scale-100');
        }, 10);
    };

    window.hideImportModal = function() {
        const modal = document.getElementById('import-modal');
        const content = document.getElementById('import-modal-content');
        content.classList.add('scale-95');
        content.classList.remove('scale-100');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    };

    // Close modal when clicking outside
    document.getElementById('import-modal').addEventListener('click', function(e) {
        if (e.target === this) {
            hideImportModal();
        }
    });

    // Utility functions
    function showNotification(message, type = 'info') {
        const colors = {
            info: 'bg-blue-500',
            success: 'bg-green-500',
            warning: 'bg-yellow-500',
            error: 'bg-red-500'
        };

        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 ${colors[type]} text-white px-6 py-3 rounded-lg shadow-lg transform transition-all duration-300 z-50 translate-x-full`;
        notification.innerHTML = `
            <div class="flex items-center">
                <span>${message}</span>
                <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Animate in
        setTimeout(() => {
            notification.classList.remove('translate-x-full');
            notification.classList.add('translate-x-0');
        }, 10);
        
        // Auto remove
        setTimeout(() => {
            notification.classList.add('translate-x-full');
            setTimeout(() => notification.remove(), 300);
        }, 5000);
    }

    function showLoadingOverlay(message) {
        const overlay = document.createElement('div');
        overlay.id = 'loading-overlay';
        overlay.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
        overlay.innerHTML = `
            <div class="bg-white rounded-lg p-6 flex items-center">
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                ${message}
            </div>
        `;
        document.body.appendChild(overlay);
    }

    // Remove loading overlay on page load
    window.addEventListener('load', function() {
        const overlay = document.getElementById('loading-overlay');
        if (overlay) {
            overlay.remove();
        }
    });

    console.log('Registration management system initialized with badge printing');
});
</script>
@endpush

@push('styles')
<style>
    @media print {
        .no-print {
            display: none !important;
        }
    }
    
    /* Custom checkbox styling */
    input[type="checkbox"]:indeterminate {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 16 16'%3e%3cpath stroke='white' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M4 8h8'/%3e%3c/svg%3e");
        border-color: #3b82f6;
        background-color: #3b82f6;
    }
    
    /* Enhanced hover effects */
    .hover-scale:hover {
        transform: scale(1.02);
    }
    
    /* Smooth transitions */
    .transition-all {
        transition-property: all;
        transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
        transition-duration: 200ms;
    }

    /* Loading animation */
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
    
    .animate-spin {
        animation: spin 1s linear infinite;
    }

    /* Badge status indicators */
    .badge-status-ready {
        @apply bg-green-100 text-green-800 border-green-200;
    }
    
    .badge-status-no-template {
        @apply bg-red-100 text-red-800 border-red-200;
    }
    
    .badge-status-no-qr {
        @apply bg-yellow-100 text-yellow-800 border-yellow-200;
    }
</style>
@endpush
@endsection@extends('layouts.app')

@section('title', 'Registrations')

@section('content')
<div class="mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- CSRF Token for JavaScript -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Header with enhanced design -->
    <div class="mb-8">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-4xl font-bold text-gray-900">Registrations</h1>
                <p class="text-lg text-gray-600 mt-2">Manage and track event registrations</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('registrations.create') }}" 
                   class="inline-flex items-center px-4 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    New Registration
                </a>
                <a href="{{ route('registrations.public-register') }}" 
                   class="inline-flex items-center px-4 py-2.5 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-lg hover:from-green-700 hover:to-green-800 shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                    </svg>
                    Public Registration
                </a>
                <button onclick="showImportModal()" 
                        class="inline-flex items-center px-4 py-2.5 bg-gradient-to-r from-purple-600 to-purple-700 text-white rounded-lg hover:from-purple-700 hover:to-purple-800 shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                    </svg>
                    Import CSV
                </button>
                <button onclick="showBadgeTemplateManager()" 
                        class="inline-flex items-center px-4 py-2.5 bg-gradient-to-r from-orange-600 to-orange-700 text-white rounded-lg hover:from-orange-700 hover:to-orange-800 shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    Badge Manager
                </button>
            </div>
        </div>
    </div>

    <!-- Import Modal -->
    <div id="import-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
        <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4 transform transition-all duration-300 scale-95" id="import-modal-content">
            <h3 class="text-xl font-bold mb-4">Import Registrations</h3>
            <form action="{{ route('registrations.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-4">
                    <label for="event_id" class="block text-sm font-semibold text-gray-700 mb-2">Select Event</label>
                    <select name="event_id" id="event_id" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Choose an event...</option>
                        @foreach($events as $event)
                            <option value="{{ $event->id }}">{{ $event->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-4">
                    <label for="csv_file" class="block text-sm font-semibold text-gray-700 mb-2">CSV File</label>
                    <input type="file" name="csv_file" id="csv_file" accept=".csv" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <p class="text-xs text-gray-500 mt-1">CSV should contain: Name, Email, Ticket Type (optional)</p>
                </div>
                <div class="flex gap-3">
                    <button type="submit" 
                            class="flex-1 inline-flex justify-center items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                        </svg>
                        Import
                    </button>
                    <button type="button" onclick="hideImportModal()"
                            class="flex-1 inline-flex justify-center items-center px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Messages with enhanced styling -->
    @if(session('success'))
    <div class="bg-gradient-to-r from-green-50 to-green-100 border-l-4 border-green-500 text-green-700 px-6 py-4 rounded-lg mb-6 shadow-md">
        <div class="flex items-center">
            <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
            </svg>
            {{ session('success') }}
        </div>
    </div>
    @endif

    @if(session('error'))
    <div class="bg-gradient-to-r from-red-50 to-red-100 border-l-4 border-red-500 text-red-700 px-6 py-4 rounded-lg mb-6 shadow-md">
        <div class="flex items-center">
            <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
            </svg>
            {{ session('error') }}
        </div>
    </div>
    @endif

    <!-- Enhanced Filters -->
    <div class="bg-white shadow-xl rounded-2xl border border-gray-100 p-8 mb-8">
        <div class="flex items-center mb-6">
            <svg class="w-6 h-6 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.207A1 1 0 013 6.5V4z"></path>
            </svg>
            <h2 class="text-xl font-bold text-gray-800">Filters & Search</h2>
        </div>
        
        <form method="GET" action="{{ route('registrations.index') }}" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Search -->
                <div>
                    <label for="search" class="block text-sm font-semibold text-gray-700 mb-2">Search</label>
                    <div class="relative">
                        <input type="text" 
                               id="search" 
                               name="search" 
                               value="{{ request('search') }}"
                               placeholder="Name, email, or event..."
                               class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                        <svg class="absolute left-3 top-3.5 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                </div>

                <!-- Event Filter -->
                <div>
                    <label for="event_id" class="block text-sm font-semibold text-gray-700 mb-2">Event</label>
                    <select name="event_id" id="event_id" 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                        <option value="">All Events</option>
                        @foreach($events as $event)
                            <option value="{{ $event->id }}" {{ request('event_id') == $event->id ? 'selected' : '' }}>
                                {{ $event->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Filter -->
                <div>
                    <label for="status" class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                    <select name="status" id="status" 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                        <option value="">All Statuses</option>
                        <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>

                <!-- Date Range -->
                <div>
                    <label for="date_from" class="block text-sm font-semibold text-gray-700 mb-2">Date Range</label>
                    <div class="flex space-x-2">
                        <input type="date" 
                               name="date_from" 
                               value="{{ request('date_from') }}"
                               class="flex-1 px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                        <input type="date" 
                               name="date_to" 
                               value="{{ request('date_to') }}"
                               class="flex-1 px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3 pt-4 border-t border-gray-200">
                <button type="submit" 
                        class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    Apply Filters
                </button>
                <a href="{{ route('registrations.index') }}" 
                   class="inline-flex items-center px-6 py-3 bg-gray-500 text-white rounded-lg hover:bg-gray-600 shadow-lg hover:shadow-xl transition-all duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Clear All
                </a>
                <a href="{{ route('registrations.export', request()->query()) }}" 
                   class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-lg hover:from-green-700 hover:to-green-800 shadow-lg hover:shadow-xl transition-all duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Export All
                </a>
            </div>
        </form>
    </div>

    <!-- Hidden form for bulk actions -->
    <form id="bulk-action-form" method="POST" action="{{ route('registrations.bulk-action') }}" style="display: none;">
        @csrf
        <input type="hidden" name="action" id="bulk-action-input">
        <div id="bulk-registration-ids"></div>
    </form>

    <!-- Enhanced Bulk Actions -->
    <div id="bulk-actions" class="bg-gradient-to-r from-indigo-50 to-blue-50 border border-indigo-200 shadow-lg rounded-xl p-6 mb-8 transform transition-all duration-300" style="display: none;">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-indigo-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                </svg>
                <span id="selected-count" class="text-lg font-semibold text-indigo-800">0 selected</span>
            </div>
            <div class="flex flex-wrap gap-3">
                <button onclick="bulkAction('confirm')" 
                        class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 shadow-md hover:shadow-lg transition-all duration-200 transform hover:scale-105">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Confirm
                </button>
                <button onclick="bulkAction('cancel')" 
                        class="inline-flex items-center px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 shadow-md hover:shadow-lg transition-all duration-200 transform hover:scale-105">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    Cancel
                </button>
                <button onclick="exportSelected()" 
                        class="inline-flex items-center px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 shadow-md hover:shadow-lg transition-all duration-200 transform hover:scale-105">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Export Selected
                </button>
                <button onclick="printSelectedBadges()" 
                        class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 shadow-md hover:shadow-lg transition-all duration-200 transform hover:scale-105">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                    </svg>
                    Print Badges
                </button>
                <button onclick="generateQrCodes()" 
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 shadow-md hover:shadow-lg transition-all duration-200 transform hover:scale-105">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                    </svg>
                    Generate QR Codes
                </button>
                <button onclick="bulkAction('delete')" 
                        class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 shadow-md hover:shadow-lg transition-all duration-200 transform hover:scale-105">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    Delete
                </button>
            </div>
        </div>
    </div>

    <!-- Enhanced Registrations Table -->
    <div class="bg-white shadow-2xl rounded-2xl border border-gray-100 overflow-hidden">
        <div class="bg-gradient-to-r from-gray-50 to-gray-100 p-8 border-b border-gray-200">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">
                        Registrations ({{ $registrations->total() }})
                    </h2>
                    <p class="text-gray-600 mt-1">Manage and track all event registrations</p>
                </div>
                @if($registrations->count() > 0)
                <label class="flex items-center bg-white px-4 py-2 rounded-lg shadow-sm border border-gray-200 hover:bg-gray-50 transition-colors cursor-pointer">
                    <input type="checkbox" id="select-all" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <span class="ml-3 text-sm font-medium text-gray-700">Select All</span>
                </label>
                @endif
            </div>
        </div>

        @if($registrations->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                            <input type="checkbox" class="select-all-header h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                            Participant
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                            Event
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                            Ticket Type
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                            Badge Status
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                            Registered
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @foreach($registrations as $registration)
                    @php
                        $hasTemplate = $registration->ticketType && 
                                     App\Models\BadgeTemplate::where('ticket_id', $registration->ticket_type_id)->exists();
                        $hasQrCode = $registration->qrCode !== null;
                        $canPrint = $hasTemplate && $hasQrCode;
                    @endphp
                    <tr class="hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 transition-all duration-200">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <input type="checkbox" 
                                   class="registration-checkbox h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 focus:ring-offset-2"
                                   data-registration-id="{{ $registration->id }}"
                                   data-participant-name="{{ $registration->user->name ?? 'N/A' }}"
                                   data-event-name="{{ $registration->event->name ?? 'N/A' }}"
                                   data-event-date="{{ $registration->event->start_date ? $registration->event->start_date->format('M d, Y') : 'TBD' }}"
                                   data-ticket-type="{{ $registration->ticketType->name ?? 'N/A' }}"
                                   data-ticket-id="{{ $registration->ticket_type_id }}"
                                   data-status="{{ $registration->status }}"
                                   data-can-print="{{ $canPrint ? 'true' : 'false' }}">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-12 w-12">
                                    <div class="h-12 w-12 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow-lg">
                                        <span class="text-white font-bold text-sm">
                                            {{ strtoupper(substr($registration->user->name ?? 'U', 0, 2)) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-semibold text-gray-900">
                                        {{ $registration->user->name ?? 'N/A' }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ $registration->user->email ?? 'N/A' }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-semibold text-gray-900">
                                {{ $registration->event->name ?? 'N/A' }}
                            </div>
                            <div class="text-sm text-gray-500 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                {{ $registration->event->start_date ? $registration->event->start_date->format('M d, Y') : 'TBD' }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                {{ $registration->ticketType->name ?? 'N/A' }}
                            </div>
                            @if($registration->ticketType)
                            <div class="text-sm font-semibold text-green-600">
                                ${{ number_format($registration->ticketType->price, 2) }}
                            </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full shadow-sm
                                {{ $registration->status === 'confirmed' ? 'bg-green-100 text-green-800 border border-green-200' : '' }}
                                {{ $registration->status === 'pending' ? 'bg-yellow-100 text-yellow-800 border border-yellow-200' : '' }}
                                {{ $registration->status === 'cancelled' ? 'bg-red-100 text-red-800 border border-red-200' : '' }}">
                                {{ ucfirst($registration->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($canPrint)
                            <span class="inline-flex items-center px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                Ready
                            </span>
                            @elseif(!$hasTemplate)
                            <span class="inline-flex items-center px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                </svg>
                                No Template
                            </span>
                            @else
                            <span class="inline-flex items-center px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                                No QR
                            </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                {{ $registration->created_at->format('M d, Y') }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('registrations.show', $registration) }}" 
                                   class="inline-flex items-center justify-center w-8 h-8 text-blue-600 hover:text-blue-900 hover:bg-blue-100 rounded-full transition-all duration-200" title="View Details">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </a>
                                <a href="{{ route('registrations.edit', $registration) }}" 
                                   class="inline-flex items-center justify-center w-8 h-8 text-indigo-600 hover:text-indigo-900 hover:bg-indigo-100 rounded-full transition-all duration-200" title="Edit Registration">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </a>
                                <button onclick="previewBadge({{ $registration->id }})" 
                                        class="inline-flex items-center justify-center w-8 h-8 text-purple-600 hover:text-purple-900 hover:bg-purple-100 rounded-full transition-all duration-200 {{ !$hasTemplate ? 'opacity-50 cursor-not-allowed' : '' }}" 
                                        title="Preview Badge" {{ !$hasTemplate ? 'disabled' : '' }}>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                </button>
                                <button onclick="printSingleBadge({{ $registration->id }})" 
                                        class="inline-flex items-center justify-center w-8 h-8 text-green-600 hover:text-green-900 hover:bg-green-100 rounded-full transition-all duration-200 {{ !$canPrint ? 'opacity-50 cursor-not-allowed' : '' }}" 
                                        title="Print Badge" {{ !$canPrint ? 'disabled' : '' }}>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                    </svg>
                                </button>
                                <form action="{{ route('registrations.destroy', $registration) }}" 
                                      method="POST" 
                                      class="inline-block"
                                      onsubmit="return confirm('Are you sure you want to delete this registration?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="inline-flex items-center justify-center w-8 h-8 text-red-600 hover:text-red-900 hover:bg-red-100 rounded-full transition-all duration-200" 
                                            title="Delete Registration">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Enhanced Pagination -->
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
            {{ $registrations->appends(request()->query())->links() }}
        </div>
        @else
        <!-- Enhanced Empty State -->
        <div class="text-center py-16">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-gray-400 to-gray-500 rounded-full mb-4">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">No registrations found</h3>
            <p class="text-gray-600 mb-6">Get started by creating your first registration or adjust your filters.</p>
            <a href="{{ route('registrations.create') }}" 
               class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Create New Registration
            </a>
        </div>
        @endif
    </div>
</div>

@push('scripts')
<!-- Badge Printing System -->
<script src="{{ asset('js/badge-printing.js') }}"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAllMain = document.getElementById('select-all');
    const selectAllHeader = document.querySelector('.select-all-header');
    const registrationCheckboxes = document.querySelectorAll('.registration-checkbox');
    const bulkActionsDiv = document.getElementById('bulk-actions');
    const selectedCountSpan = document.getElementById('selected-count');

    // Get CSRF token
    function getCSRFToken() {
        return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    }

    // Select all functionality
    function handleSelectAll(checked) {
        registrationCheckboxes.forEach(checkbox => {
            checkbox.checked = checked;
        });
        updateBulkActions();
    }

    if (selectAllMain) {
        selectAllMain.addEventListener('change', function() {
            handleSelectAll(this.checked);
            if (selectAllHeader) selectAllHeader.checked = this.checked;
        });
    }

    if (selectAllHeader) {
        selectAllHeader.addEventListener('change', function() {
            handleSelectAll(this.checked);
            if (selectAllMain) selectAllMain.checked = this.checked;
        });
    }

    // Individual checkbox change
    registrationCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateBulkActions();
            
            // Update select all checkboxes
            const allChecked = Array.from(registrationCheckboxes).every(cb => cb.checked);
            const someChecked = Array.from(registrationCheckboxes).some(cb => cb.checked);
            
            if (selectAllMain) {
                selectAllMain.checked = allChecked;
                selectAllMain.indeterminate = someChecked && !allChecked;
            }
            if (selectAllHeader) {
                selectAllHeader.checked = allChecked;
                selectAllHeader.indeterminate = someChecked && !allChecked;
            }
        });
    });

    function updateBulkActions() {
        const checkedBoxes = document.querySelectorAll('.registration-checkbox:checked');
        const count = checkedBoxes.length;
        
        selectedCountSpan.textContent = `${count} selected`;
        
        if (count > 0) {
            bulkActionsDiv.style.display = 'block';
            bulkActionsDiv.classList.add('scale-100');
            bulkActionsDiv.classList.remove('scale-95');
        } else {
            bulkActionsDiv.style.display = 'none';
            bulkActionsDiv.classList.add('scale-95');
            bulkActionsDiv.classList.remove('scale-100');
        }
    }

    // Export selected registrations
    window.exportSelected = function() {
        const checkedBoxes = document.querySelectorAll('.registration-checkbox:checked');
        const registrationIds = Array.from(checkedBoxes).map(cb => cb.getAttribute('data-registration-id'));
        
        if (registrationIds.length === 0) {
            showNotification('Please select registrations to export', 'warning');
            return;
        }

        // Create form to submit registration IDs
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("registrations.export-selected") }}';
        form.style.display = 'none';
        
        // Add CSRF token
        const tokenInput = document.createElement('input');
        tokenInput.type = 'hidden';
        tokenInput.name = '_token';
        tokenInput.value = getCSRFToken();
        form.appendChild(tokenInput);
        
        // Add registration IDs
        registrationIds.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'registration_ids[]';
            input.value = id;
            form.appendChild(input);
        });
        
        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    };

    // Bulk actions - Fixed to use form submission
    window.bulkAction = function(action) {
        const checkedBoxes = document.querySelectorAll('.registration-checkbox:checked');
        const registrationIds = Array.from(checkedBoxes).map(cb => cb.getAttribute('data-registration-id'));
        
        if (registrationIds.length === 0) {
            showNotification('Please select registrations first', 'warning');
            return;
        }

        let actionText = action;
        if (action === 'confirm') actionText = 'confirm';
        if (action === 'cancel') actionText = 'cancel';
        if (action === 'delete') actionText = 'delete';

        if (!confirm(`Are you sure you want to ${actionText} ${registrationIds.length} registration(s)?`)) {
            return;
        }

        // Use form submission instead of fetch
        const form = document.getElementById('bulk-action-form');
        const actionInput = document.getElementById('bulk-action-input');
        const idsContainer = document.getElementById('bulk-registration-ids');
        
        // Set action
        actionInput.value = action;
        
        // Clear previous IDs
        idsContainer.innerHTML = '';
        
        // Add registration IDs as hidden inputs
        registrationIds.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'registration_ids[]';
            input.value = id;
            idsContainer.appendChild(input);
        });
        
        // Show loading
        showLoadingOverlay(`Processing ${actionText} action...`);
        
        // Submit form
        form.submit();
    };

    // Badge printing functions
    window.printSingleBadge = function(registrationId) {
        const checkbox = document.querySelector(`[data-registration-id="${registrationId}"]`);
        if (!checkbox) return;

        const canPrint = checkbox.getAttribute('data-can-print') === 'true';
        
        if (!canPrint) {
            showNotification('This registration cannot be printed. Missing template or QR code.', 'warning');
            return;
        }

        // Use the badge printing system
        if (typeof badgePrinting !== 'undefined') {
            badgePrinting.printSingleBadge(registrationId);
        } else {
            // Fallback
            window.open(`/registrations/${registrationId}/print-badge`, '_blank', 'width=900,height=700');
        }
    };

    window.previewBadge = function(registrationId) {
        const checkbox = document.querySelector(`[data-registration-id="${registrationId}"]`);
        if (!checkbox) return;

        const hasTemplate = checkbox.getAttribute('data-can-print') !== 'false';
        
        if (!hasTemplate) {
            showNotification('No badge template available for this registration.', 'warning');
            return;
        }

        // Use the badge printing system
        if (typeof badgePrinting !== 'undefined') {
            badgePrinting.previewBadge(registrationId);
        } else {
            showNotification('Badge preview system not loaded', 'error');
        }
    };

    // Badge bulk printing
    window.printSelectedBadges = function() {
        const checkedBoxes = document.querySelectorAll('.registration-checkbox:checked');
        
        if (checkedBoxes.length === 0) {
            showNotification('Please select registrations to print badges for', 'warning');
            return;
        }

        const printableBoxes = Array.from(checkedBoxes).filter(cb => cb.getAttribute('data-can-print') === 'true');
        
        if (printableBoxes.length === 0) {
            showNotification('None of the selected registrations can be printed', 'warning');
            return;
        }

        if (printableBoxes.length < checkedBoxes.length) {
            const proceed = confirm(`Only ${printableBoxes.length} out of ${checkedBoxes.length} registrations can be printed. Continue?`);
            if (!proceed) return;
        }

        // Use the badge printing system
        if (typeof badgePrinting !== 'undefined') {
            badgePrinting.printSelectedBadges();
        } else {
            // Fallback - bulk print form
            const registrationIds = printableBoxes.map(cb => cb.getAttribute('data-registration-id'));
            
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/registrations/bulk-print-badges';
            form.target = '_blank';
            form.style.display = 'none';
            
            const tokenInput = document.createElement('input');
            tokenInput.type = 'hidden';
            tokenInput.name = '_token';
            tokenInput.value = getCSRFToken();
            form.appendChild(tokenInput);
            
            registrationIds.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'registration_ids[]';
                input.value = id;
                form.appendChild(input);
            });
            
            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
        }
    };

    // Generate QR codes
    window.generateQrCodes = function() {
        const checkedBoxes = document.querySelectorAll('.registration-checkbox:checked');
        const registrationIds = Array.from(checkedBoxes).map(cb => cb.getAttribute('data-registration-id'));
        
        if (registrationIds.length === 0) {
            showNotification('Please select registrations to generate QR codes for', 'warning');
            return;
        }

        // Use the badge printing system
        if (typeof badgePrinting !== 'undefined') {
            badgePrinting.generateMissingQrCodes();
        } else {
            showNotification('Badge printing system not loaded', 'error');
        }
    };

    // Badge template manager
    window.showBadgeTemplateManager = function() {
        if (typeof badgePrinting !== 'undefined') {
            badgePrinting.showBadgeTemplateManager();
        } else {
            showNotification('Badge printing system not loaded', 'error');
        }
    };

    // Modal functions
    window.showImportModal = function() {
        const modal = document.getElementById('import-modal');
        const content = document.getElementById('import-modal-content');
        modal.classList.remove('hidden');
        setTimeout(() => {
            content.classList.remove('scale-95');
            content.classList.add('scale-100');
        }, 10);
    };

    window.hideImportModal = function() {
        const modal = document.getElementById('import-modal');
        const content = document.getElementById('import-modal-content');
        content.classList.add('scale-95');
        content.classList.remove('scale-100');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    };

    // Close modal when clicking outside
    document.getElementById('import-modal').addEventListener('click', function(e) {
        if (e.target === this) {
            hideImportModal();
        }
    });

    // Utility functions
    function showNotification(message, type = 'info') {
        const colors = {
            info: 'bg-blue-500',
            success: 'bg-green-500',
            warning: 'bg-yellow-500',
            error: 'bg-red-500'
        };

        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 ${colors[type]} text-white px-6 py-3 rounded-lg shadow-lg transform transition-all duration-300 z-50 translate-x-full`;
        notification.innerHTML = `
            <div class="flex items-center">
                <span>${message}</span>
                <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Animate in
        setTimeout(() => {
            notification.classList.remove('translate-x-full');
            notification.classList.add('translate-x-0');
        }, 10);
        
        // Auto remove
        setTimeout(() => {
            notification.classList.add('translate-x-full');
            setTimeout(() => notification.remove(), 300);
        }, 5000);
    }

    function showLoadingOverlay(message) {
        const overlay = document.createElement('div');
        overlay.id = 'loading-overlay';
        overlay.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
        overlay.innerHTML = `
            <div class="bg-white rounded-lg p-6 flex items-center">
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                ${message}
            </div>
        `;
        document.body.appendChild(overlay);
    }

    // Remove loading overlay on page load
    window.addEventListener('load', function() {
        const overlay = document.getElementById('loading-overlay');
        if (overlay) {
            overlay.remove();
        }
    });

    console.log('Registration management system initialized with badge printing');
});
</script>
@endpush

@push('styles')
<style>
    @media print {
        .no-print {
            display: none !important;
        }
    }
    
    /* Custom checkbox styling */
    input[type="checkbox"]:indeterminate {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 16 16'%3e%3cpath stroke='white' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M4 8h8'/%3e%3c/svg%3e");
        border-color: #3b82f6;
        background-color: #3b82f6;
    }
    
    /* Enhanced hover effects */
    .hover-scale:hover {
        transform: scale(1.02);
    }
    
    /* Smooth transitions */
    .transition-all {
        transition-property: all;
        transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
        transition-duration: 200ms;
    }

    /* Loading animation */
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
    
    .animate-spin {
        animation: spin 1s linear infinite;
    }

    /* Badge status indicators */
    .badge-status-ready {
        @apply bg-green-100 text-green-800 border-green-200;
    }
    
    .badge-status-no-template {
        @apply bg-red-100 text-red-800 border-red-200;
    }
    
    .badge-status-no-qr {
        @apply bg-yellow-100 text-yellow-800 border-yellow-200;
    }
</style>
@endpush
@endsection