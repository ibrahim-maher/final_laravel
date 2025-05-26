@extends('layouts.app')

@section('title', 'Visitor Logs')
@section('page-title', 'Visitor Management Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Dashboard Header -->
    <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-xl shadow-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold mb-2">Visitor Management Dashboard</h2>
                <p class="text-blue-100">Comprehensive visitor tracking and analytics</p>
            </div>
            <div class="flex items-center space-x-4">
                <div class="text-center">
                    <div class="text-2xl font-bold" id="live-total">{{ $stats['total_checkins'] + $stats['total_checkouts'] }}</div>
                    <div class="text-sm text-blue-100">Total Actions</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold" id="live-active">{{ $stats['active_visitors'] }}</div>
                    <div class="text-sm text-blue-100">Active Now</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Check-ins</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($stats['total_checkins']) }}</p>
                    <p class="text-sm text-green-600">
                        <i class="fas fa-arrow-up"></i>
                        {{ $stats['completion_rate'] }}% completion
                    </p>
                </div>
                <div class="bg-green-100 p-3 rounded-full">
                    <i class="fas fa-sign-in-alt text-2xl text-green-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Check-outs</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($stats['total_checkouts']) }}</p>
                    <p class="text-sm text-blue-600">
                        <i class="fas fa-arrow-up"></i>
                        Today's activity
                    </p>
                </div>
                <div class="bg-blue-100 p-3 rounded-full">
                    <i class="fas fa-sign-out-alt text-2xl text-blue-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-orange-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Active Visitors</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($stats['active_visitors']) }}</p>
                    <p class="text-sm text-orange-600">
                        <i class="fas fa-users"></i>
                        Currently present
                    </p>
                </div>
                <div class="bg-orange-100 p-3 rounded-full">
                    <i class="fas fa-users text-2xl text-orange-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Avg Duration</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($stats['average_duration']) }}m</p>
                    <p class="text-sm text-purple-600">
                        <i class="fas fa-clock"></i>
                        Per visit
                    </p>
                </div>
                <div class="bg-purple-100 p-3 rounded-full">
                    <i class="fas fa-stopwatch text-2xl text-purple-600"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions & Analytics Preview -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Quick Actions -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">
                <i class="fas fa-bolt mr-2 text-yellow-600"></i>
                Quick Actions
            </h3>
            <div class="space-y-3">
                <a href="{{ route('checkin.index') }}" class="flex items-center p-3 bg-green-50 hover:bg-green-100 rounded-lg transition-colors">
                    <i class="fas fa-sign-in-alt text-green-600 mr-3"></i>
                    <span class="font-medium">Check-in System</span>
                </a>
                <a href="{{ route('checkin.checkout') }}" class="flex items-center p-3 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors">
                    <i class="fas fa-sign-out-alt text-blue-600 mr-3"></i>
                    <span class="font-medium">Check-out System</span>
                </a>
                <a href="{{ route('checkin.scan') }}" class="flex items-center p-3 bg-purple-50 hover:bg-purple-100 rounded-lg transition-colors">
                    <i class="fas fa-print text-purple-600 mr-3"></i>
                    <span class="font-medium">Scan & Print</span>
                </a>
                <button onclick="exportLogs()" class="flex items-center p-3 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors w-full text-left">
                    <i class="fas fa-download text-gray-600 mr-3"></i>
                    <span class="font-medium">Export Data</span>
                </button>
            </div>
        </div>

        <!-- Real-time Activity -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-900">
                    <i class="fas fa-broadcast-tower mr-2 text-red-600"></i>
                    Live Activity
                </h3>
                <span id="live-indicator" class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></span>
            </div>
            <div id="live-activity" class="space-y-3 max-h-60 overflow-y-auto">
                @if(isset($recentActivity) && $recentActivity->count() > 0)
                    @foreach($recentActivity->take(5) as $log)
                    <div class="flex items-center space-x-3 p-2 bg-gray-50 rounded-lg">
                        <div class="w-2 h-2 {{ $log->action === 'checkin' ? 'bg-green-500' : 'bg-blue-500' }} rounded-full"></div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ $log->registration->user->name }}</p>
                            <p class="text-xs text-gray-500">{{ ucfirst($log->action) }} • {{ $log->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                    @endforeach
                @else
                    @foreach($logs->take(5) as $log)
                    <div class="flex items-center space-x-3 p-2 bg-gray-50 rounded-lg">
                        <div class="w-2 h-2 {{ $log->action === 'checkin' ? 'bg-green-500' : 'bg-blue-500' }} rounded-full"></div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ $log->registration->user->name }}</p>
                            <p class="text-xs text-gray-500">{{ ucfirst($log->action) }} • {{ $log->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                    @endforeach
                @endif
            </div>
        </div>

        <!-- Today's Hourly Distribution -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">
                <i class="fas fa-chart-line mr-2 text-indigo-600"></i>
                Today's Activity
            </h3>
            <div class="mb-4">
                <canvas id="hourly-chart" height="150"></canvas>
            </div>
            <div class="grid grid-cols-2 gap-4 text-center">
                <div class="p-2 bg-green-50 rounded">
                    <div class="text-lg font-bold text-green-600" id="peak-hour">
                        {{ !empty($analytics['peak_hours']) ? array_keys($analytics['peak_hours'])[0] : '--' }}:00
                    </div>
                    <div class="text-xs text-gray-600">Peak Hour</div>
                </div>
                <div class="p-2 bg-blue-50 rounded">
                    <div class="text-lg font-bold text-blue-600">{{ $stats['completion_rate'] }}%</div>
                    <div class="text-xs text-gray-600">Completion Rate</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Advanced Filters -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-bold text-gray-900">
                <i class="fas fa-filter mr-2 text-blue-600"></i>
                Advanced Filters
            </h3>
            <div class="flex items-center space-x-2">
                <button onclick="resetFilters()" class="px-3 py-1 text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                    <i class="fas fa-undo mr-1"></i>Reset
                </button>
                <button onclick="saveFilterPreset()" class="px-3 py-1 text-sm bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition-colors">
                    <i class="fas fa-save mr-1"></i>Save Preset
                </button>
            </div>
        </div>
        
        <form method="GET" id="filter-form" class="grid grid-cols-1 md:grid-cols-6 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Name or email..."
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Event</label>
                <select name="event_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">All Events</option>
                    @foreach($events as $event)
                        <option value="{{ $event->id }}" {{ request('event_id') == $event->id ? 'selected' : '' }}>
                            {{ $event->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Action</label>
                <select name="action" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">All Actions</option>
                    <option value="checkin" {{ request('action') == 'checkin' ? 'selected' : '' }}>Check-in</option>
                    <option value="checkout" {{ request('action') == 'checkout' ? 'selected' : '' }}>Check-out</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Date From</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Date To</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            
            <div class="flex items-end">
                <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-search mr-2"></i>Apply
                </button>
            </div>
        </form>
        
        <!-- Additional Filters Toggle -->
        <div class="mt-4">
            <button onclick="toggleAdvancedFilters()" class="text-sm text-blue-600 hover:text-blue-800">
                <i class="fas fa-chevron-down mr-1" id="advanced-toggle-icon"></i>
                More Filters
            </button>
            <div id="advanced-filters" class="hidden mt-4 grid grid-cols-1 md:grid-cols-4 gap-4 p-4 bg-gray-50 rounded-lg">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Created By</label>
                    <select name="created_by" form="filter-form" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">All Users</option>
                        @foreach($creators as $creator)
                            <option value="{{ $creator->id }}" {{ request('created_by') == $creator->id ? 'selected' : '' }}>
                                {{ $creator->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Method</label>
                    <select name="qr_scanned" form="filter-form" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">All Methods</option>
                        <option value="1" {{ request('qr_scanned') === '1' ? 'selected' : '' }}>QR Code</option>
                        <option value="0" {{ request('qr_scanned') === '0' ? 'selected' : '' }}>Manual</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Min Duration (min)</label>
                    <input type="number" name="min_duration" value="{{ request('min_duration') }}" min="0"
                           placeholder="e.g., 30"
                           form="filter-form"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Max Duration (min)</label>
                    <input type="number" name="max_duration" value="{{ request('max_duration') }}" min="0"
                           placeholder="e.g., 480"
                           form="filter-form"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Actions Bar -->
    <div id="bulk-actions-bar" class="hidden bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <span class="text-sm text-blue-700">
                    <span id="selected-count">0</span> items selected
                </span>
                <button onclick="clearSelection()" class="text-sm text-blue-600 hover:text-blue-800">
                    Clear selection
                </button>
            </div>
            <div class="flex items-center space-x-2">
                <button onclick="bulkExport()" class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-download mr-2"></i>Export Selected
                </button>
                @if(auth()->user()->isAdmin())
                <button onclick="bulkDelete()" class="px-4 py-2 bg-red-600 text-white text-sm rounded-lg hover:bg-red-700 transition-colors">
                    <i class="fas fa-trash mr-2"></i>Delete Selected
                </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="flex items-center justify-between p-6 border-b border-gray-200">
            <div class="flex items-center space-x-4">
                <h3 class="text-lg font-bold text-gray-900">
                    <i class="fas fa-table mr-2 text-green-600"></i>
                    Visitor Logs
                </h3>
                <span class="px-3 py-1 bg-blue-100 text-blue-800 text-sm rounded-full">
                    {{ $logs->total() }} total records
                </span>
            </div>
            
            <div class="flex items-center space-x-3">
                <!-- View Toggle -->
                <div class="flex bg-gray-100 rounded-lg p-1">
                    <button onclick="setView('table')" id="table-view-btn" class="px-3 py-1 rounded-md text-sm font-medium bg-white text-gray-900 shadow-sm">
                        <i class="fas fa-table mr-1"></i>Table
                    </button>
                    <button onclick="setView('cards')" id="cards-view-btn" class="px-3 py-1 rounded-md text-sm font-medium text-gray-600 hover:text-gray-900">
                        <i class="fas fa-th-large mr-1"></i>Cards
                    </button>
                </div>
                
                <!-- Export Dropdown -->
                <div class="relative">
                    <button onclick="toggleExportDropdown()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                        <i class="fas fa-download mr-2"></i>Export
                        <i class="fas fa-chevron-down ml-1"></i>
                    </button>
                    <div id="export-dropdown" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-10">
                        <div class="py-1">
                            <button onclick="exportData('csv')" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-file-csv mr-2"></i>Export as CSV
                            </button>
                            <button onclick="exportData('excel')" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-file-excel mr-2"></i>Export as Excel
                            </button>
                            <button onclick="exportData('pdf')" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-file-pdf mr-2"></i>Export as PDF
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Auto-refresh Toggle -->
                <button onclick="toggleAutoRefresh()" id="auto-refresh-btn" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                    <i class="fas fa-sync-alt mr-2"></i>Auto-refresh: <span id="auto-refresh-status">Off</span>
                </button>
            </div>
        </div>
        
        <!-- Table View -->
        <div id="table-view" class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <input type="checkbox" id="select-all" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100"
                            onclick="sortBy('id')">
                            ID
                            <i class="fas fa-sort ml-1"></i>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100"
                            onclick="sortBy('user')">
                            User
                            <i class="fas fa-sort ml-1"></i>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Event
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100"
                            onclick="sortBy('action')">
                            Action
                            <i class="fas fa-sort ml-1"></i>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100"
                            onclick="sortBy('created_at')">
                            Timestamp
                            <i class="fas fa-sort ml-1"></i>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Duration
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Method
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Created By
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200" id="logs-tbody">
                    @forelse($logs as $log)
                    <tr class="hover:bg-gray-50 transition-colors" data-log-id="{{ $log->id }}">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <input type="checkbox" class="row-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500" value="{{ $log->id }}">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $log->id }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center mr-3">
                                    <i class="fas fa-user text-gray-600 text-xs"></i>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $log->registration->user->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $log->registration->user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $log->registration->event->name }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($log->action === 'checkin')
                                <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                                    <i class="fas fa-sign-in-alt mr-1"></i>Check-in
                                </span>
                            @else
                                <span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">
                                    <i class="fas fa-sign-out-alt mr-1"></i>Check-out
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <div class="text-sm text-gray-900">{{ $log->created_at->format('M d, Y H:i') }}</div>
                            <div class="text-xs text-gray-500">{{ $log->created_at->diffForHumans() }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            @if($log->duration_minutes)
                                <span class="text-purple-600 font-medium">{{ $log->duration_minutes }}m</span>
                            @else
                                <span class="text-gray-400">--</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs rounded-full {{ $log->qr_scanned ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                <i class="fas {{ $log->qr_scanned ? 'fa-qrcode' : 'fa-keyboard' }} mr-1"></i>
                                {{ $log->qr_scanned ? 'QR Code' : 'Manual' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $log->creator->name ?? 'System' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center space-x-2">
                                <button onclick="viewLogDetails({{ $log->id }})" class="text-blue-600 hover:text-blue-900" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button onclick="viewRegistration({{ $log->registration->id }})" class="text-green-600 hover:text-green-900" title="View Registration">
                                    <i class="fas fa-user"></i>
                                </button>
                                @if($log->admin_note)
                                <button onclick="viewNote({{ $log->id }})" class="text-yellow-600 hover:text-yellow-900" title="Has Note">
                                    <i class="fas fa-sticky-note"></i>
                                </button>
                                @endif
                                @if(auth()->user()->isAdmin())
                                <button onclick="deleteLog({{ $log->id }})" class="text-red-600 hover:text-red-900" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="px-6 py-12 text-center">
                            <div class="text-gray-500">
                                <i class="fas fa-inbox text-4xl mb-4"></i>
                                <p class="text-lg">No visitor logs found</p>
                                <p class="text-sm">Try adjusting your filters or check back later</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Cards View -->
        <div id="cards-view" class="hidden p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" id="cards-container">
                @foreach($logs as $log)
                <div class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-user text-gray-600"></i>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">{{ $log->registration->user->name }}</p>
                                <p class="text-sm text-gray-500">ID: {{ $log->id }}</p>
                            </div>
                        </div>
                        @if($log->action === 'checkin')
                            <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                                <i class="fas fa-sign-in-alt mr-1"></i>Check-in
                            </span>
                        @else
                            <span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">
                                <i class="fas fa-sign-out-alt mr-1"></i>Check-out
                            </span>
                        @endif
                    </div>
                    
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Event:</span>
                            <span class="text-gray-900">{{ Str::limit($log->registration->event->name, 20) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Time:</span>
                            <span class="text-gray-900">{{ $log->created_at->format('M d, H:i') }}</span>
                        </div>
                        @if($log->duration_minutes)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Duration:</span>
                            <span class="text-purple-600 font-medium">{{ $log->duration_minutes }}m</span>
                        </div>
                        @endif
                        <div class="flex justify-between">
                            <span class="text-gray-600">Method:</span>
                            <span class="text-gray-900">{{ $log->qr_scanned ? 'QR Code' : 'Manual' }}</span>
                        </div>
                    </div>
                    
                    <div class="mt-4 flex items-center justify-between">
                        <span class="text-xs text-gray-500">by {{ $log->creator->name ?? 'System' }}</span>
                        <button onclick="viewLogDetails({{ $log->id }})" class="text-blue-600 hover:text-blue-800 text-sm">
                            <i class="fas fa-eye mr-1"></i>Details
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        
        <!-- Pagination -->
        @if($logs->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $logs->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Log Details Modal -->
<div id="log-details-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-xl max-w-2xl w-full max-h-screen overflow-y-auto">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-bold text-gray-900">Log Details</h3>
                    <button onclick="closeLogModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <div id="log-modal-content">
                    <!-- Content will be populated here -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div id="confirm-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-xl max-w-md w-full">
            <div class="p-6">
                <div class="flex items-center justify-center mb-4">
                    <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                    </div>
                </div>
                <h3 class="text-lg font-bold text-gray-900 text-center mb-2" id="confirm-title">Confirm Action</h3>
                <p class="text-gray-600 text-center mb-6" id="confirm-message">Are you sure you want to perform this action?</p>
                
                <div class="flex items-center justify-center space-x-3">
                    <button onclick="closeConfirmModal()" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                        Cancel
                    </button>
                    <button id="confirm-button" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                        Confirm
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loading-overlay" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen">
        <div class="bg-white rounded-lg p-6 text-center">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
            <p class="text-gray-700">Processing...</p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let autoRefreshInterval = null;
let currentView = 'table';
let sortDirection = 'desc';
let sortField = 'created_at';
let hourlyChart = null;

document.addEventListener('DOMContentLoaded', function() {
    initializeHourlyChart();
    setupEventListeners();
    startLiveUpdates();
    loadFilterPresets();
});

// Chart Initialization
function initializeHourlyChart() {
    const ctx = document.getElementById('hourly-chart').getContext('2d');
    
    const hourlyData = @json($analytics['hourly_distribution'] ?? array_fill(0, 24, 0));
    const labels = Array.from({length: 24}, (_, i) => i + ':00');
    
    hourlyChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Check-ins',
                data: Object.values(hourlyData),
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                },
                x: {
                    display: false
                }
            }
        }
    });
}

// View Management
function setView(viewType) {
    currentView = viewType;
    
    if (viewType === 'table') {
        document.getElementById('table-view').classList.remove('hidden');
        document.getElementById('cards-view').classList.add('hidden');
        document.getElementById('table-view-btn').classList.add('bg-white', 'text-gray-900', 'shadow-sm');
        document.getElementById('table-view-btn').classList.remove('text-gray-600');
        document.getElementById('cards-view-btn').classList.remove('bg-white', 'text-gray-900', 'shadow-sm');
        document.getElementById('cards-view-btn').classList.add('text-gray-600');
    } else {
        document.getElementById('table-view').classList.add('hidden');
        document.getElementById('cards-view').classList.remove('hidden');
        document.getElementById('cards-view-btn').classList.add('bg-white', 'text-gray-900', 'shadow-sm');
        document.getElementById('cards-view-btn').classList.remove('text-gray-600');
        document.getElementById('table-view-btn').classList.remove('bg-white', 'text-gray-900', 'shadow-sm');
        document.getElementById('table-view-btn').classList.add('text-gray-600');
    }
    
    localStorage.setItem('visitor_logs_view', viewType);
}

// Filtering and Sorting
function toggleAdvancedFilters() {
    const filtersDiv = document.getElementById('advanced-filters');
    const icon = document.getElementById('advanced-toggle-icon');
    
    if (filtersDiv.classList.contains('hidden')) {
        filtersDiv.classList.remove('hidden');
        icon.classList.remove('fa-chevron-down');
        icon.classList.add('fa-chevron-up');
    } else {
        filtersDiv.classList.add('hidden');
        icon.classList.remove('fa-chevron-up');
        icon.classList.add('fa-chevron-down');
    }
}

function resetFilters() {
    document.getElementById('filter-form').reset();
    window.location.href = '/visitor-logs';
}

function sortBy(field) {
    if (sortField === field) {
        sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
    } else {
        sortField = field;
        sortDirection = 'desc';
    }
    
    const url = new URL(window.location);
    url.searchParams.set('sort', field);
    url.searchParams.set('direction', sortDirection);
    
    window.location.href = url.toString();
}

// Export Functions
function toggleExportDropdown() {
    const dropdown = document.getElementById('export-dropdown');
    dropdown.classList.toggle('hidden');
}

function exportData(format) {
    const form = document.getElementById('filter-form');
    const formData = new FormData(form);
    formData.append('format', format);
    
    const params = new URLSearchParams(formData);
    showLoading();
    
    fetch(`/visitor-logs/export?${params.toString()}`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (response.ok) {
            return response.blob();
        }
        throw new Error('Export failed');
    })
    .then(blob => {
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `visitor_logs_${new Date().toISOString().split('T')[0]}.${format}`;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
        hideLoading();
        showNotification('Export completed successfully', 'success');
    })
    .catch(error => {
        hideLoading();
        showNotification('Export failed. Please try again.', 'error');
    });
    
    document.getElementById('export-dropdown').classList.add('hidden');
}

function exportLogs() {
    exportData('csv');
}

// Auto-refresh
function toggleAutoRefresh() {
    const btn = document.getElementById('auto-refresh-btn');
    const status = document.getElementById('auto-refresh-status');
    
    if (autoRefreshInterval) {
        clearInterval(autoRefreshInterval);
        autoRefreshInterval = null;
        status.textContent = 'Off';
        btn.classList.remove('bg-green-100', 'text-green-700');
        btn.classList.add('bg-gray-100', 'text-gray-700');
    } else {
        autoRefreshInterval = setInterval(refreshData, 30000);
        status.textContent = 'On';
        btn.classList.remove('bg-gray-100', 'text-gray-700');
        btn.classList.add('bg-green-100', 'text-green-700');
    }
}

function refreshData() {
    updateLiveStats();
    updateLiveActivity();
}

// Live Updates
function startLiveUpdates() {
    setInterval(updateLiveStats, 15000);
    setInterval(updateLiveActivity, 20000);
}

function updateLiveStats() {
    fetch('/visitor-logs/realtime/dashboard', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('live-total').textContent = data.live_stats.today_checkins + data.live_stats.today_checkouts;
        document.getElementById('live-active').textContent = data.live_stats.active_visitors;
    })
    .catch(error => console.error('Error updating live stats:', error));
}

function updateLiveActivity() {
    fetch('/visitor-logs/realtime/dashboard', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        const container = document.getElementById('live-activity');
        container.innerHTML = '';
        
        data.recent_activity.slice(0, 5).forEach(activity => {
            const activityElement = document.createElement('div');
            activityElement.className = 'flex items-center space-x-3 p-2 bg-gray-50 rounded-lg animate-fade-in';
            activityElement.innerHTML = `
                <div class="w-2 h-2 ${activity.action === 'checkin' ? 'bg-green-500' : 'bg-blue-500'} rounded-full"></div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate">${activity.registration.user.name}</p>
                    <p class="text-xs text-gray-500">${activity.action.charAt(0).toUpperCase() + activity.action.slice(1)} • ${activity.created_at}</p>
                </div>
            `;
            container.appendChild(activityElement);
        });
    })
    .catch(error => console.error('Error updating live activity:', error));
}

// Modal Functions
function viewLogDetails(logId) {
    showLoading();
    
    fetch(`/visitor-logs/${logId}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('log-modal-content').innerHTML = data.html;
        document.getElementById('log-details-modal').classList.remove('hidden');
        hideLoading();
    })
    .catch(error => {
        hideLoading();
        showNotification('Failed to load log details', 'error');
    });
}

function closeLogModal() {
    document.getElementById('log-details-modal').classList.add('hidden');
}

function viewRegistration(registrationId) {
    window.open(`/registrations/${registrationId}`, '_blank');
}

function viewNote(logId) {
    // Implementation for viewing admin notes
    viewLogDetails(logId);
}

// Confirmation Modal
function showConfirmModal(title, message, callback) {
    document.getElementById('confirm-title').textContent = title;
    document.getElementById('confirm-message').textContent = message;
    document.getElementById('confirm-button').onclick = callback;
    document.getElementById('confirm-modal').classList.remove('hidden');
}

function closeConfirmModal() {
    document.getElementById('confirm-modal').classList.add('hidden');
}

// Delete Functions
function deleteLog(logId) {
    showConfirmModal(
        'Delete Log',
        'Are you sure you want to delete this visitor log? This action cannot be undone.',
        () => {
            performDeleteLog(logId);
            closeConfirmModal();
        }
    );
}

function performDeleteLog(logId) {
    showLoading();
    
    fetch(`/visitor-logs/${logId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const row = document.querySelector(`tr[data-log-id="${logId}"]`);
            if (row) {
                row.remove();
            }
            showNotification('Log deleted successfully', 'success');
        } else {
            showNotification('Failed to delete log', 'error');
        }
        hideLoading();
    })
    .catch(error => {
        hideLoading();
        showNotification('Failed to delete log', 'error');
    });
}

// Selection Management
function setupEventListeners() {
    // Select all checkbox
    document.getElementById('select-all').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.row-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateBulkActions();
    });
    
    // Individual checkboxes
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('row-checkbox')) {
            const allCheckboxes = document.querySelectorAll('.row-checkbox');
            const checkedBoxes = document.querySelectorAll('.row-checkbox:checked');
            const selectAllBox = document.getElementById('select-all');
            
            selectAllBox.checked = allCheckboxes.length === checkedBoxes.length;
            selectAllBox.indeterminate = checkedBoxes.length > 0 && checkedBoxes.length < allCheckboxes.length;
            
            updateBulkActions();
        }
    });
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.relative')) {
            document.getElementById('export-dropdown').classList.add('hidden');
        }
    });
    
    // Load saved view preference
    const savedView = localStorage.getItem('visitor_logs_view');
    if (savedView) {
        setView(savedView);
    }
}

