@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard Overview')

@section('content')
<div class="space-y-6">
    <!-- Welcome Section with Enhanced Gradient -->
    <div class="relative overflow-hidden bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-500 rounded-2xl shadow-2xl p-8 text-white">
        <div class="absolute inset-0 bg-black opacity-10"></div>
        <div class="relative z-10">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-3xl font-bold mb-3 animate-fade-in">Welcome back, {{ auth()->user()->name }}! 👋</h2>
                    <p class="text-indigo-100 text-lg">Here's what's happening with your events today.</p>
                    <div class="mt-4 flex items-center space-x-4">
                        <span class="bg-white/20 backdrop-blur-sm px-4 py-2 rounded-full text-sm font-semibold">
                            <i class="fas fa-clock mr-2"></i>{{ now()->format('l, F j, Y') }}
                        </span>
                        <span class="bg-white/20 backdrop-blur-sm px-4 py-2 rounded-full text-sm font-semibold">
                            <i class="fas fa-users mr-2"></i>{{ $stats['active_visitors'] ?? 0 }} Active Now
                        </span>
                    </div>
                </div>
                <div class="text-right">
                    <div class="bg-white/20 backdrop-blur-sm rounded-2xl p-6">
                        <div class="text-5xl font-bold mb-2">{{ now()->format('d') }}</div>
                        <div class="text-lg">{{ now()->format('M Y') }}</div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Decorative Elements -->
        <div class="absolute -bottom-20 -right-20 w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
        <div class="absolute -top-20 -left-20 w-64 h-64 bg-purple-400/20 rounded-full blur-3xl"></div>
    </div>

    <!-- Enhanced Stats Cards with Animations -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Events Card -->
        <div class="group bg-white rounded-2xl shadow-lg p-6 border-l-4 border-indigo-500 hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Events</p>
                    <p class="text-4xl font-bold text-gray-900 mt-2">{{ $stats['total_events'] ?? 0 }}</p>
                    <div class="flex items-center mt-3">
                        @if(($changes['events'] ?? 0) > 0)
                            <span class="text-sm text-green-600 font-semibold">
                                <i class="fas fa-arrow-up mr-1"></i>{{ $changes['events'] }}%
                            </span>
                        @else
                            <span class="text-sm text-red-600 font-semibold">
                                <i class="fas fa-arrow-down mr-1"></i>{{ abs($changes['events'] ?? 0) }}%
                            </span>
                        @endif
                        <span class="text-gray-500 text-xs ml-2">vs last month</span>
                    </div>
                </div>
                <div class="bg-gradient-to-br from-indigo-100 to-indigo-200 p-4 rounded-2xl group-hover:scale-110 transition-transform">
                    <i class="fas fa-calendar-alt text-3xl text-indigo-600"></i>
                </div>
            </div>
        </div>

        <!-- Active Events Card -->
        <div class="group bg-white rounded-2xl shadow-lg p-6 border-l-4 border-emerald-500 hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Active Events</p>
                    <p class="text-4xl font-bold text-gray-900 mt-2">{{ $stats['active_events'] ?? 0 }}</p>
                    <div class="mt-3">
                        <div class="flex items-center">
                            <div class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse mr-2"></div>
                            <span class="text-sm text-gray-500">Currently running</span>
                        </div>
                    </div>
                </div>
                <div class="bg-gradient-to-br from-emerald-100 to-emerald-200 p-4 rounded-2xl group-hover:scale-110 transition-transform">
                    <i class="fas fa-play-circle text-3xl text-emerald-600"></i>
                </div>
            </div>
        </div>

        <!-- Total Registrations Card -->
        <div class="group bg-white rounded-2xl shadow-lg p-6 border-l-4 border-purple-500 hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Registrations</p>
                    <p class="text-4xl font-bold text-gray-900 mt-2">{{ $stats['total_registrations'] ?? 0 }}</p>
                    <div class="flex items-center mt-3">
                        @if(($changes['registrations'] ?? 0) > 0)
                            <span class="text-sm text-green-600 font-semibold">
                                <i class="fas fa-arrow-up mr-1"></i>{{ $changes['registrations'] }}%
                            </span>
                        @else
                            <span class="text-sm text-red-600 font-semibold">
                                <i class="fas fa-arrow-down mr-1"></i>{{ abs($changes['registrations'] ?? 0) }}%
                            </span>
                        @endif
                        <span class="text-gray-500 text-xs ml-2">vs last month</span>
                    </div>
                </div>
                <div class="bg-gradient-to-br from-purple-100 to-purple-200 p-4 rounded-2xl group-hover:scale-110 transition-transform">
                    <i class="fas fa-user-plus text-3xl text-purple-600"></i>
                </div>
            </div>
        </div>

        <!-- Check-ins Today Card -->
        <div class="group bg-white rounded-2xl shadow-lg p-6 border-l-4 border-amber-500 hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Check-ins Today</p>
                    <p class="text-4xl font-bold text-gray-900 mt-2">{{ $stats['checkins_today'] ?? 0 }}</p>
                    <div class="mt-3">
                        <div class="flex items-center space-x-3">
                            <span class="text-xs bg-amber-100 text-amber-800 px-2 py-1 rounded-full font-semibold">
                                <i class="fas fa-qrcode mr-1"></i>QR: {{ $stats['qr_checkins_today'] ?? 0 }}
                            </span>
                            <span class="text-xs bg-gray-100 text-gray-800 px-2 py-1 rounded-full font-semibold">
                                Manual: {{ $stats['manual_checkins_today'] ?? 0 }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="bg-gradient-to-br from-amber-100 to-amber-200 p-4 rounded-2xl group-hover:scale-110 transition-transform">
                    <i class="fas fa-sign-in-alt text-3xl text-amber-600"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions Grid with Enhanced Design -->
    <div class="bg-white rounded-2xl shadow-lg p-8">
        <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
            <i class="fas fa-rocket text-indigo-600 mr-3"></i>
            Quick Actions
        </h3>
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
            <!-- Create Event -->
            <a href="{{ route('events.create') }}" class="group flex flex-col items-center p-6 bg-gradient-to-br from-blue-50 to-blue-100 hover:from-blue-100 hover:to-blue-200 rounded-xl transition-all duration-300 transform hover:scale-105">
                <div class="bg-blue-500 text-white p-4 rounded-2xl mb-3 group-hover:shadow-lg transition-shadow">
                    <i class="fas fa-plus-circle text-2xl"></i>
                </div>
                <span class="text-sm font-semibold text-gray-800">Create Event</span>
            </a>

            <!-- QR Scanner -->
            <a href="{{ route('checkin.index') }}" class="group flex flex-col items-center p-6 bg-gradient-to-br from-purple-50 to-purple-100 hover:from-purple-100 hover:to-purple-200 rounded-xl transition-all duration-300 transform hover:scale-105">
                <div class="bg-purple-500 text-white p-4 rounded-2xl mb-3 group-hover:shadow-lg transition-shadow">
                    <i class="fas fa-qrcode text-2xl"></i>
                </div>
                <span class="text-sm font-semibold text-gray-800">QR Check-in</span>
            </a>

            <!-- Check-out -->
            <a href="{{ route('checkin.checkout') }}" class="group flex flex-col items-center p-6 bg-gradient-to-br from-indigo-50 to-indigo-100 hover:from-indigo-100 hover:to-indigo-200 rounded-xl transition-all duration-300 transform hover:scale-105">
                <div class="bg-indigo-500 text-white p-4 rounded-2xl mb-3 group-hover:shadow-lg transition-shadow">
                    <i class="fas fa-sign-out-alt text-2xl"></i>
                </div>
                <span class="text-sm font-semibold text-gray-800">Check-out</span>
            </a>

            <!-- Scan & Print -->
            <a href="{{ route('checkin.scan-for-print') }}" class="group flex flex-col items-center p-6 bg-gradient-to-br from-emerald-50 to-emerald-100 hover:from-emerald-100 hover:to-emerald-200 rounded-xl transition-all duration-300 transform hover:scale-105">
                <div class="bg-emerald-500 text-white p-4 rounded-2xl mb-3 group-hover:shadow-lg transition-shadow">
                    <i class="fas fa-print text-2xl"></i>
                </div>
                <span class="text-sm font-semibold text-gray-800">Scan & Print</span>
            </a>

            <!-- View Registrations -->
            <a href="{{ route('registrations.index') }}" class="group flex flex-col items-center p-6 bg-gradient-to-br from-green-50 to-green-100 hover:from-green-100 hover:to-green-200 rounded-xl transition-all duration-300 transform hover:scale-105">
                <div class="bg-green-500 text-white p-4 rounded-2xl mb-3 group-hover:shadow-lg transition-shadow">
                    <i class="fas fa-users text-2xl"></i>
                </div>
                <span class="text-sm font-semibold text-gray-800">Registrations</span>
            </a>

            <!-- Analytics -->
            <a href="{{ route('visitor-logs.analytics') }}" class="group flex flex-col items-center p-6 bg-gradient-to-br from-orange-50 to-orange-100 hover:from-orange-100 hover:to-orange-200 rounded-xl transition-all duration-300 transform hover:scale-105">
                <div class="bg-orange-500 text-white p-4 rounded-2xl mb-3 group-hover:shadow-lg transition-shadow">
                    <i class="fas fa-chart-line text-2xl"></i>
                </div>
                <span class="text-sm font-semibold text-gray-800">Analytics</span>
            </a>
        </div>
    </div>

    <!-- Live Activity Feed & Recent Events -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Live Activity Feed -->
        <div class="bg-white rounded-2xl shadow-lg p-8">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-gray-900 flex items-center">
                    <i class="fas fa-stream text-purple-600 mr-3"></i>
                    Live Activity Feed
                    <span class="ml-2 w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                </h3>
                <a href="{{ route('visitor-logs.realtime') }}" class="text-purple-600 hover:text-purple-800 text-sm font-semibold">
                    View Real-time →
                </a>
            </div>
            @if($recentActivity && $recentActivity->count() > 0)
            <div class="space-y-3 max-h-96 overflow-y-auto">
                @foreach($recentActivity->take(8) as $activity)
                <div class="flex items-center p-3 hover:bg-gray-50 rounded-lg transition-colors">
                    <div class="flex-shrink-0 mr-3">
                        @if($activity->action == 'checkin')
                            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-sign-in-alt text-green-600"></i>
                            </div>
                        @else
                            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-sign-out-alt text-blue-600"></i>
                            </div>
                        @endif
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-900">
                            {{ $activity->registration->user->name ?? 'Unknown' }}
                        </p>
                        <p class="text-xs text-gray-500">
                            {{ ucfirst($activity->action) }} • {{ $activity->registration->event->name ?? 'Unknown Event' }}
                        </p>
                    </div>
                    <div class="text-xs text-gray-400">
                        {{ $activity->created_at->diffForHumans() }}
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-12">
                <i class="fas fa-history text-4xl text-gray-300 mb-3"></i>
                <p class="text-gray-500">No recent activity</p>
            </div>
            @endif
        </div>

        <!-- Recent Events with Better Design -->
        <div class="bg-white rounded-2xl shadow-lg p-8">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-gray-900 flex items-center">
                    <i class="fas fa-calendar-week text-indigo-600 mr-3"></i>
                    Recent Events
                </h3>
                <a href="{{ route('events.index') }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-semibold">
                    View All →
                </a>
            </div>
            @if($recentEvents && $recentEvents->count() > 0)
            <div class="space-y-4">
                @foreach($recentEvents->take(5) as $event)
                <div class="group p-4 border border-gray-200 hover:border-indigo-300 rounded-xl hover:shadow-md transition-all duration-300">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center flex-1">
                            <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-xl flex items-center justify-center mr-4 group-hover:scale-110 transition-transform">
                                <i class="fas fa-calendar-alt text-white text-lg"></i>
                            </div>
                            <div class="flex-1">
                                <h4 class="font-semibold text-gray-900 group-hover:text-indigo-600 transition-colors">{{ $event->name }}</h4>
                                <div class="flex items-center space-x-3 mt-1">
                                    <span class="text-xs text-gray-500 flex items-center">
                                        <i class="fas fa-map-marker-alt mr-1"></i>
                                        {{ $event->venue->name ?? 'No venue' }}
                                    </span>
                                    <span class="text-xs text-gray-500 flex items-center">
                                        <i class="fas fa-clock mr-1"></i>
                                        {{ $event->start_date->format('M d, Y') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="text-xs font-semibold text-gray-600 bg-gray-100 px-3 py-1 rounded-full">
                                {{ $event->registrations_count ?? 0 }} registered
                            </span>
                            <span class="px-3 py-1 text-xs rounded-full font-semibold {{ $event->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $event->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-12">
                <i class="fas fa-calendar-alt text-4xl text-gray-300 mb-3"></i>
                <p class="text-gray-500 mb-4">No events yet</p>
                <a href="{{ route('events.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                    <i class="fas fa-plus mr-2"></i>
                    Create Your First Event
                </a>
            </div>
            @endif
        </div>
    </div>

    <!-- Analytics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Event Status Distribution -->
        <div class="bg-white rounded-2xl shadow-lg p-8">
            <h3 class="text-lg font-bold text-gray-900 mb-6 flex items-center">
                <i class="fas fa-chart-pie text-indigo-600 mr-3"></i>
                Event Status Distribution
            </h3>
            <div class="space-y-4">
                <div class="flex items-center justify-between p-4 bg-blue-50 rounded-xl">
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-blue-500 rounded-full mr-3"></div>
                        <span class="font-medium text-gray-700">Upcoming</span>
                    </div>
                    <span class="text-2xl font-bold text-blue-600">{{ $eventStats['upcoming'] ?? 0 }}</span>
                </div>
                <div class="flex items-center justify-between p-4 bg-green-50 rounded-xl">
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-green-500 rounded-full mr-3"></div>
                        <span class="font-medium text-gray-700">Ongoing</span>
                    </div>
                    <span class="text-2xl font-bold text-green-600">{{ $eventStats['ongoing'] ?? 0 }}</span>
                </div>
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-gray-500 rounded-full mr-3"></div>
                        <span class="font-medium text-gray-700">Completed</span>
                    </div>
                    <span class="text-2xl font-bold text-gray-600">{{ $eventStats['completed'] ?? 0 }}</span>
                </div>
            </div>
        </div>

        <!-- Performance Metrics -->
        <div class="bg-white rounded-2xl shadow-lg p-8">
            <h3 class="text-lg font-bold text-gray-900 mb-6 flex items-center">
                <i class="fas fa-tachometer-alt text-purple-600 mr-3"></i>
                Performance Metrics
            </h3>
            <div class="space-y-4">
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm font-medium text-gray-600">Conversion Rate</span>
                        <span class="text-sm font-bold text-gray-900">{{ $performanceMetrics['conversion_rate'] ?? 0 }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-purple-600 h-2 rounded-full" style="width: {{ $performanceMetrics['conversion_rate'] ?? 0 }}%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm font-medium text-gray-600">Completion Rate</span>
                        <span class="text-sm font-bold text-gray-900">{{ $performanceMetrics['completion_rate'] ?? 0 }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-green-600 h-2 rounded-full" style="width: {{ $performanceMetrics['completion_rate'] ?? 0 }}%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm font-medium text-gray-600">No-show Rate</span>
                        <span class="text-sm font-bold text-gray-900">{{ $performanceMetrics['no_show_rate'] ?? 0 }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-red-600 h-2 rounded-full" style="width: {{ $performanceMetrics['no_show_rate'] ?? 0 }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Venues -->
        <div class="bg-white rounded-2xl shadow-lg p-8">
            <h3 class="text-lg font-bold text-gray-900 mb-6 flex items-center">
                <i class="fas fa-building text-emerald-600 mr-3"></i>
                Top Venues
            </h3>
            @if(isset($analytics['venue_performance']) && count($analytics['venue_performance']) > 0)
            <div class="space-y-3">
                @foreach($analytics['venue_performance']->take(3) as $venue)
                <div class="flex items-center justify-between p-3 hover:bg-gray-50 rounded-lg transition-colors">
                    <div>
                        <p class="font-medium text-gray-900">{{ $venue['name'] }}</p>
                        <p class="text-xs text-gray-500">{{ $venue['total_events'] }} events</p>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-bold text-emerald-600">{{ $venue['total_registrations'] }}</p>
                        <p class="text-xs text-gray-500">registrations</p>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-gray-500 text-center py-4">No venue data available</p>
            @endif
        </div>
    </div>

    <!-- System Alerts -->
    @if(isset($alerts) && count($alerts) > 0)
    <div class="space-y-4">
        @foreach($alerts as $alert)
        <div class="bg-{{ $alert['type'] == 'danger' ? 'red' : 'yellow' }}-50 border-l-4 border-{{ $alert['type'] == 'danger' ? 'red' : 'yellow' }}-500 p-4 rounded-lg">
            <div class="flex items-center">
                <i class="fas fa-{{ $alert['type'] == 'danger' ? 'exclamation-circle' : 'exclamation-triangle' }} text-{{ $alert['type'] == 'danger' ? 'red' : 'yellow' }}-500 mr-3"></i>
                <div class="flex-1">
                    <p class="font-medium text-{{ $alert['type'] == 'danger' ? 'red' : 'yellow' }}-800">{{ $alert['message'] }}</p>
                </div>
                <a href="{{ $alert['action'] }}" class="text-{{ $alert['type'] == 'danger' ? 'red' : 'yellow' }}-600 hover:text-{{ $alert['type'] == 'danger' ? 'red' : 'yellow' }}-800 text-sm font-semibold">
                    View Details →
                </a>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>

<style>
@keyframes fade-in {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in {
    animation: fade-in 0.5s ease-out;
}
</style>
@endsection