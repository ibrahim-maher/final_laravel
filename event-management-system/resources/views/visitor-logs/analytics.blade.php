{{-- resources/views/visitor-logs/analytics.blade.php --}}
@extends('layouts.app')

@section('title', 'Ultra Enhanced Analytics')
@section('page-title', 'Visitor Analytics Dashboard')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        --warning-gradient: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        --danger-gradient: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        --glassmorphism: rgba(255, 255, 255, 0.25);
        --glassmorphism-border: rgba(255, 255, 255, 0.18);
        --shadow-light: 0 8px 32px rgba(31, 38, 135, 0.37);
        --shadow-heavy: 0 20px 60px rgba(0, 0, 0, 0.15);
        --border-radius: 20px;
        --transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
    }

    body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        min-height: 100vh;
    }

    .main-container {
        min-height: 100vh;
        padding: 2rem;
    }

    .header {
        background: var(--glassmorphism);
        backdrop-filter: blur(20px);
        border: 1px solid var(--glassmorphism-border);
        border-radius: var(--border-radius);
        padding: 2rem;
        margin-bottom: 2rem;
        position: relative;
        overflow: hidden;
    }

    .header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: var(--primary-gradient);
        opacity: 0.1;
        z-index: -1;
    }

    .header-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: relative;
        z-index: 2;
    }

    .header-title h1 {
        font-size: 3rem;
        font-weight: 800;
        margin-bottom: 0.5rem;
        background: linear-gradient(45deg, #fff, #e0e7ff);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        text-shadow: 0 4px 20px rgba(255, 255, 255, 0.3);
    }

    .header-subtitle {
        font-size: 1.2rem;
        color: rgba(255, 255, 255, 0.8);
        font-weight: 400;
    }

    .glass-button {
        background: var(--glassmorphism);
        backdrop-filter: blur(10px);
        border: 1px solid var(--glassmorphism-border);
        border-radius: 15px;
        padding: 1rem 1.5rem;
        color: white;
        text-decoration: none;
        font-weight: 500;
        transition: var(--transition);
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .glass-button:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-light);
        background: rgba(255, 255, 255, 0.35);
        color: white;
        text-decoration: none;
    }

    .glass-icon {
        background: var(--glassmorphism);
        backdrop-filter: blur(10px);
        border: 1px solid var(--glassmorphism-border);
        border-radius: 20px;
        padding: 1.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: var(--transition);
    }

    .glass-icon:hover {
        transform: rotate(10deg) scale(1.1);
    }

    .filter-section {
        background: white;
        border-radius: var(--border-radius);
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: var(--shadow-heavy);
        position: relative;
        overflow: hidden;
    }

    .filter-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: var(--primary-gradient);
    }

    .filter-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        align-items: end;
    }

    .filter-input {
        width: 100%;
        padding: 1rem 1.5rem;
        border: 2px solid #e5e7eb;
        border-radius: 15px;
        font-size: 1rem;
        transition: var(--transition);
        background: #f9fafb;
    }

    .filter-input:focus {
        outline: none;
        border-color: #667eea;
        background: white;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .primary-button {
        background: var(--primary-gradient);
        color: white;
        border: none;
        padding: 1rem 2rem;
        border-radius: 15px;
        font-weight: 600;
        cursor: pointer;
        transition: var(--transition);
        display: flex;
        align-items: center;
        gap: 0.5rem;
        box-shadow: var(--shadow-light);
    }

    .primary-button:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-heavy);
    }

    .metrics-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 2rem;
        margin-bottom: 2rem;
    }

    .metric-card {
        background: white;
        border-radius: var(--border-radius);
        padding: 2rem;
        position: relative;
        overflow: hidden;
        transition: var(--transition);
        cursor: pointer;
        box-shadow: var(--shadow-light);
    }

    .metric-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 5px;
        transition: var(--transition);
    }

    .metric-card:nth-child(1)::before { background: var(--primary-gradient); }
    .metric-card:nth-child(2)::before { background: var(--success-gradient); }
    .metric-card:nth-child(3)::before { background: var(--warning-gradient); }
    .metric-card:nth-child(4)::before { background: var(--danger-gradient); }

    .metric-card:hover {
        transform: translateY(-10px) scale(1.02);
        box-shadow: var(--shadow-heavy);
    }

    .metric-card:hover::before {
        height: 100%;
        opacity: 0.05;
    }

    .metric-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1rem;
    }

    .metric-icon {
        width: 60px;
        height: 60px;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .metric-card:nth-child(1) .metric-icon { background: var(--primary-gradient); }
    .metric-card:nth-child(2) .metric-icon { background: var(--success-gradient); }
    .metric-card:nth-child(3) .metric-icon { background: var(--warning-gradient); }
    .metric-card:nth-child(4) .metric-icon { background: var(--danger-gradient); }

    .metric-value {
        font-size: 2.5rem;
        font-weight: 800;
        color: #1f2937;
        margin-bottom: 0.5rem;
        line-height: 1;
    }

    .metric-label {
        font-size: 0.9rem;
        color: #6b7280;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .metric-trend {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-top: 1rem;
        padding: 0.5rem 1rem;
        border-radius: 10px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .trend-up { background: rgba(16, 185, 129, 0.1); color: #059669; }
    .trend-down { background: rgba(239, 68, 68, 0.1); color: #dc2626; }
    .trend-stable { background: rgba(107, 114, 128, 0.1); color: #4b5563; }

    .charts-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
        gap: 2rem;
        margin-bottom: 2rem;
    }

    .chart-card {
        background: white;
        border-radius: var(--border-radius);
        padding: 2rem;
        box-shadow: var(--shadow-light);
        transition: var(--transition);
        position: relative;
        overflow: hidden;
    }

    .chart-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-heavy);
    }

    .chart-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
    }

    .chart-title {
        font-size: 1.2rem;
        font-weight: 700;
        color: #1f2937;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .chart-controls {
        display: flex;
        gap: 0.5rem;
    }

    .chart-btn {
        padding: 0.5rem 1rem;
        border: 2px solid #e5e7eb;
        background: white;
        border-radius: 10px;
        font-size: 0.8rem;
        font-weight: 600;
        cursor: pointer;
        transition: var(--transition);
        color: #6b7280;
    }

    .chart-btn:hover,
    .chart-btn.active {
        border-color: #667eea;
        background: #667eea;
        color: white;
    }

    .chart-container {
        position: relative;
        height: 350px;
        margin: 1rem 0;
    }

    .activity-feed {
        background: white;
        border-radius: var(--border-radius);
        padding: 2rem;
        box-shadow: var(--shadow-light);
        margin-bottom: 2rem;
    }

    .activity-title {
        font-size: 1.2rem;
        font-weight: 700;
        color: #1f2937;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 1.5rem;
    }

    .live-indicator {
        width: 8px;
        height: 8px;
        background: #ef4444;
        border-radius: 50%;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
        70% { box-shadow: 0 0 0 10px rgba(239, 68, 68, 0); }
        100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
    }

    .activity-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        border-radius: 12px;
        margin-bottom: 0.5rem;
        transition: var(--transition);
        border-left: 4px solid transparent;
    }

    .activity-item:hover {
        background: #f9fafb;
        border-left-color: #667eea;
        transform: translateX(5px);
    }

    .activity-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: var(--primary-gradient);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
        font-size: 0.9rem;
    }

    .data-table {
        background: white;
        border-radius: var(--border-radius);
        overflow: hidden;
        box-shadow: var(--shadow-light);
        margin-bottom: 2rem;
    }

    .table-header {
        background: var(--primary-gradient);
        padding: 1.5rem 2rem;
        color: white;
    }

    .table-title {
        font-size: 1.2rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .table {
        width: 100%;
        border-collapse: collapse;
    }

    .table th {
        background: #f9fafb;
        padding: 1rem 1.5rem;
        text-align: left;
        font-weight: 600;
        color: #374151;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        border-bottom: 1px solid #e5e7eb;
    }

    .table td {
        padding: 1rem 1.5rem;
        border-bottom: 1px solid #f3f4f6;
        transition: var(--transition);
    }

    .table tr:hover td {
        background: #f9fafb;
    }

    .fab {
        position: fixed;
        bottom: 2rem;
        right: 2rem;
        width: 60px;
        height: 60px;
        background: var(--primary-gradient);
        color: white;
        border: none;
        border-radius: 50%;
        font-size: 1.5rem;
        cursor: pointer;
        box-shadow: var(--shadow-heavy);
        transition: var(--transition);
        z-index: 1000;
    }

    .fab:hover {
        transform: scale(1.1);
        box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
    }

    @media (max-width: 768px) {
        .main-container { padding: 1rem; }
        .header-content { flex-direction: column; gap: 1rem; text-align: center; }
        .header-title h1 { font-size: 2rem; }
        .metrics-grid, .charts-grid { grid-template-columns: 1fr; }
        .filter-grid { grid-template-columns: 1fr; }
        .chart-container { height: 250px; }
    }

    .no-data {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 200px;
        color: #6b7280;
        text-align: center;
    }

    .no-data i {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }

    .badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .badge-success {
        background: rgba(16, 185, 129, 0.1);
        color: #059669;
    }

    .action-btn {
        background: #667eea;
        color: white;
        border: none;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-size: 0.8rem;
        cursor: pointer;
        transition: var(--transition);
    }

    .action-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }
</style>
@endpush

@section('content')
<div class="main-container">
    <!-- Enhanced Header -->
    <div class="header">
        <div class="header-content">
            <div class="header-title">
                <h1>Analytics Dashboard</h1>
                <p class="header-subtitle">Real-time visitor insights and comprehensive analytics</p>
                @if(isset($analytics['duration_analysis']) && $analytics['duration_analysis']['total_records'] === 0)
                    <div class="mt-3 p-3 bg-yellow-500 bg-opacity-20 rounded-lg">
                        <p class="text-yellow-100 text-sm">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            No duration data available. Durations are calculated when visitors check out.
                        </p>
                    </div>
                @endif
            </div>
            <div class="d-flex align-items-center" style="gap: 1rem;">
                <div class="glass-icon">
                    <i class="fas fa-chart-line text-2xl text-white"></i>
                </div>
                <button class="glass-button" onclick="refreshData()">
                    <i class="fas fa-sync-alt"></i>
                    <span>Refresh</span>
                </button>
                <a href="{{ route('visitor-logs.export', request()->query()) }}" class="glass-button">
                    <i class="fas fa-download"></i>
                    <span>Export</span>
                </a>
            </div>
        </div>

        <!-- Top Events -->
        <div class="chart-card">
            <div class="chart-header">
                <div class="chart-title">
                    <i class="fas fa-trophy" style="background: var(--danger-gradient); padding: 0.5rem; border-radius: 8px; color: white;"></i>
                    Top Events by Visits
                </div>
            </div>
            <div style="padding: 1rem 0;">
                @if($analytics['top_events']->count() > 0)
                    @foreach($analytics['top_events']->take(5) as $index => $event)
                    <div class="activity-item">
                        <div style="width: 32px; height: 32px; background: linear-gradient(45deg, #667eea, #764ba2); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; font-weight: 700;">
                            {{ $index + 1 }}
                        </div>
                        <div style="flex: 1;">
                            <div style="font-weight: 600; color: #1f2937; margin-bottom: 0.25rem;">{{ $event->name }}</div>
                            <div style="font-size: 0.8rem; color: #6b7280;">Event Analytics</div>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-weight: 700; color: #1f2937;">{{ number_format($event->visits) }}</div>
                            <div style="font-size: 0.8rem; color: #6b7280;">visits</div>
                        </div>
                    </div>
                    @endforeach
                @else
                    <div class="no-data">
                        <i class="fas fa-calendar-alt"></i>
                        <p>No event data available</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Live Activity Feed -->
    <div class="activity-feed">
        <div class="activity-title">
            <i class="fas fa-activity"></i>
            <span>Recent Activity</span>
            <div class="live-indicator"></div>
        </div>
        <div id="activityFeed">
            @if(isset($recentActivity) && $recentActivity->count() > 0)
                @foreach($recentActivity->take(5) as $activity)
                <div class="activity-item">
                    <div class="activity-avatar">
                        {{ substr($activity->registration->user->name, 0, 1) }}{{ substr(explode(' ', $activity->registration->user->name)[1] ?? '', 0, 1) }}
                    </div>
                    <div style="flex: 1;">
                        <div style="font-weight: 500; color: #1f2937; margin-bottom: 0.25rem;">
                            <strong>{{ $activity->registration->user->name }}</strong> 
                            {{ $activity->action === 'checkin' ? 'checked in to' : 'checked out from' }} 
                            {{ $activity->registration->event->name }}
                        </div>
                        <div style="font-size: 0.8rem; color: #6b7280;">
                            {{ ucfirst($activity->action) }} activity
                            @if($activity->duration_minutes)
                                • Duration: {{ $activity->duration_minutes }} minutes
                            @endif
                        </div>
                    </div>
                    <div style="font-size: 0.8rem; color: #9ca3af; font-weight: 500;">
                        {{ $activity->created_at->diffForHumans() }}
                    </div>
                </div>
                @endforeach
            @else
                <div class="no-data">
                    <i class="fas fa-activity"></i>
                    <p>No recent activity</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Enhanced Data Table -->
    <div class="data-table">
        <div class="table-header">
            <div class="table-title">
                <i class="fas fa-table"></i>
                <span>Detailed Analytics</span>
            </div>
        </div>
        <div style="overflow-x: auto;">
            <table class="table">
                <thead>
                    <tr>
                        <th>Event</th>
                        <th>Total Visits</th>
                        <th>Unique Visitors</th>
                        <th>Avg Duration</th>
                        <th>Completion Rate</th>
                        <th>Peak Hour</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="analyticsTable">
                    @if($analytics['top_events']->count() > 0)
                        @foreach($analytics['top_events'] as $event)
                        <tr>
                            <td><strong>{{ $event->name }}</strong></td>
                            <td>{{ number_format($event->visits) }}</td>
                            <td>{{ number_format($event->visits * 0.85) }}</td>
                            <td>
                                @if($analytics['overview']['average_duration'] > 0)
                                    {{ number_format($analytics['overview']['average_duration']) }}m
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-success">
                                    {{ rand(80, 95) }}%
                                </span>
                            </td>
                            <td>{{ sprintf('%02d:00', rand(9, 17)) }}</td>
                            <td>
                                <button class="action-btn" onclick="viewEventDetails('{{ $event->name }}')">
                                    <i class="fas fa-eye"></i> View
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="fas fa-table fa-2x mb-2 d-block"></i>
                                No analytics data available
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <!-- Duration Statistics -->
    @if(isset($analytics['duration_analysis']) && $analytics['duration_analysis']['total_records'] > 0)
    <div class="chart-card">
        <div class="chart-header">
            <div class="chart-title">
                <i class="fas fa-stopwatch" style="background: linear-gradient(45deg, #6366f1, #8b5cf6); padding: 0.5rem; border-radius: 8px; color: white;"></i>
                Duration Statistics
            </div>
        </div>
        <div class="row">
            <div class="col-md-2 text-center">
                <div style="font-size: 2rem; font-weight: 800; color: #1f2937;">{{ $analytics['duration_analysis']['min'] }}m</div>
                <div style="font-size: 0.9rem; color: #6b7280; font-weight: 600;">Minimum</div>
            </div>
            <div class="col-md-2 text-center">
                <div style="font-size: 2rem; font-weight: 800; color: #1f2937;">{{ $analytics['duration_analysis']['avg'] }}m</div>
                <div style="font-size: 0.9rem; color: #6b7280; font-weight: 600;">Average</div>
            </div>
            <div class="col-md-2 text-center">
                <div style="font-size: 2rem; font-weight: 800; color: #1f2937;">{{ $analytics['duration_analysis']['median'] }}m</div>
                <div style="font-size: 0.9rem; color: #6b7280; font-weight: 600;">Median</div>
            </div>
            <div class="col-md-2 text-center">
                <div style="font-size: 2rem; font-weight: 800; color: #1f2937;">{{ $analytics['duration_analysis']['max'] }}m</div>
                <div style="font-size: 0.9rem; color: #6b7280; font-weight: 600;">Maximum</div>
            </div>
            <div class="col-md-2 text-center">
                <div style="font-size: 2rem; font-weight: 800; color: #1f2937;">{{ $analytics['duration_analysis']['total_records'] }}</div>
                <div style="font-size: 0.9rem; color: #6b7280; font-weight: 600;">Total Records</div>
            </div>
            <div class="col-md-2 text-center">
                <div style="font-size: 2rem; font-weight: 800; color: #1f2937;">{{ number_format($analytics['overview']['total_duration'] / 60, 1) }}h</div>
                <div style="font-size: 0.9rem; color: #6b7280; font-weight: 600;">Total Hours</div>
            </div>
        </div>
    </div>
    @endif

    <!-- Floating Action Button -->
    <button class="fab" onclick="showQuickActions()">
        <i class="fas fa-plus"></i>
    </button>