function updateBulkActions() {
    const checkedBoxes = document.querySelectorAll('.row-checkbox:checked');
    const bulkBar = document.getElementById('bulk-actions-bar');
    const selectedCount = document.getElementById('selected-count');
    
    if (checkedBoxes.length > 0) {
        bulkBar.classList.remove('hidden');
        selectedCount.textContent = checkedBoxes.length;
    } else {
        bulkBar.classList.add('hidden');
    }
}

function clearSelection() {
    document.querySelectorAll('.row-checkbox').forEach(checkbox => {
        checkbox.checked = false;
    });
    document.getElementById('select-all').checked = false;
    document.getElementById('select-all').indeterminate = false;
    updateBulkActions();
}

function bulkExport() {
    const checkedBoxes = document.querySelectorAll('.row-checkbox:checked');
    const ids = Array.from(checkedBoxes).map(cb => cb.value);
    
    if (ids.length === 0) {
        showNotification('Please select items to export', 'warning');
        return;
    }
    
    const form = document.createElement('form');
    form.method = 'GET';
    form.action = '/visitor-logs/export';
    
    ids.forEach(id => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'ids[]';
        input.value = id;
        form.appendChild(input);
    });
    
    const formatInput = document.createElement('input');
    formatInput.type = 'hidden';
    formatInput.name = 'format';
    formatInput.value = 'csv';
    form.appendChild(formatInput);
    
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

