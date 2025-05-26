{{-- resources/views/visitor-logs/realtime.blade.php --}}
@extends('layouts.app')

@section('title', 'Real-time Dashboard')
@section('page-title', 'Live Visitor Tracking')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-green-600 to-blue-600 rounded-xl shadow-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold mb-2">Live Visitor Dashboard</h2>
                <p class="text-green-100">Real-time monitoring and analytics</p>
            </div>
            <div class="flex items-center space-x-4">
                <div class="text-center">
                    <div class="text-2xl font-bold" id="live-checkins">{{ $data['live_stats']['today_checkins'] }}</div>
                    <div class="text-sm text-green-100">Today's Check-ins</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold" id="live-active-count">{{ $data['live_stats']['active_visitors'] }}</div>
                    <div class="text-sm text-green-100">Active Now</div>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-3 h-3 bg-green-400 rounded-full animate-pulse"></div>
                    <span class="text-sm">Live</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Event Filter -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-bold text-gray-900">Filter by Event</h3>
            <div class="flex items-center space-x-4">
                <select id="event-filter" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">All Events</option>
                    @foreach($events as $event)
                        <option value="{{ $event->id }}" {{ $eventId == $event->id ? 'selected' : '' }}>
                            {{ $event->name }}
                        </option>
                    @endforeach
                </select>
                <button onclick="toggleAutoRefresh()" id="auto-refresh-btn" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                    <i class="fas fa-sync-alt mr-2"></i>Auto-refresh: <span id="refresh-status">On</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Live Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Today's Check-ins</p>
                    <p class="text-3xl font-bold text-gray-900" id="stat-checkins">{{ $data['live_stats']['today_checkins'] }}</p>
                </div>
                <div class="bg-green-100 p-3 rounded-full">
                    <i class="fas fa-sign-in-alt text-2xl text-green-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Today's Check-outs</p>
                    <p class="text-3xl font-bold text-gray-900" id="stat-checkouts">{{ $data['live_stats']['today_checkouts'] }}</p>
                </div>
                <div class="bg-blue-100 p-3 rounded-full">
                    <i class="fas fa-sign-out-alt text-2xl text-blue-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-orange-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Currently Active</p>
                    <p class="text-3xl font-bold text-gray-900" id="stat-active">{{ $data['live_stats']['active_visitors'] }}</p>
                </div>
                <div class="bg-orange-100 p-3 rounded-full">
                    <i class="fas fa-users text-2xl text-orange-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Last Hour</p>
                    <p class="text-3xl font-bold text-gray-900" id="stat-last-hour">{{ $data['live_stats']['last_hour_checkins'] }}</p>
                </div>
                <div class="bg-purple-100 p-3 rounded-full">
                    <i class="fas fa-clock text-2xl text-purple-600"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Visitors and Recent Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Active Visitors -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold text-gray-900">
                    <i class="fas fa-users mr-2 text-orange-600"></i>
                    Active Visitors ({{ count($data['active_visitors']) }})
                </h3>
                <button onclick="refreshActiveVisitors()" class="text-blue-600 hover:text-blue-800">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
            
            <div id="active-visitors-list" class="space-y-3 max-h-96 overflow-y-auto">
                @forelse($data['active_visitors'] as $visitor)
                <div class="flex items-center justify-between p-3 bg-orange-50 rounded-lg">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-orange-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-user text-orange-600"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">{{ $visitor['user_name'] }}</p>
                            <p class="text-sm text-gray-500">{{ $visitor['event_name'] }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-medium text-orange-600">{{ $visitor['duration_minutes'] }}m</p>
                        <p class="text-xs text-gray-500">active</p>
                    </div>
                </div>
                @empty
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-users text-4xl mb-2"></i>
                    <p>No active visitors</p>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold text-gray-900">
                    <i class="fas fa-clock mr-2 text-blue-600"></i>
                    Recent Activity
                </h3>
                <button onclick="refreshRecentActivity()" class="text-blue-600 hover:text-blue-800">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
            
            <div id="recent-activity-list" class="space-y-3 max-h-96 overflow-y-auto">
                @foreach($data['recent_activity'] as $activity)
                <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                    <div class="w-2 h-2 {{ $activity->action === 'checkin' ? 'bg-green-500' : 'bg-blue-500' }} rounded-full"></div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-900">{{ $activity->registration->user->name }}</p>
                        <p class="text-xs text-gray-500">
                            {{ ucfirst($activity->action) }} • {{ $activity->created_at->diffForHumans() }}
                        </p>
                    </div>
                    <span class="px-2 py-1 text-xs rounded-full {{ $activity->action === 'checkin' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                        {{ ucfirst($activity->action) }}
                    </span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Hourly Activity Chart -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <h3 class="text-lg font-bold text-gray-900 mb-4">
            <i class="fas fa-chart-line mr-2 text-indigo-600"></i>
            Today's Hourly Activity
        </h3>
        <div class="h-64">
            <canvas id="hourly-activity-chart"></canvas>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let autoRefreshEnabled = true;
let refreshInterval;
let hourlyChart;

document.addEventListener('DOMContentLoaded', function() {
    initializeChart();
    startAutoRefresh();
    
    // Event filter change
    document.getElementById('event-filter').addEventListener('change', function() {
        const eventId = this.value;
        const url = new URL(window.location);
        if (eventId) {
            url.searchParams.set('event_id', eventId);
        } else {
            url.searchParams.delete('event_id');
        }
        window.location.href = url.toString();
    });
});

function initializeChart() {
    const ctx = document.getElementById('hourly-activity-chart').getContext('2d');
    const hourlyData = @json(array_values($data['hourly_checkins']));
    
    hourlyChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: Array.from({length: 24}, (_, i) => i + ':00'),
            datasets: [{
                label: 'Check-ins',
                data: hourlyData,
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
}

function startAutoRefresh() {
    if (autoRefreshEnabled) {
        refreshInterval = setInterval(refreshData, 10000); // Refresh every 10 seconds
    }
}

function toggleAutoRefresh() {
    autoRefreshEnabled = !autoRefreshEnabled;
    const btn = document.getElementById('auto-refresh-btn');
    const status = document.getElementById('refresh-status');
    
    if (autoRefreshEnabled) {
        startAutoRefresh();
        status.textContent = 'On';
        btn.classList.remove('bg-gray-600');
        btn.classList.add('bg-green-600');
    } else {
        clearInterval(refreshInterval);
        status.textContent = 'Off';
        btn.classList.remove('bg-green-600');
        btn.classList.add('bg-gray-600');
    }
}

function refreshData() {
    const eventId = document.getElementById('event-filter').value;
    const url = new URL('{{ route("visitor-logs.realtime") }}');
    if (eventId) url.searchParams.set('event_id', eventId);
    
    fetch(url, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        updateLiveStats(data.live_stats);
        updateActiveVisitors(data.active_visitors);
        updateRecentActivity(data.recent_activity);
        updateHourlyChart(data.hourly_checkins);
    })
    .catch(error => {
        console.error('Error refreshing data:', error);
    });
}

function updateLiveStats(stats) {
    document.getElementById('live-checkins').textContent = stats.today_checkins;
    document.getElementById('live-active-count').textContent = stats.active_visitors;
    document.getElementById('stat-checkins').textContent = stats.today_checkins;
    document.getElementById('stat-checkouts').textContent = stats.today_checkouts;
    document.getElementById('stat-active').textContent = stats.active_visitors;
    document.getElementById('stat-last-hour').textContent = stats.last_hour_checkins;
}

function updateActiveVisitors(visitors) {
    const container = document.getElementById('active-visitors-list');
    
    if (visitors.length === 0) {
        container.innerHTML = `
            <div class="text-center py-8 text-gray-500">
                <i class="fas fa-users text-4xl mb-2"></i>
                <p>No active visitors</p>
            </div>
        `;
        return;
    }
    
    container.innerHTML = visitors.map(visitor => `
        <div class="flex items-center justify-between p-3 bg-orange-50 rounded-lg">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-orange-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-user text-orange-600"></i>
                </div>
                <div>
                    <p class="font-medium text-gray-900">${visitor.user_name}</p>
                    <p class="text-sm text-gray-500">${visitor.event_name}</p>
                </div>
            </div>
            <div class="text-right">
                <p class="text-sm font-medium text-orange-600">${visitor.duration_minutes}m</p>
                <p class="text-xs text-gray-500">active</p>
            </div>
        </div>
    `).join('');
}

function updateRecentActivity(activities) {
    const container = document.getElementById('recent-activity-list');
    
    container.innerHTML = activities.map(activity => `
        <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
            <div class="w-2 h-2 ${activity.action === 'checkin' ? 'bg-green-500' : 'bg-blue-500'} rounded-full"></div>
            <div class="flex-1">
                <p class="text-sm font-medium text-gray-900">${activity.registration.user.name}</p>
                <p class="text-xs text-gray-500">
                    ${activity.action.charAt(0).toUpperCase() + activity.action.slice(1)} • ${activity.created_at}
                </p>
            </div>
            <span class="px-2 py-1 text-xs rounded-full ${activity.action === 'checkin' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'}">
                ${activity.action.charAt(0).toUpperCase() + activity.action.slice(1)}
            </span>
        </div>
    `).join('');
}

function updateHourlyChart(hourlyData) {
    if (hourlyChart) {
        hourlyChart.data.datasets[0].data = Object.values(hourlyData);
        hourlyChart.update('none');
    }
}

function refreshActiveVisitors() {
    refreshData();
}

function refreshRecentActivity() {
    refreshData();
}
</script>
@endpush