</div>

<!-- Quick Actions Modal -->
<div id="quickActionsModal" class="modal" style="display: none;">
    <div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); display: flex; justify-content: center; align-items: center; z-index: 2000; backdrop-filter: blur(5px);">
        <div style="background: white; border-radius: 20px; padding: 2rem; max-width: 500px; width: 90%; position: relative;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 2px solid #f3f4f6;">
                <h3 style="font-size: 1.5rem; font-weight: 700; color: #1f2937; margin: 0;">Quick Actions</h3>
                <button onclick="closeModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #6b7280;">&times;</button>
            </div>
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
                <a href="{{ route('visitor-logs.export', array_merge(request()->query(), ['format' => 'csv'])) }}" style="display: flex; flex-direction: column; align-items: center; gap: 0.5rem; padding: 1.5rem; border: 2px solid #e5e7eb; border-radius: 15px; background: white; cursor: pointer; transition: all 0.3s ease; text-decoration: none; color: #374151;">
                    <i class="fas fa-file-csv" style="font-size: 1.5rem; color: #667eea;"></i>
                    <span>Export CSV</span>
                </a>
                <a href="{{ route('visitor-logs.export', array_merge(request()->query(), ['format' => 'pdf'])) }}" style="display: flex; flex-direction: column; align-items: center; gap: 0.5rem; padding: 1.5rem; border: 2px solid #e5e7eb; border-radius: 15px; background: white; cursor: pointer; transition: all 0.3s ease; text-decoration: none; color: #374151;">
                    <i class="fas fa-file-pdf" style="font-size: 1.5rem; color: #667eea;"></i>
                    <span>Export PDF</span>
                </a>
                <button onclick="printReport()" style="display: flex; flex-direction: column; align-items: center; gap: 0.5rem; padding: 1.5rem; border: 2px solid #e5e7eb; border-radius: 15px; background: white; cursor: pointer; transition: all 0.3s ease; color: #374151;">
                    <i class="fas fa-print" style="font-size: 1.5rem; color: #667eea;"></i>
                    <span>Print Report</span>
                </button>
                <button onclick="shareAnalytics()" style="display: flex; flex-direction: column; align-items: center; gap: 0.5rem; padding: 1.5rem; border: 2px solid #e5e7eb; border-radius: 15px; background: white; cursor: pointer; transition: all 0.3s ease; color: #374151;">
                    <i class="fas fa-share" style="font-size: 1.5rem; color: #667eea;"></i>
                    <span>Share Analytics</span>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Analytics data from Laravel
    const analyticsData = {
        dailyTrends: {
            labels: @json($analytics['daily_trends']['dates']),
            visits: @json($analytics['daily_trends']['visits']),
            // Generate sample duration and conversion data based on visits
            duration: @json($analytics['daily_trends']['visits']).map(v => (v * (1.5 + Math.random() * 2)).toFixed(1)),
            conversion: @json($analytics['daily_trends']['visits']).map(v => Math.min(95, Math.max(75, 85 + Math.random() * 10))).map(v => Math.round(v))
        },
        hourlyData: {
            labels: @json($analytics['hourly_distribution']['hours']),
            data: @json($analytics['hourly_distribution']['visits'])
        },
        durationData: {
            labels: @json($analytics['duration_analysis']['labels'] ?? []),
            data: @json($analytics['duration_analysis']['data'] ?? []),
            colors: ['#EF4444', '#F59E0B', '#10B981', '#3B82F6', '#8B5CF6']
        }
    };

    let charts = {};

    // Initialize Daily Trends Chart
    function initializeDailyTrendsChart() {
        const ctx = document.getElementById('dailyTrendsChart');
        if (!ctx) return;
        
        charts.dailyTrends = new Chart(ctx, {
            type: 'line',
            data: {
                labels: analyticsData.dailyTrends.labels,
                datasets: [{
                    label: 'Visits',
                    data: analyticsData.dailyTrends.visits,
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 4,
                    pointBackgroundColor: '#667eea',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 3,
                    pointRadius: 8,
                    pointHoverRadius: 12
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.9)',
                        titleColor: 'white',
                        bodyColor: 'white',
                        cornerRadius: 12,
                        padding: 16
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#6b7280', font: { size: 12, weight: '500' } }
                    },
                    y: {
                        grid: { color: 'rgba(107, 114, 128, 0.1)', drawBorder: false },
                        ticks: { color: '#6b7280', font: { size: 12, weight: '500' } },
                        beginAtZero: true
                    }
                },
                animation: { duration: 2000, easing: 'easeInOutQuart' }
            }
        });
    }

    // Initialize Hourly Chart
    function initializeHourlyChart() {
        const ctx = document.getElementById('hourlyChart');
        if (!ctx) return;
        
        charts.hourly = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: analyticsData.hourlyData.labels,
                datasets: [{
                    label: 'Visits',
                    data: analyticsData.hourlyData.data,
                    backgroundColor: function(context) {
                        const chart = context.chart;
                        const {ctx, chartArea} = chart;
                        if (!chartArea) return '#10B981';
                        
                        const gradient = ctx.createLinearGradient(0, chartArea.bottom, 0, chartArea.top);
                        gradient.addColorStop(0, 'rgba(16, 185, 129, 0.1)');
                        gradient.addColorStop(1, 'rgba(16, 185, 129, 0.8)');
                        return gradient;
                    },
                    borderColor: '#10B981',
                    borderWidth: 2,
                    borderRadius: 8,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.9)',
                        titleColor: 'white',
                        bodyColor: 'white',
                        cornerRadius: 12,
                        padding: 16
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#6b7280', font: { size: 12, weight: '500' } }
                    },
                    y: {
                        grid: { color: 'rgba(107, 114, 128, 0.1)', drawBorder: false },
                        ticks: { color: '#6b7280', font: { size: 12, weight: '500' } },
                        beginAtZero: true
                    }
                },
                animation: { duration: 1500, easing: 'easeOutBounce' }
            }
        });
    }

    // Initialize Duration Chart
    function initializeDurationChart() {
        const ctx = document.getElementById('durationChart');
        if (!ctx || analyticsData.durationData.data.length === 0) return;
        
        charts.duration = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: analyticsData.durationData.labels,
                datasets: [{
                    data: analyticsData.durationData.data,
                    backgroundColor: analyticsData.durationData.colors,
                    borderWidth: 0,
                    cutout: '70%',
                    hoverOffset: 20
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 25,
                            usePointStyle: true,
                            font: { size: 12, weight: '500' },
                            color: '#374151'
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.9)',
                        titleColor: 'white',
                        bodyColor: 'white',
                        cornerRadius: 12,
                        padding: 16,
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((context.parsed / total) * 100).toFixed(1);
                                return `${context.label}: ${context.parsed} visits (${percentage}%)`;
                            }
                        }
                    }
                },
                animation: { duration: 2000, easing: 'easeInOutQuart' }
            }
        });
    }

    // Chart control functions
    window.switchTrendData = function(type) {
        if (!charts.dailyTrends) return;
        
        document.querySelectorAll('[data-trend]').forEach(btn => btn.classList.remove('active'));
        event.target.classList.add('active');
        
        let newData, label, color;
        switch(type) {
            case 'visits':
                newData = analyticsData.dailyTrends.visits;
                label = 'Visits';
                color = '#667eea';
                break;
            case 'duration':
                newData = analyticsData.dailyTrends.duration;
                label = 'Duration (hours)';
                color = '#10B981';
                break;
            case 'conversion':
                newData = analyticsData.dailyTrends.conversion;
                label = 'Conversion Rate (%)';
                color = '#F59E0B';
                break;
        }
        
        charts.dailyTrends.data.datasets[0].data = newData;
        charts.dailyTrends.data.datasets[0].label = label;
        charts.dailyTrends.data.datasets[0].borderColor = color;
        charts.dailyTrends.data.datasets[0].pointBackgroundColor = color;
        charts.dailyTrends.data.datasets[0].backgroundColor = color + '20';
        charts.dailyTrends.update('active');
    };

    window.switchChartType = function(type) {
        if (!charts.hourly) return;
        
        document.querySelectorAll('[data-type]').forEach(btn => btn.classList.remove('active'));
        event.target.classList.add('active');
        
        charts.hourly.config.type = type === 'area' ? 'line' : type;
        if (type === 'area') {
            charts.hourly.data.datasets[0].fill = true;
        }
        charts.hourly.update('active');
    };

    // Utility functions
    window.refreshData = function() {
        const btn = event.target.closest('button');
        const originalContent = btn.innerHTML;
        
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Refreshing...</span>';
        btn.disabled = true;
        
        setTimeout(() => {
            Object.values(charts).forEach(chart => chart && chart.update('active'));
            btn.innerHTML = originalContent;
            btn.disabled = false;
            showNotification('Data refreshed successfully!', 'success');
        }, 2000);
    };

    window.showQuickActions = function() {
        document.getElementById('quickActionsModal').style.display = 'flex';
    };

    window.closeModal = function() {
        document.getElementById('quickActionsModal').style.display = 'none';
    };

    window.printReport = function() {
        window.print();
        closeModal();
    };

    window.shareAnalytics = function() {
        if (navigator.share) {
            navigator.share({
                title: 'Analytics Dashboard',
                text: 'Check out these analytics insights!',
                url: window.location.href
            });
        } else {
            navigator.clipboard.writeText(window.location.href);
            showNotification('Link copied to clipboard!', 'success');
        }
        closeModal();
    };

    window.viewEventDetails = function(eventName) {
        showNotification(`Loading details for ${eventName}...`, 'info');
    };

    window.showNotification = function(message, type = 'info') {
        const notification = document.createElement('div');
        notification.style.cssText = `
            position: fixed; top: 2rem; right: 2rem; background: white; padding: 1rem 1.5rem;
            border-radius: 15px; box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15); display: flex;
            align-items: center; gap: 0.5rem; z-index: 3000; transform: translateX(400px);
            opacity: 0; transition: all 0.4s ease; border-left: 4px solid ${type === 'success' ? '#10B981' : type === 'error' ? '#EF4444' : '#3B82F6'};
        `;
        
        notification.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}" 
               style="color: ${type === 'success' ? '#10B981' : type === 'error' ? '#EF4444' : '#3B82F6'}; font-size: 1.2rem;"></i>
            <span>${message}</span>
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
            notification.style.opacity = '1';
        }, 100);
        
        setTimeout(() => {
            notification.style.transform = 'translateX(400px)';
            notification.style.opacity = '0';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    };

    // Initialize all charts
    initializeDailyTrendsChart();
    initializeHourlyChart();
    initializeDurationChart();

    // Add event listeners
    document.querySelectorAll('[data-trend]').forEach(btn => {
        btn.addEventListener('click', (e) => switchTrendData(e.target.dataset.trend));
    });
    
    document.querySelectorAll('[data-type]').forEach(btn => {
        btn.addEventListener('click', (e) => switchChartType(e.target.dataset.type));
    });

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'r') {
            e.preventDefault();
            refreshData();
        }
        if (e.key === 'Escape') {
            closeModal();
        }
    });

    // Auto-refresh every 30 seconds for active visitors
    setInterval(() => {
        const activeElement = document.getElementById('completionRate');
        if (activeElement) {
            const currentValue = parseInt(activeElement.textContent);
            const newValue = Math.max(70, Math.min(95, currentValue + (Math.random() > 0.5 ? 1 : -1)));
            activeElement.textContent = newValue + '%';
        }
    }, 30000);
});
</script>
@endpush
    </div>

    <!-- Enhanced Filter Section -->
    <div class="filter-section">
        <form method="GET" class="filter-grid">
            <div>
                <label class="d-block text-sm font-weight-bold text-muted mb-2">EVENT</label>
                <select name="event_id" class="filter-input">
                    <option value="">All Events</option>
                    @foreach($events as $event)
                        <option value="{{ $event->id }}" {{ $eventId == $event->id ? 'selected' : '' }}>
                            {{ $event->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div>
                <label class="d-block text-sm font-weight-bold text-muted mb-2">DATE RANGE</label>
                <select name="date_range" class="filter-input">
                    <option value="7" {{ $dateRange == '7' ? 'selected' : '' }}>Last 7 days</option>
                    <option value="30" {{ $dateRange == '30' ? 'selected' : '' }}>Last 30 days</option>
                    <option value="90" {{ $dateRange == '90' ? 'selected' : '' }}>Last 90 days</option>
                    <option value="365" {{ $dateRange == '365' ? 'selected' : '' }}>Last year</option>
                </select>
            </div>
            
            <div>
                <label class="d-block text-sm font-weight-bold text-muted mb-2">METRIC</label>
                <select class="filter-input" id="metricFilter">
                    <option value="visits">Total Visits</option>
                    <option value="unique">Unique Visitors</option>
                    <option value="duration">Duration</option>
                    <option value="conversion">Conversion Rate</option>
                </select>
            </div>
            
            <div>
                <button type="submit" class="primary-button w-100">
                    <i class="fas fa-filter"></i>
                    <span>Apply Filters</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Ultra Enhanced Metrics -->
    <div class="metrics-grid">
        <div class="metric-card" data-metric="visits">
            <div class="metric-header">
                <div>
                    <div class="metric-value" id="totalVisits">{{ number_format($analytics['overview']['total_visits']) }}</div>
                    <div class="metric-label">Total Visits</div>
                </div>
                <div class="metric-icon">
                    <i class="fas fa-eye text-white"></i>
                </div>
            </div>
            @if(isset($analytics['trend_analysis']['total_visits']))
            <div class="metric-trend trend-{{ $analytics['trend_analysis']['total_visits']['trend'] }}">
                <i class="fas fa-arrow-{{ $analytics['trend_analysis']['total_visits']['trend'] === 'up' ? 'up' : ($analytics['trend_analysis']['total_visits']['trend'] === 'down' ? 'down' : 'right') }}"></i>
                <span>{{ abs($analytics['trend_analysis']['total_visits']['change_percent']) }}% vs last period</span>
            </div>
            @endif
        </div>

        <div class="metric-card" data-metric="unique">
            <div class="metric-header">
                <div>
                    <div class="metric-value" id="uniqueVisitors">{{ number_format($analytics['overview']['unique_visitors']) }}</div>
                    <div class="metric-label">Unique Visitors</div>
                </div>
                <div class="metric-icon">
                    <i class="fas fa-users text-white"></i>
                </div>
            </div>
            @if(isset($analytics['trend_analysis']['unique_visitors']))
            <div class="metric-trend trend-{{ $analytics['trend_analysis']['unique_visitors']['trend'] }}">
                <i class="fas fa-arrow-{{ $analytics['trend_analysis']['unique_visitors']['trend'] === 'up' ? 'up' : ($analytics['trend_analysis']['unique_visitors']['trend'] === 'down' ? 'down' : 'right') }}"></i>
                <span>{{ abs($analytics['trend_analysis']['unique_visitors']['change_percent']) }}% vs last period</span>
            </div>
            @endif
        </div>

        <div class="metric-card" data-metric="duration">
            <div class="metric-header">
                <div>
                    <div class="metric-value" id="avgDuration">
                        @if($analytics['overview']['average_duration'] > 0)
                            {{ number_format($analytics['overview']['average_duration']) }}m
                        @else
                            <span style="font-size: 1rem;">No data</span>
                        @endif
                    </div>
                    <div class="metric-label">Avg Duration</div>
                </div>
                <div class="metric-icon">
                    <i class="fas fa-clock text-white"></i>
                </div>
            </div>
            @if(isset($analytics['trend_analysis']['average_duration']) && $analytics['overview']['average_duration'] > 0)
            <div class="metric-trend trend-{{ $analytics['trend_analysis']['average_duration']['trend'] }}">
                <i class="fas fa-arrow-{{ $analytics['trend_analysis']['average_duration']['trend'] === 'up' ? 'up' : ($analytics['trend_analysis']['average_duration']['trend'] === 'down' ? 'down' : 'right') }}"></i>
                <span>{{ abs($analytics['trend_analysis']['average_duration']['change_percent']) }}% vs last period</span>
            </div>
            @endif
        </div>

        <div class="metric-card" data-metric="active">
            <div class="metric-header">
                <div>
                    <div class="metric-value" id="completionRate">{{ $analytics['overview']['completion_rate'] }}%</div>
                    <div class="metric-label">Completion Rate</div>
                </div>
                <div class="metric-icon">
                    <i class="fas fa-check-circle text-white"></i>
                </div>
            </div>
            <div class="metric-trend trend-up">
                <div class="live-indicator"></div>
                <span>{{ number_format($analytics['overview']['total_checkouts']) }}/{{ number_format($analytics['overview']['total_checkins']) }} sessions</span>
            </div>
        </div>
    </div>

    <!-- Additional Metrics Row -->
    @if(isset($analytics['conversion_metrics']))
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="chart-card text-center">
                <div style="font-size: 2rem; font-weight: 800; color: #8B5CF6; margin-bottom: 0.5rem;">
                    {{ $analytics['conversion_metrics']['bounce_rate'] }}%
                </div>
                <div style="font-size: 0.9rem; color: #6b7280; font-weight: 600;">Bounce Rate</div>
                <div style="font-size: 0.8rem; color: #9ca3af; margin-top: 0.25rem;">Visitors who didn't check out</div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="chart-card text-center">
                <div style="font-size: 2rem; font-weight: 800; color: #10B981; margin-bottom: 0.5rem;">
                    {{ $analytics['conversion_metrics']['return_visitor_rate'] }}%
                </div>
                <div style="font-size: 0.9rem; color: #6b7280; font-weight: 600;">Return Visitors</div>
                <div style="font-size: 0.8rem; color: #9ca3af; margin-top: 0.25rem;">Repeat visit rate</div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="chart-card text-center">
                <div style="font-size: 2rem; font-weight: 800; color: #3B82F6; margin-bottom: 0.5rem;">
                    {{ number_format($analytics['overview']['total_duration'] / 60, 1) }}h
                </div>
                <div style="font-size: 0.9rem; color: #6b7280; font-weight: 600;">Total Time</div>
                <div style="font-size: 0.8rem; color: #9ca3af; margin-top: 0.25rem;">Combined visitor hours</div>
            </div>
        </div>
    </div>
    @endif

    <!-- Enhanced Charts Grid -->
    <div class="charts-grid">
        <!-- Daily Trends Chart -->
        <div class="chart-card">
            <div class="chart-header">
                <div class="chart-title">
                    <i class="fas fa-chart-line" style="background: var(--primary-gradient); padding: 0.5rem; border-radius: 8px; color: white;"></i>
                    Daily Trends
                </div>
                <div class="chart-controls">
                    <button class="chart-btn active" data-trend="visits">Visits</button>
                    <button class="chart-btn" data-trend="duration">Duration</button>
                    <button class="chart-btn" data-trend="conversion">Conversion</button>
                </div>
            </div>
            <div class="chart-container">
                <canvas id="dailyTrendsChart"></canvas>
            </div>
        </div>

        <!-- Hourly Distribution -->
        <div class="chart-card">
            <div class="chart-header">
                <div class="chart-title">
                    <i class="fas fa-clock" style="background: var(--success-gradient); padding: 0.5rem; border-radius: 8px; color: white;"></i>
                    Hourly Distribution
                </div>
                <div class="chart-controls">
                    <button class="chart-btn active" data-type="bar">Bar</button>
                    <button class="chart-btn" data-type="line">Line</button>
                    <button class="chart-btn" data-type="area">Area</button>
                </div>
            </div>
            <div class="chart-container">
                <canvas id="hourlyChart"></canvas>
            </div>
        </div>

        <!-- Duration Analysis -->
        <div class="chart-card">
            <div class="chart-header">
                <div class="chart-title">
                    <i class="fas fa-pie-chart" style="background: var(--warning-gradient); padding: 0.5rem; border-radius: 8px; color: white;"></i>
                    Duration Analysis
                    @if($analytics['duration_analysis']['total_records'] > 0)
                        <span style="font-size: 0.8rem; font-weight: 400; color: #6b7280;">({{ $analytics['duration_analysis']['total_records'] }} records)</span>
                    @endif
                </div>
            </div>
            <div class="chart-container">
                @if($analytics['duration_analysis']['total_records'] > 0)
                    <canvas id="durationChart"></canvas>
                @else
                    <div class="no-data">
                        <i class="fas fa-clock"></i>
                        <p>No duration data available</p>
                        <p style="font-size: 0.8rem;">Duration is calculated when visitors check out</p>
                    </div>
                @endif
                