function bulkDelete() {
    const checkedBoxes = document.querySelectorAll('.row-checkbox:checked');
    const ids = Array.from(checkedBoxes).map(cb => cb.value);
    
    if (ids.length === 0) {
        showNotification('Please select items to delete', 'warning');
        return;
    }
    
    showConfirmModal(
        'Delete Selected Logs',
        `Are you sure you want to delete ${ids.length} selected logs? This action cannot be undone.`,
        () => {
            performBulkDelete(ids);
            closeConfirmModal();
        }
    );
}

function performBulkDelete(ids) {
    showLoading();
    
    fetch('/visitor-logs/bulk-delete', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ ids: ids })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            ids.forEach(id => {
                const row = document.querySelector(`tr[data-log-id="${id}"]`);
                if (row) row.remove();
            });
            clearSelection();
            showNotification(`${data.deleted_count} logs deleted successfully`, 'success');
        } else {
            showNotification('Failed to delete logs', 'error');
        }
        hideLoading();
    })
    .catch(error => {
        hideLoading();
        showNotification('Failed to delete logs', 'error');
    });
}

// Filter Presets
function saveFilterPreset() {
    const form = document.getElementById('filter-form');
    const formData = new FormData(form);
    const filters = {};
    
    for (let [key, value] of formData.entries()) {
        if (value) filters[key] = value;
    }
    
    const presetName = prompt('Enter a name for this filter preset:');
    if (presetName) {
        const presets = JSON.parse(localStorage.getItem('visitor_log_presets') || '{}');
        presets[presetName] = filters;
        localStorage.setItem('visitor_log_presets', JSON.stringify(presets));
        
        showNotification('Filter preset saved successfully', 'success');
        loadFilterPresets();
    }
}

