@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard Overview')

@section('content')
<div class="space-y-6">
    <!-- Welcome Section -->
    <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-xl shadow-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold mb-2">Welcome back, {{ auth()->user()->name }}!</h2>
                <p class="text-blue-100">Here's what's happening with your events today.</p>
            </div>
            <div class="text-right">
                <div class="text-3xl font-bold">{{ now()->format('d') }}</div>
                <div class="text-sm">{{ now()->format('M Y') }}</div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Events -->
        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-blue-500 hover:shadow-xl transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Events</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $stats['total_events'] ?? 0 }}</p>
                    <p class="text-sm text-green-600">
                        <i class="fas fa-arrow-up"></i>
                        {{ $changes['events'] ?? 0 }}% from last month
                    </p>
                </div>
                <div class="bg-blue-100 p-3 rounded-full">
                    <i class="fas fa-calendar-alt text-2xl text-blue-600"></i>
                </div>
            </div>
        </div>

        <!-- Active Events -->
        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-green-500 hover:shadow-xl transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Active Events</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $stats['active_events'] ?? 0 }}</p>
                    <p class="text-sm text-gray-500">Currently running</p>
                </div>
                <div class="bg-green-100 p-3 rounded-full">
                    <i class="fas fa-play-circle text-2xl text-green-600"></i>
                </div>
            </div>
        </div>

        <!-- Total Registrations -->
        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-purple-500 hover:shadow-xl transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Registrations</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $stats['total_registrations'] ?? 0 }}</p>
                    <p class="text-sm text-green-600">
                        <i class="fas fa-arrow-up"></i>
                        {{ $changes['registrations'] ?? 0 }}% from last month
                    </p>
                </div>
                <div class="bg-purple-100 p-3 rounded-full">
                    <i class="fas fa-user-plus text-2xl text-purple-600"></i>
                </div>
            </div>
        </div>

        <!-- Total Users -->
        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-yellow-500 hover:shadow-xl transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Users</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $stats['total_users'] ?? 0 }}</p>
                    <p class="text-sm text-gray-500">System users</p>
                </div>
                <div class="bg-yellow-100 p-3 rounded-full">
                    <i class="fas fa-users text-2xl text-yellow-600"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <a href="{{ route('events.create') }}" class="flex flex-col items-center p-4 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors">
                <i class="fas fa-plus-circle text-2xl text-blue-600 mb-2"></i>
                <span class="text-sm font-medium text-gray-700">Create Event</span>
            </a>
            <a href="{{ route('registrations.index') }}" class="flex flex-col items-center p-4 bg-green-50 hover:bg-green-100 rounded-lg transition-colors">
                <i class="fas fa-user-plus text-2xl text-green-600 mb-2"></i>
                <span class="text-sm font-medium text-gray-700">View Registrations</span>
            </a>
            <a href="{{ route('checkin.index') }}" class="flex flex-col items-center p-4 bg-purple-50 hover:bg-purple-100 rounded-lg transition-colors">
                <i class="fas fa-qrcode text-2xl text-purple-600 mb-2"></i>
                <span class="text-sm font-medium text-gray-700">QR Scanner</span>
            </a>
            <a href="{{ route('visitor-logs.index') }}" class="flex flex-col items-center p-4 bg-orange-50 hover:bg-orange-100 rounded-lg transition-colors">
                <i class="fas fa-chart-line text-2xl text-orange-600 mb-2"></i>
                <span class="text-sm font-medium text-gray-700">Analytics</span>
            </a>
        </div>
    </div>

    <!-- Recent Events -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900">Recent Events</h3>
            <a href="{{ route('events.index') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">View All</a>
        </div>
        @if($recentEvents && $recentEvents->count() > 0)
        <div class="space-y-4">
            @foreach($recentEvents->take(5) as $event)
            <div class="flex items-center justify-between p-3 hover:bg-gray-50 rounded-lg transition-colors">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-500 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-calendar-alt text-white text-sm"></i>
                    </div>
                    <div>
                        <h4 class="font-medium text-gray-900">{{ $event->name }}</h4>
                        <p class="text-sm text-gray-500">{{ $event->venue->name ?? 'No venue' }} • {{ $event->start_date->format('M d') }}</p>
                    </div>
                </div>
                <div class="text-right">
                    <span class="px-2 py-1 text-xs rounded-full {{ $event->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                        {{ $event->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-8">
            <i class="fas fa-calendar-alt text-4xl text-gray-300 mb-3"></i>
            <p class="text-gray-500">No events yet</p>
            <a href="{{ route('events.create') }}" class="mt-4 inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                <i class="fas fa-plus mr-2"></i>
                Create Your First Event
            </a>
        </div>
        @endif
    </div>

    <!-- Event Status -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Event Status Distribution</h3>
        <div class="grid grid-cols-3 gap-4">
            <div class="text-center p-4 bg-blue-50 rounded-lg">
                <div class="text-2xl font-bold text-blue-600">{{ $eventStats['upcoming'] ?? 0 }}</div>
                <div class="text-sm text-gray-600">Upcoming</div>
            </div>
            <div class="text-center p-4 bg-green-50 rounded-lg">
                <div class="text-2xl font-bold text-green-600">{{ $eventStats['ongoing'] ?? 0 }}</div>
                <div class="text-sm text-gray-600">Ongoing</div>
            </div>
            <div class="text-center p-4 bg-gray-50 rounded-lg">
                <div class="text-2xl font-bold text-gray-600">{{ $eventStats['completed'] ?? 0 }}</div>
                <div class="text-sm text-gray-600">Completed</div>
            </div>
        </div>
    </div>
</div>
@endsection