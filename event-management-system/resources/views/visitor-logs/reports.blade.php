{{-- resources/views/visitor-logs/reports.blade.php --}}
@extends('layouts.app')

@section('title', 'Visitor Reports')
@section('page-title', 'Comprehensive Reports')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-green-600 to-teal-600 rounded-xl shadow-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold mb-2">Visitor Reports</h2>
                <p class="text-green-100">Generate detailed reports and insights</p>
            </div>
            <div class="flex items-center space-x-4">
                <i class="fas fa-file-chart-pie text-4xl text-green-200"></i>
            </div>
        </div>
    </div>

    <!-- Report Configuration -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Report Configuration</h3>
        
        <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Report Type</label>
                <select name="type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                    <option value="summary" {{ $reportType == 'summary' ? 'selected' : '' }}>Summary Report</option>
                    <option value="detailed" {{ $reportType == 'detailed' ? 'selected' : '' }}>Detailed Report</option>
                    <option value="attendance" {{ $reportType == 'attendance' ? 'selected' : '' }}>Attendance Report</option>
                    <option value="duration" {{ $reportType == 'duration' ? 'selected' : '' }}>Duration Analysis</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Event</label>
                <select name="event_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                    <option value="">All Events</option>
                    @foreach($events as $event)
                        <option value="{{ $event->id }}" {{ $eventId == $event->id ? 'selected' : '' }}>
                            {{ $event->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Date From</label>
                <input type="date" name="date_from" value="{{ $dateFrom }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Date To</label>
                <input type="date" name="date_to" value="{{ $dateTo }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
            </div>
            
            <div class="flex items-end">
                <button type="submit" class="w-full px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                    <i class="fas fa-chart-bar mr-2"></i>Generate Report
                </button>
            </div>
        </form>
    </div>

    <!-- Report Content Based on Type -->
    @if($reportType == 'summary')
        @include('visitor-logs.reports.summary', ['data' => $reportData])
    @elseif($reportType == 'detailed')
        @include('visitor-logs.reports.detailed', ['data' => $reportData])
    @elseif($reportType == 'attendance')
        @include('visitor-logs.reports.attendance', ['data' => $reportData])
    @elseif($reportType == 'duration')
        @include('visitor-logs.reports.duration', ['data' => $reportData])
    @endif

    <!-- Export Options -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-bold text-gray-900">Export Report</h3>
            <div class="flex items-center space-x-3">
                <button onclick="exportReport('pdf')" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                    <i class="fas fa-file-pdf mr-2"></i>Export PDF
                </button>
                <button onclick="exportReport('excel')" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                    <i class="fas fa-file-excel mr-2"></i>Export Excel
                </button>
                <button onclick="exportReport('csv')" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-file-csv mr-2"></i>Export CSV
                </button>
                <button onclick="printReport()" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                    <i class="fas fa-print mr-2"></i>Print
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function exportReport(format) {
    const params = new URLSearchParams(window.location.search);
    params.set('format', format);
    params.set('export', 'report');
    
    window.open(`/visitor-logs/export?${params.toString()}`, '_blank');
}

function printReport() {
    window.print();
}
</script>
@endpush