function loadFilterPresets() {
    const presets = JSON.parse(localStorage.getItem('visitor_log_presets') || '{}');
    // Implementation for loading and displaying saved filter presets
    // This would add a dropdown with saved presets
}

// Utility Functions
function showLoading() {
    document.getElementById('loading-overlay').classList.remove('hidden');
}

function hideLoading() {
    document.getElementById('loading-overlay').classList.add('hidden');
}

function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg max-w-sm animate-fade-in ${
        type === 'success' ? 'bg-green-100 text-green-800 border border-green-200' :
        type === 'error' ? 'bg-red-100 text-red-800 border border-red-200' :
        type === 'warning' ? 'bg-yellow-100 text-yellow-800 border border-yellow-200' :
        'bg-blue-100 text-blue-800 border border-blue-200'
    }`;
    
    notification.innerHTML = `
        <div class="flex items-center">
            <i class="fas ${
                type === 'success' ? 'fa-check-circle' :
                type === 'error' ? 'fa-exclamation-circle' :
                type === 'warning' ? 'fa-exclamation-triangle' :
                'fa-info-circle'
            } mr-2"></i>
            <span>${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-current hover:opacity-70">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 5000);
}

// Initialize everything when the page loads
document.addEventListener('DOMContentLoaded', function() {
    // Focus on search input if no other filters are applied
    if (!window.location.search) {
        const searchInput = document.querySelector('input[name="search"]');
        if (searchInput) searchInput.focus();
    }
});
</script>
@endpush

