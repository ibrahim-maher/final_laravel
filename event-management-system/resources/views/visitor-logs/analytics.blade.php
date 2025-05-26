{{-- resources/views/visitor-logs/analytics.blade.php --}}
@extends('layouts.app')

@section('title', 'Visitor Analytics')
@section('page-title', 'Advanced Analytics Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-purple-600 to-indigo-600 rounded-xl shadow-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold mb-2">Visitor Analytics Dashboard</h2>
                <p class="text-purple-100">Comprehensive insights and trends analysis</p>
            </div>
            <div class="flex items-center space-x-4">
                <i class="fas fa-chart-line text-4xl text-purple-200"></i>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Event</label>
                <select name="event_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                    <option value="">All Events</option>
                    @foreach($events as $event)
                        <option value="{{ $event->id }}" {{ $eventId == $event->id ? 'selected' : '' }}>
                            {{ $event->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Date Range</label>
                <select name="date_range" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                    <option value="7" {{ $dateRange == '7' ? 'selected' : '' }}>Last 7 days</option>
                    <option value="30" {{ $dateRange == '30' ? 'selected' : '' }}>Last 30 days</option>
                    <option value="90" {{ $dateRange == '90' ? 'selected' : '' }}>Last 90 days</option>
                    <option value="365" {{ $dateRange == '365' ? 'selected' : '' }}>Last year</option>
                </select>
            </div>
            
            <div class="md:col-span-2 flex items-end">
                <button type="submit" class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                    <i class="fas fa-search mr-2"></i>Update Analytics
                </button>
            </div>
        </form>
    </div>

    <!-- Overview Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Visits</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($analytics['overview']['total_visits']) }}</p>
                </div>
                <div class="bg-blue-100 p-3 rounded-full">
                    <i class="fas fa-chart-bar text-2xl text-blue-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Unique Visitors</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($analytics['overview']['unique_visitors']) }}</p>
                </div>
                <div class="bg-green-100 p-3 rounded-full">
                    <i class="fas fa-users text-2xl text-green-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-yellow-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Avg Duration</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($analytics['overview']['average_duration']) }}m</p>
                </div>
                <div class="bg-yellow-100 p-3 rounded-full">
                    <i class="fas fa-clock text-2xl text-yellow-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Hours</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($analytics['overview']['total_duration'] / 60, 1) }}h</p>
                </div>
                <div class="bg-purple-100 p-3 rounded-full">
                    <i class="fas fa-stopwatch text-2xl text-purple-600"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row 1 -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Daily Trends -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">
                <i class="fas fa-chart-line mr-2 text-blue-600"></i>
                Daily Trends
            </h3>
            <div class="h-64">
                <canvas id="daily-trends-chart"></canvas>
            </div>
        </div>

        <!-- Hourly Distribution -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">
                <i class="fas fa-clock mr-2 text-green-600"></i>
                Hourly Distribution
            </h3>
            <div class="h-64">
                <canvas id="hourly-distribution-chart"></canvas>
            </div>
        </div>
    </div>

    <!-- Charts Row 2 -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Duration Analysis -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">
                <i class="fas fa-pie-chart mr-2 text-purple-600"></i>
                Duration Analysis
            </h3>
            <div class="h-64">
                <canvas id="duration-analysis-chart"></canvas>
            </div>
        </div>

        <!-- Top Events -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">
                <i class="fas fa-trophy mr-2 text-yellow-600"></i>
                Top Events by Visits
            </h3>
            <div class="space-y-3">
                @foreach($analytics['top_events']->take(5) as $index => $event)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center">
                            <i class="fas fa-calendar-alt text-gray-500"></i>
                        </div>
                        <span class="text-gray-800 font-medium">{{ $event->name }}</span>
                    </div>
                    <span class="text-gray-600">{{ number_format($event->visits) }} visits</span>
                </div>
                @endforeach
                @if($analytics['top_events']->count() > 5)
                <div class="text-center text-gray-600 mt-3">
                    <a href="{{ route('visitor-logs.analytics.events') }}" class="text-purple-600 hover:underline">
                        View All Events
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
</div>
@endsection
<script>
document.addEventListener('DOMContentLoaded', function() {
    const dailyTrendsCtx = document.getElementById('daily-trends-chart').getContext('2d');
    const hourlyDistributionCtx = document.getElementById('hourly-distribution-chart').getContext('2d');
    const durationAnalysisCtx = document.getElementById('duration-analysis-chart').getContext('2d');

    // Daily Trends Chart
    new Chart(dailyTrendsCtx, {
        type: 'line',
        data: {
            labels: @json($analytics['daily_trends']['dates']),
            datasets: [{
                label: 'Visits',
                data: @json($analytics['daily_trends']['visits']),
                borderColor: '#4F46E5',
                backgroundColor: 'rgba(79, 70, 229, 0.1)',
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: { grid: { display: false } },
                y: { beginAtZero: true }
            }
        }
    });

    // Hourly Distribution Chart
    new Chart(hourlyDistributionCtx, {
        type: 'bar',
        data: {
            labels: @json($analytics['hourly_distribution']['hours']),
            datasets: [{
                label: 'Visits',
                data: @json($analytics['hourly_distribution']['visits']),
                backgroundColor: '#10B981',
                borderRadius: 5
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: { grid: { display: false } },
                y: { beginAtZero: true }
            }
        }
    });

    // Duration Analysis Chart
    new Chart(durationAnalysisCtx, {
        type: 'pie',
        data: {
            labels: @json($analytics['duration_analysis']['labels']),
            datasets: [{
                data: @json($analytics['duration_analysis']['data']),
                backgroundColor: ['#F59E0B', '#EF4444', '#6366F1', '#10B981'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top' },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.label || '';
                            if (label) {
                                label += ': ';
                            }
                            label += context.raw + ' visits';
                            return label;
                        }
                    }
                }
            }
        }
    });
});
</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.1/chart.min.js"></script>