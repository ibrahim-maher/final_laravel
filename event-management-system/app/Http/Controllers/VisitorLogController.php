<?php

namespace App\Http\Controllers;

use App\Models\VisitorLog;
use App\Models\Event;
use App\Models\User;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class VisitorLogController extends Controller
{
    /**
     * Display visitor logs with advanced filtering
     */
    public function index(Request $request)
    {
        $query = VisitorLog::with(['registration.user', 'registration.event', 'creator']);

        // Apply filters
        $this->applyFilters($query, $request);

        // Sort
        $sortBy = $request->input('sort', 'created_at');
        $sortDirection = $request->input('direction', 'desc');
        $query->orderBy($sortBy, $sortDirection);

        // Paginate
        $perPage = $request->input('per_page', 20);
        $logs = $query->paginate($perPage)->withQueryString();

        // Get filter options
        $events = Event::orderBy('name')->get();
        $creators = User::whereHas('created_logs')->orderBy('name')->get();

        // Get statistics
        $stats = $this->getFilteredStatistics($request);

        // Get analytics data
        $analytics = $this->getAnalyticsData($request);

        return view('visitor-logs.index', compact(
            'logs', 'events', 'creators', 'stats', 'analytics'
        ));
    }

    /**
     * Show detailed visitor log
     */
    public function show(VisitorLog $visitorLog)
    {
        $visitorLog->load(['registration.user', 'registration.event', 'creator']);
        
        // Get related logs for this registration
        $relatedLogs = VisitorLog::where('registration_id', $visitorLog->registration_id)
            ->where('id', '!=', $visitorLog->id)
            ->with(['creator'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Get registration timeline
        $timeline = $this->getRegistrationTimeline($visitorLog->registration_id);

        return view('visitor-logs.show', compact('visitorLog', 'relatedLogs', 'timeline'));
    }

    /**
     * Analytics dashboard
     */
    public function analytics(Request $request)
    {
        $eventId = $request->input('event_id');
        $dateRange = $request->input('date_range', '7'); // days
        
        $dateFrom = now()->subDays($dateRange)->startOfDay();
        $dateTo = now()->endOfDay();

        $analytics = [
            'overview' => $this->getOverviewAnalytics($eventId, $dateFrom, $dateTo),
            'hourly_distribution' => $this->getHourlyDistribution($eventId, $dateFrom, $dateTo),
            'daily_trends' => $this->getDailyTrends($eventId, $dateFrom, $dateTo),
            'duration_analysis' => $this->getDurationAnalysis($eventId, $dateFrom, $dateTo),
            'top_events' => $this->getTopEvents($dateFrom, $dateTo),
            'peak_times' => $this->getPeakTimes($eventId, $dateFrom, $dateTo),
            'visitor_patterns' => $this->getVisitorPatterns($eventId, $dateFrom, $dateTo)
        ];

        $events = Event::orderBy('name')->get();

        return view('visitor-logs.analytics', compact('analytics', 'events', 'eventId', 'dateRange'));
    }

    /**
     * Real-time dashboard
     */
    public function realtime(Request $request)
    {
        $eventId = $request->input('event_id');
        
        $data = [
            'active_visitors' => $this->getActiveVisitorsData($eventId),
            'recent_activity' => $this->getRecentActivity($eventId, 20),
            'live_stats' => $this->getLiveStats($eventId),
            'hourly_checkins' => $this->getTodayHourlyCheckins($eventId)
        ];

        if ($request->expectsJson()) {
            return response()->json($data);
        }

        $events = Event::where('is_active', true)->orderBy('name')->get();
        return view('visitor-logs.realtime', compact('data', 'events', 'eventId'));
    }

    /**
     * Export filtered logs
     */
    public function export(Request $request)
    {
        $query = VisitorLog::with(['registration.user', 'registration.event', 'creator']);
        $this->applyFilters($query, $request);

        $format = $request->input('format', 'csv');
        $logs = $query->orderBy('created_at', 'desc')->get();

        $filename = 'visitor_logs_' . now()->format('Y_m_d_H_i_s');

        switch ($format) {
            case 'csv':
                return $this->exportToCsv($logs, $filename);
            case 'excel':
                return $this->exportToExcel($logs, $filename);
            case 'pdf':
                return $this->exportToPdf($logs, $filename);
            default:
                return $this->exportToCsv($logs, $filename);
        }
    }

    /**
     * Generate reports
     */
    public function reports(Request $request)
    {
        $reportType = $request->input('type', 'summary');
        $eventId = $request->input('event_id');
        $dateFrom = $request->input('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));

        $reportData = match($reportType) {
            'summary' => $this->generateSummaryReport($eventId, $dateFrom, $dateTo),
            'detailed' => $this->generateDetailedReport($eventId, $dateFrom, $dateTo),
            'attendance' => $this->generateAttendanceReport($eventId, $dateFrom, $dateTo),
            'duration' => $this->generateDurationReport($eventId, $dateFrom, $dateTo),
            default => $this->generateSummaryReport($eventId, $dateFrom, $dateTo)
        };

        $events = Event::orderBy('name')->get();

        return view('visitor-logs.reports', compact(
            'reportData', 'events', 'reportType', 'eventId', 'dateFrom', 'dateTo'
        ));
    }

    /**
     * Visitor timeline for specific user/registration
     */
    public function visitorTimeline(Request $request)
    {
        $userId = $request->input('user_id');
        $registrationId = $request->input('registration_id');
        $eventId = $request->input('event_id');

        $query = VisitorLog::with(['registration.user', 'registration.event', 'creator']);

        if ($userId) {
            $query->whereHas('registration', function($q) use ($userId) {
                $q->where('user_id', $userId);
            });
        }

        if ($registrationId) {
            $query->where('registration_id', $registrationId);
        }

        if ($eventId) {
            $query->whereHas('registration', function($q) use ($eventId) {
                $q->where('event_id', $eventId);
            });
        }

        $timeline = $query->orderBy('created_at', 'desc')
                         ->paginate(50)
                         ->withQueryString();

        $events = Event::orderBy('name')->get();
        $users = User::whereHas('registrations.visitorLogs')
                    ->orderBy('name')
                    ->get();

        return view('visitor-logs.timeline', compact(
            'timeline', 'events', 'users', 'userId', 'registrationId', 'eventId'
        ));
    }

    /**
     * API endpoint for mobile app
     */
    public function apiLogs(Request $request)
    {
        $query = VisitorLog::with(['registration.user', 'registration.event']);

        // Apply filters for API
        if ($request->event_id) {
            $query->whereHas('registration', function($q) use ($request) {
                $q->where('event_id', $request->event_id);
            });
        }

        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->action) {
            $query->where('action', $request->action);
        }

        $logs = $query->orderBy('created_at', 'desc')
                     ->paginate($request->input('per_page', 20))
                     ->withQueryString();

        return response()->json([
            'data' => $logs->items(),
            'pagination' => [
                'current_page' => $logs->currentPage(),
                'last_page' => $logs->lastPage(),
                'per_page' => $logs->perPage(),
                'total' => $logs->total()
            ],
            'stats' => VisitorLog::getStatistics($request->event_id)
        ]);
    }

    // Private helper methods

    private function applyFilters($query, Request $request)
    {
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('registration.user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('event_id')) {
            $query->whereHas('registration', function($q) use ($request) {
                $q->where('event_id', $request->event_id);
            });
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('created_by')) {
            $query->where('created_by', $request->created_by);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('qr_scanned')) {
            $query->where('qr_scanned', $request->qr_scanned === '1');
        }

        if ($request->filled('has_duration')) {
            if ($request->has_duration === '1') {
                $query->whereNotNull('duration_minutes');
            } else {
                $query->whereNull('duration_minutes');
            }
        }

        if ($request->filled('min_duration')) {
            $query->where('duration_minutes', '>=', $request->min_duration);
        }

        if ($request->filled('max_duration')) {
            $query->where('duration_minutes', '<=', $request->max_duration);
        }
    }

    private function getFilteredStatistics(Request $request)
    {
        $cacheKey = 'filtered_stats_' . md5(serialize($request->all()));
        
        return Cache::remember($cacheKey, 300, function() use ($request) {
            $query = VisitorLog::query();
            $this->applyFilters($query, $request);

            $checkins = (clone $query)->where('action', 'checkin')->count();
            $checkouts = (clone $query)->where('action', 'checkout')->count();
            $totalDuration = (clone $query)->where('action', 'checkout')
                                          ->whereNotNull('duration_minutes')
                                          ->sum('duration_minutes');
            $avgDuration = (clone $query)->where('action', 'checkout')
                                        ->whereNotNull('duration_minutes')
                                        ->avg('duration_minutes');

            return [
                'total_checkins' => $checkins,
                'total_checkouts' => $checkouts,
                'total_duration' => $totalDuration,
                'average_duration' => round($avgDuration ?? 0, 2),
                'active_visitors' => $checkins - $checkouts,
                'completion_rate' => $checkins > 0 ? round(($checkouts / $checkins) * 100, 2) : 0
            ];
        });
    }

    private function getAnalyticsData(Request $request)
    {
        $eventId = $request->input('event_id');
        $cacheKey = "analytics_data_{$eventId}_" . now()->format('Y-m-d-H');

        return Cache::remember($cacheKey, 600, function() use ($eventId) {
            return [
                'hourly_distribution' => VisitorLog::getHourlyDistribution($eventId),
                'peak_hours' => VisitorLog::getPeakHours($eventId),
                'daily_trends' => $this->getDailyTrends($eventId, now()->subDays(7), now())
            ];
        });
    }

    private function getOverviewAnalytics($eventId, $dateFrom, $dateTo)
    {
        $query = VisitorLog::query();
        
        if ($eventId) {
            $query->whereHas('registration', function($q) use ($eventId) {
                $q->where('event_id', $eventId);
            });
        }

        $query->whereBetween('created_at', [$dateFrom, $dateTo]);

        return [
            'total_visits' => (clone $query)->count(),
            'unique_visitors' => (clone $query)->distinct('registration_id')->count(),
            'total_checkins' => (clone $query)->where('action', 'checkin')->count(),
            'total_checkouts' => (clone $query)->where('action', 'checkout')->count(),
            'average_duration' => (clone $query)->where('action', 'checkout')
                                               ->whereNotNull('duration_minutes')
                                               ->avg('duration_minutes'),
            'total_duration' => (clone $query)->where('action', 'checkout')
                                             ->whereNotNull('duration_minutes')
                                             ->sum('duration_minutes')
        ];
    }

    private function getDailyTrends($eventId, $dateFrom, $dateTo)
    {
        $query = VisitorLog::selectRaw('DATE(created_at) as date, action, COUNT(*) as count')
                          ->whereBetween('created_at', [$dateFrom, $dateTo])
                          ->groupBy('date', 'action')
                          ->orderBy('date');

        if ($eventId) {
            $query->whereHas('registration', function($q) use ($eventId) {
                $q->where('event_id', $eventId);
            });
        }

        return $query->get()->groupBy('date');
    }

    private function getHourlyDistribution($eventId, $dateFrom, $dateTo)
    {
        $query = VisitorLog::selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
                          ->whereBetween('created_at', [$dateFrom, $dateTo])
                          ->where('action', 'checkin')
                          ->groupBy('hour')
                          ->orderBy('hour');

        if ($eventId) {
            $query->whereHas('registration', function($q) use ($eventId) {
                $q->where('event_id', $eventId);
            });
        }

        $data = $query->get()->keyBy('hour');
        
        $distribution = [];
        for ($i = 0; $i < 24; $i++) {
            $distribution[$i] = $data->get($i)?->count ?? 0;
        }

        return $distribution;
    }

    private function getDurationAnalysis($eventId, $dateFrom, $dateTo)
    {
        $query = VisitorLog::where('action', 'checkout')
                          ->whereNotNull('duration_minutes')
                          ->whereBetween('created_at', [$dateFrom, $dateTo]);

        if ($eventId) {
            $query->whereHas('registration', function($q) use ($eventId) {
                $q->where('event_id', $eventId);
            });
        }

        $durations = $query->pluck('duration_minutes');

        return [
            'min' => $durations->min(),
            'max' => $durations->max(),
            'avg' => $durations->avg(),
            'median' => $durations->median(),
            'ranges' => [
                '0-30' => $durations->filter(fn($d) => $d <= 30)->count(),
                '31-60' => $durations->filter(fn($d) => $d > 30 && $d <= 60)->count(),
                '61-120' => $durations->filter(fn($d) => $d > 60 && $d <= 120)->count(),
                '121+' => $durations->filter(fn($d) => $d > 120)->count(),
            ]
        ];
    }

    private function getTopEvents($dateFrom, $dateTo)
    {
        return VisitorLog::selectRaw('event_id, COUNT(*) as visit_count')
                        ->join('registrations', 'visitor_logs.registration_id', '=', 'registrations.id')
                        ->join('events', 'registrations.event_id', '=', 'events.id')
                        ->whereBetween('visitor_logs.created_at', [$dateFrom, $dateTo])
                        ->groupBy('event_id')
                        ->orderBy('visit_count', 'desc')
                        ->limit(10)
                        ->with('registration.event')
                        ->get();
    }

    private function getPeakTimes($eventId, $dateFrom, $dateTo)
    {
        return VisitorLog::getPeakHours($eventId, $dateFrom->format('Y-m-d'), $dateTo->format('Y-m-d'));
    }

    private function getVisitorPatterns($eventId, $dateFrom, $dateTo)
    {
        $query = VisitorLog::selectRaw('
                    registration_id,
                    COUNT(*) as visit_frequency,
                    AVG(duration_minutes) as avg_duration,
                    MIN(created_at) as first_visit,
                    MAX(created_at) as last_visit
                ')
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->groupBy('registration_id');

        if ($eventId) {
            $query->whereHas('registration', function($q) use ($eventId) {
                $q->where('event_id', $eventId);
            });
        }

        return $query->get();
    }

    private function getActiveVisitorsData($eventId)
    {
        $query = Registration::whereHas('visitorLogs', function($q) {
            $q->where('action', 'checkin')
              ->whereNotExists(function($subQ) {
                  $subQ->select(DB::raw(1))
                       ->from('visitor_logs as vl2')
                       ->whereColumn('vl2.registration_id', 'visitor_logs.registration_id')
                       ->where('vl2.action', 'checkout')
                       ->where('vl2.created_at', '>', DB::raw('visitor_logs.created_at'));
              });
        })->with(['user', 'event']);

        if ($eventId) {
            $query->where('event_id', $eventId);
        }

        return $query->get()->map(function($registration) {
            $lastCheckin = $registration->visitorLogs()
                                       ->where('action', 'checkin')
                                       ->latest()
                                       ->first();
            
            return [
                'registration' => $registration,
                'checked_in_at' => $lastCheckin->created_at,
                'duration' => now()->diffInMinutes($lastCheckin->created_at)
            ];
        });
    }

    private function getRecentActivity($eventId, $limit)
    {
        $query = VisitorLog::with(['registration.user', 'registration.event', 'creator'])
                          ->latest();

        if ($eventId) {
            $query->whereHas('registration', function($q) use ($eventId) {
                $q->where('event_id', $eventId);
            });
        }

        return $query->limit($limit)->get();
    }

    private function getLiveStats($eventId)
    {
        $baseQuery = VisitorLog::query();
        
        if ($eventId) {
            $baseQuery->whereHas('registration', function($q) use ($eventId) {
                $q->where('event_id', $eventId);
            });
        }

        return [
            'today_checkins' => (clone $baseQuery)->checkins()->today()->count(),
            'today_checkouts' => (clone $baseQuery)->checkouts()->today()->count(),
            'active_visitors' => VisitorLog::getActiveVisitors($eventId),
            'last_hour_checkins' => (clone $baseQuery)->checkins()
                                                     ->where('created_at', '>=', now()->subHour())
                                                     ->count()
        ];
    }

    private function getTodayHourlyCheckins($eventId)
    {
        return VisitorLog::getHourlyDistribution($eventId, now()->format('Y-m-d'));
    }

    private function getRegistrationTimeline($registrationId)
    {
        return VisitorLog::where('registration_id', $registrationId)
                        ->with(['creator'])
                        ->orderBy('created_at', 'asc')
                        ->get();
    }

    private function exportToCsv($logs, $filename)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}.csv\"",
        ];

        $callback = function() use ($logs) {
            $file = fopen('php://output', 'w');
            
            fputcsv($file, [
                'ID', 'Registration ID', 'User Name', 'Email', 'Event', 'Action', 
                'Timestamp', 'Duration (min)', 'Admin Note', 'Created By', 
                'IP Address', 'QR Scanned', 'Method'
            ]);

            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->id,
                    $log->registration_id,
                    $log->registration->user->name,
                    $log->registration->user->email,
                    $log->registration->event->name,
                    ucfirst($log->action),
                    $log->formatted_created_at,
                    $log->duration_minutes ?? '',
                    $log->admin_note ?? '',
                    $log->creator->name ?? 'System',
                    $log->ip_address ?? '',
                    $log->qr_scanned ? 'Yes' : 'No',
                    $log->device_info['method'] ?? ($log->qr_scanned ? 'QR Code' : 'Manual')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportToExcel($logs, $filename)
    {
        // Implementation using Laravel Excel
        // return Excel::download(new VisitorLogsExport($logs), $filename . '.xlsx');
    }

    private function exportToPdf($logs, $filename)
    {
        // Implementation using DomPDF
        // return PDF::loadView('exports.visitor-logs-pdf', compact('logs'))->download($filename . '.pdf');
    }

    private function generateSummaryReport($eventId, $dateFrom, $dateTo)
    {
        // Implementation for summary report generation
        return [];
    }

    private function generateDetailedReport($eventId, $dateFrom, $dateTo)
    {
        // Implementation for detailed report generation
        return [];
    }

    private function generateAttendanceReport($eventId, $dateFrom, $dateTo)
    {
        // Implementation for attendance report generation
        return [];
    }

    private function generateDurationReport($eventId, $dateFrom, $dateTo)
    {
        // Implementation for duration report generation
        return [];
    }
}