@push('styles')
<style>
/* Custom animations */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.animate-fade-in {
    animation: fadeIn 0.3s ease-out;
}

/* Hover effects */
.hover-scale:hover {
    transform: scale(1.02);
    transition: transform 0.2s ease;
}

/* Loading states */
.loading {
    opacity: 0.6;
    pointer-events: none;
}

/* Custom scrollbar */
.overflow-y-auto::-webkit-scrollbar {
    width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Table row hover effect */
tbody tr:hover {
    background-color: rgba(59, 130, 246, 0.05);
}

/* Card hover effect */
.hover\:shadow-md:hover {
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

/* Real-time indicator pulse */
@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

/* Loading spinner */
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.animate-spin {
    animation: spin 1s linear infinite;
}

/* Transition effects */
.transition-colors {
    transition-property: color, background-color, border-color, text-decoration-color, fill, stroke;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 150ms;
}

.transition-shadow {
    transition-property: box-shadow;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 150ms;
}

/* Focus states */
.focus\:ring-2:focus {
    --tw-ring-offset-shadow: var(--tw-ring-inset) 0 0 0 var(--tw-ring-offset-width) var(--tw-ring-offset-color);
    --tw-ring-shadow: var(--tw-ring-inset) 0 0 0 calc(2px + var(--tw-ring-offset-width)) var(--tw-ring-color);
    box-shadow: var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow, 0 0 #0000);
}

.focus\:ring-blue-500:focus {
    --tw-ring-opacity: 1;
    --tw-ring-color: rgb(59 130 246 / var(--tw-ring-opacity));
}

.focus\:border-transparent:focus {
    border-color: transparent;
}

/* Custom checkbox styles */
.row-checkbox:checked {
    background-color: #3b82f6;
    border-color: #3b82f6;
}

.row-checkbox:indeterminate {
    background-color: #3b82f6;
    border-color: #3b82f6;
}

/* Modal backdrop blur */
.modal-backdrop {
    backdrop-filter: blur(4px);
}

/* Status indicators */
.status-indicator {
    position: relative;
    display: inline-block;
}

.status-indicator::after {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    border: 2px solid white;
}

.status-indicator.online::after {
    background-color: #10b981;
}

.status-indicator.offline::after {
    background-color: #6b7280;
}

/* Chart container */
.chart-container {
    position: relative;
    height: 150px;
    width: 100%;
}

/* Button loading state */
.btn-loading {
    pointer-events: none;
    opacity: 0.7;
}

.btn-loading::after {
    content: '';
    display: inline-block;
    width: 16px;
    height: 16px;
    margin-left: 8px;
    border: 2px solid transparent;
    border-top: 2px solid currentColor;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .grid-cols-1.md\:grid-cols-2.lg\:grid-cols-4 {
        grid-template-columns: repeat(1, minmax(0, 1fr));
    }
    
    .grid-cols-1.md\:grid-cols-6 {
        grid-template-columns: repeat(1, minmax(0, 1fr));
    }
    
    .overflow-x-auto table {
        min-width: 800px;
    }
}

/* Dark mode support (if needed) */
@media (prefers-color-scheme: dark) {
    .dark-mode {
        background-color: #1f2937;
        color: #f9fafb;
    }
    
    .dark-mode .bg-white {
        background-color: #374151;
    }
    
    .dark-mode .text-gray-900 {
        color: #f9fafb;
    }
    
    .dark-mode .border-gray-200 {
        border-color: #4b5563;
    }
}
</style>
@endpush