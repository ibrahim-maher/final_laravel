<?php

namespace App\Http\Controllers;

use App\Models\VisitorLog;
use App\Models\Event;
use App\Models\User;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class VisitorLogController extends Controller
{
    use AuthorizesRequests;

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
        
        // Handle case where User model might not have createdLogs relationship
        try {
            $creators = User::whereHas('createdLogs')->orderBy('name')->get();
        } catch (\Exception $e) {
            // Fallback: get users who have created logs by checking the visitor_logs table directly
            $creators = User::whereIn('id', function($query) {
                $query->select('created_by')
                      ->from('visitor_logs')
                      ->whereNotNull('created_by')
                      ->distinct();
            })->orderBy('name')->get();
        }

        // Get statistics
        $stats = $this->getFilteredStatistics($request);

        // Get analytics data
        $analytics = $this->getAnalyticsData($request);

        // Get recent activity for live feed
        $recentActivity = $this->getRecentActivity(null, 10);

        return view('visitor-logs.index', compact(
            'logs', 'events', 'creators', 'stats', 'analytics', 'recentActivity'
        ));
    }

    /**
     * Show detailed visitor log
     */
    public function show($id)
    {
        $visitorLog = VisitorLog::with(['registration.user', 'registration.event', 'creator'])->findOrFail($id);
        
        // Get related logs for this registration
        $relatedLogs = VisitorLog::where('registration_id', $visitorLog->registration_id)
            ->where('id', '!=', $visitorLog->id)
            ->with(['creator'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Get registration timeline
        $timeline = $this->getRegistrationTimeline($visitorLog->registration_id);

        if (request()->expectsJson()) {
            return response()->json([
                'html' => view('visitor-logs.partials.details', compact('visitorLog', 'relatedLogs', 'timeline'))->render()
            ]);
        }

        return view('visitor-logs.show', compact('visitorLog', 'relatedLogs', 'timeline'));
    }

    /**
     * Delete a visitor log
     */
    public function destroy($id)
    {
        $visitorLog = VisitorLog::findOrFail($id);
        
        // Simple authorization check
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Access denied');
        }

        try {
            $visitorLog->delete();
            
            Log::info('Visitor log deleted', [
                'log_id' => $visitorLog->id,
                'deleted_by' => auth()->id(),
                'registration_id' => $visitorLog->registration_id
            ]);

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Log deleted successfully'
                ]);
            }

            return redirect()->route('visitor-logs.index')
                           ->with('success', 'Log deleted successfully');

        } catch (\Exception $e) {
            Log::error('Failed to delete visitor log', [
                'log_id' => $visitorLog->id,
                'error' => $e->getMessage()
            ]);

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete log'
                ], 500);
            }

            return redirect()->back()->with('error', 'Failed to delete log');
        }
    }

    /**
     * Bulk delete visitor logs
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:visitor_logs,id'
        ]);

        try {
            $logs = VisitorLog::whereIn('id', $request->ids)->get();
            
            // Check authorization for each log
            foreach ($logs as $log) {
                $this->authorize('delete', $log);
            }

            $deletedCount = VisitorLog::whereIn('id', $request->ids)->delete();

            Log::info('Bulk delete visitor logs', [
                'deleted_count' => $deletedCount,
                'deleted_by' => auth()->id(),
                'ids' => $request->ids
            ]);

            return response()->json([
                'success' => true,
                'deleted_count' => $deletedCount,
                'message' => "{$deletedCount} logs deleted successfully"
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to bulk delete visitor logs', [
                'error' => $e->getMessage(),
                'ids' => $request->ids
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete logs'
            ], 500);
        }
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
        $request->validate([
            'format' => 'nullable|in:csv,excel,pdf',
            'ids' => 'nullable|array',
            'ids.*' => 'exists:visitor_logs,id'
        ]);

        $query = VisitorLog::with(['registration.user', 'registration.event', 'creator']);
        
        // If specific IDs are provided, export only those
        if ($request->filled('ids')) {
            $query->whereIn('id', $request->ids);
        } else {
            // Apply filters
            $this->applyFilters($query, $request);
        }

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
            'stats' => $this->getFilteredStatistics($request)
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
                'active_visitors' => max(0, $checkins - $checkouts),
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
                'hourly_distribution' => $this->getHourlyDistribution($eventId, now()->startOfDay(), now()->endOfDay()),
                'peak_hours' => $this->getPeakTimes($eventId, now()->startOfDay(), now()->endOfDay()),
                'daily_trends' => $this->getDailyTrends($eventId, now()->subDays(7), now())
            ];
        });
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

    $results = $query->get()->groupBy('date');
    
    // Transform the data to match the expected format
    $dates = [];
    $visits = [];
    
    // Create a complete date range to fill gaps
    $period = new \DatePeriod(
        \Carbon\Carbon::parse($dateFrom)->startOfDay(),
        new \DateInterval('P1D'),
        \Carbon\Carbon::parse($dateTo)->endOfDay()->addDay()
    );
    
    foreach ($period as $date) {
        $dateStr = $date->format('Y-m-d');
        $dates[] = $date->format('M d'); // Format for display
        
        // Count total visits (checkins + checkouts) for this date
        $dateData = $results->get($dateStr, collect());
        $totalVisits = $dateData->sum('count');
        $visits[] = $totalVisits;
    }
    
    return [
        'dates' => $dates,
        'visits' => $visits,
        'raw_data' => $results // Keep the original data if needed elsewhere
    ];
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
    
    $hours = [];
    $visits = [];
    
    // Generate 24 hours with proper formatting
    for ($i = 0; $i < 24; $i++) {
        $hours[] = sprintf('%02d:00', $i); // Format as "00:00", "01:00", etc.
        $visits[] = $data->get($i)?->count ?? 0;
    }

    return [
        'hours' => $hours,
        'visits' => $visits,
        'raw_data' => $data // Keep original data if needed
    ];
}

  
    private function getTopEvents($dateFrom, $dateTo)
    {
    return DB::table('visitor_logs')
            ->join('registrations', 'visitor_logs.registration_id', '=', 'registrations.id')
            ->join('events', 'registrations.event_id', '=', 'events.id')
            ->selectRaw('events.id, events.name, COUNT(*) as visits') // Changed from visit_count to visits
            ->whereBetween('visitor_logs.created_at', [$dateFrom, $dateTo])
            ->whereNull('visitor_logs.deleted_at')
            ->groupBy('events.id', 'events.name')
            ->orderBy('visits', 'desc') // Changed from visit_count to visits
            ->limit(10)
            ->get();
}

private function getPeakTimes($eventId, $dateFrom, $dateTo)
{
    $distribution = $this->getHourlyDistribution($eventId, $dateFrom, $dateTo);
    $hourlyData = $distribution['visits']; // Get the visits array
    
    if (empty($hourlyData)) {
        return [];
    }

    $max = max($hourlyData);
    $peakHours = [];
    
    foreach ($hourlyData as $hour => $count) {
        if ($count === $max && $count > 0) {
            $peakHours[$hour] = $count;
        }
    }

    return $peakHours;
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
        $query = DB::table('visitor_logs as vl1')
            ->select([
                'vl1.registration_id',
                'vl1.created_at as checkin_time',
                'r.user_id',
                'r.event_id',
                'u.name as user_name',
                'u.email as user_email',
                'e.name as event_name'
            ])
            ->join('registrations as r', 'r.id', '=', 'vl1.registration_id')
            ->join('users as u', 'u.id', '=', 'r.user_id')
            ->join('events as e', 'e.id', '=', 'r.event_id')
            ->where('vl1.action', 'checkin')
            ->whereNotExists(function($query) {
                $query->select(DB::raw(1))
                    ->from('visitor_logs as vl2')
                    ->whereRaw('vl2.registration_id = vl1.registration_id')
                    ->where('vl2.action', 'checkout')
                    ->whereRaw('vl2.created_at > vl1.created_at');
            })
            ->whereNull('vl1.deleted_at');

        if ($eventId) {
            $query->where('r.event_id', $eventId);
        }

        return $query->get()->map(function($visitor) {
            return [
                'registration_id' => $visitor->registration_id,
                'user_id' => $visitor->user_id,
                'user_name' => $visitor->user_name,
                'user_email' => $visitor->user_email,
                'event_name' => $visitor->event_name,
                'checked_in_at' => Carbon::parse($visitor->checkin_time),
                'duration_minutes' => now()->diffInMinutes(Carbon::parse($visitor->checkin_time))
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

        $todayCheckins = (clone $baseQuery)
            ->where('action', 'checkin')
            ->whereDate('created_at', today())
            ->count();

        $todayCheckouts = (clone $baseQuery)
            ->where('action', 'checkout')
            ->whereDate('created_at', today())
            ->count();

        $activeVisitors = $this->getActiveVisitorsData($eventId)->count();

        $lastHourCheckins = (clone $baseQuery)
            ->where('action', 'checkin')
            ->where('created_at', '>=', now()->subHour())
            ->count();

        return [
            'today_checkins' => $todayCheckins,
            'today_checkouts' => $todayCheckouts,
            'active_visitors' => $activeVisitors,
            'last_hour_checkins' => $lastHourCheckins
        ];
    }

  private function getTodayHourlyCheckins($eventId)
{
    $distribution = $this->getHourlyDistribution($eventId, now()->startOfDay(), now()->endOfDay());
    
    // If the realtime view expects just the visit counts, return those
    // Otherwise, return the full structure
    if (request()->expectsJson() || request()->is('*/realtime')) {
        return $distribution['visits']; // Return just the array of counts
    }
    
    return $distribution; // Return full structure with hours and visits
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
                    $log->created_at->format('Y-m-d H:i:s'),
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
        
        // For now, fallback to CSV
        return $this->exportToCsv($logs, $filename);
    }

    private function exportToPdf($logs, $filename)
    {
        // Implementation using DomPDF
        // $pdf = PDF::loadView('exports.visitor-logs-pdf', compact('logs'));
        // return $pdf->download($filename . '.pdf');
        
        // For now, fallback to CSV
        return $this->exportToCsv($logs, $filename);
    }

    private function generateSummaryReport($eventId, $dateFrom, $dateTo)
    {
        $dateFromCarbon = Carbon::parse($dateFrom);
        $dateToCarbon = Carbon::parse($dateTo);
        
        $query = VisitorLog::query();
        
        if ($eventId) {
            $query->whereHas('registration', function($q) use ($eventId) {
                $q->where('event_id', $eventId);
            });
        }
        
        $query->whereBetween('created_at', [$dateFromCarbon, $dateToCarbon]);
        
        $totalLogs = (clone $query)->count();
        $checkins = (clone $query)->where('action', 'checkin')->count();
        $checkouts = (clone $query)->where('action', 'checkout')->count();
        $uniqueVisitors = (clone $query)->distinct('registration_id')->count();
        
        $avgDuration = (clone $query)
            ->where('action', 'checkout')
            ->whereNotNull('duration_minutes')
            ->avg('duration_minutes');
            
        $totalDuration = (clone $query)
            ->where('action', 'checkout')
            ->whereNotNull('duration_minutes')
            ->sum('duration_minutes');

        return [
            'period' => [
                'from' => $dateFromCarbon->format('M d, Y'),
                'to' => $dateToCarbon->format('M d, Y'),
                'days' => $dateFromCarbon->diffInDays($dateToCarbon) + 1
            ],
            'totals' => [
                'total_logs' => $totalLogs,
                'total_checkins' => $checkins,
                'total_checkouts' => $checkouts,
                'unique_visitors' => $uniqueVisitors,
                'completion_rate' => $checkins > 0 ? round(($checkouts / $checkins) * 100, 2) : 0
            ],
            'duration' => [
                'total_minutes' => $totalDuration ?? 0,
                'total_hours' => $totalDuration ? round($totalDuration / 60, 2) : 0,
                'average_minutes' => $avgDuration ? round($avgDuration, 2) : 0,
                'average_hours' => $avgDuration ? round($avgDuration / 60, 2) : 0
            ],
            'daily_averages' => [
                'logs_per_day' => round($totalLogs / ($dateFromCarbon->diffInDays($dateToCarbon) + 1), 2),
                'checkins_per_day' => round($checkins / ($dateFromCarbon->diffInDays($dateToCarbon) + 1), 2),
                'visitors_per_day' => round($uniqueVisitors / ($dateFromCarbon->diffInDays($dateToCarbon) + 1), 2)
            ]
        ];
    }

    private function generateDetailedReport($eventId, $dateFrom, $dateTo)
    {
        $summary = $this->generateSummaryReport($eventId, $dateFrom, $dateTo);
        $hourlyDistribution = $this->getHourlyDistribution($eventId, Carbon::parse($dateFrom), Carbon::parse($dateTo));
        $dailyTrends = $this->getDailyTrends($eventId, Carbon::parse($dateFrom), Carbon::parse($dateTo));
        $durationAnalysis = $this->getDurationAnalysis($eventId, Carbon::parse($dateFrom), Carbon::parse($dateTo));
        
        // Top visitors by frequency
        $topVisitors = VisitorLog::selectRaw('
                registration_id,
                COUNT(*) as visit_count,
                AVG(duration_minutes) as avg_duration
            ')
            ->with(['registration.user', 'registration.event'])
            ->whereBetween('created_at', [Carbon::parse($dateFrom), Carbon::parse($dateTo)])
            ->when($eventId, function($q) use ($eventId) {
                $q->whereHas('registration', function($subQ) use ($eventId) {
                    $subQ->where('event_id', $eventId);
                });
            })
            ->groupBy('registration_id')
            ->orderBy('visit_count', 'desc')
            ->limit(10)
            ->get();

        return [
            'summary' => $summary,
            'hourly_distribution' => $hourlyDistribution,
            'daily_trends' => $dailyTrends,
            'duration_analysis' => $durationAnalysis,
            'top_visitors' => $topVisitors,
            'peak_hours' => $this->getPeakTimes($eventId, Carbon::parse($dateFrom), Carbon::parse($dateTo))
        ];
    }

    private function generateAttendanceReport($eventId, $dateFrom, $dateTo)
    {
        $query = VisitorLog::with(['registration.user', 'registration.event'])
            ->whereBetween('created_at', [Carbon::parse($dateFrom), Carbon::parse($dateTo)]);
            
        if ($eventId) {
            $query->whereHas('registration', function($q) use ($eventId) {
                $q->where('event_id', $eventId);
            });
        }

        $attendanceData = $query->get()->groupBy(function($log) {
            return $log->created_at->format('Y-m-d');
        });

        $dailyAttendance = [];
        foreach ($attendanceData as $date => $logs) {
            $checkins = $logs->where('action', 'checkin')->count();
            $checkouts = $logs->where('action', 'checkout')->count();
            $uniqueVisitors = $logs->unique('registration_id')->count();
            
            $dailyAttendance[$date] = [
                'date' => $date,
                'checkins' => $checkins,
                'checkouts' => $checkouts,
                'unique_visitors' => $uniqueVisitors,
                'completion_rate' => $checkins > 0 ? round(($checkouts / $checkins) * 100, 2) : 0
            ];
        }

        // Fill missing dates with zeros
        $period = new \DatePeriod(
            Carbon::parse($dateFrom),
            new \DateInterval('P1D'),
            Carbon::parse($dateTo)->addDay()
        );

        foreach ($period as $date) {
            $dateStr = $date->format('Y-m-d');
            if (!isset($dailyAttendance[$dateStr])) {
                $dailyAttendance[$dateStr] = [
                    'date' => $dateStr,
                    'checkins' => 0,
                    'checkouts' => 0,
                    'unique_visitors' => 0,
                    'completion_rate' => 0
                ];
            }
        }

        ksort($dailyAttendance);

        return [
            'daily_attendance' => array_values($dailyAttendance),
            'summary' => $this->generateSummaryReport($eventId, $dateFrom, $dateTo)
        ];
    }

    private function generateDurationReport($eventId, $dateFrom, $dateTo)
    {
        $durationAnalysis = $this->getDurationAnalysis($eventId, Carbon::parse($dateFrom), Carbon::parse($dateTo));
        
        // Get detailed duration data
        $query = VisitorLog::with(['registration.user', 'registration.event'])
            ->where('action', 'checkout')
            ->whereNotNull('duration_minutes')
            ->whereBetween('created_at', [Carbon::parse($dateFrom), Carbon::parse($dateTo)]);
            
        if ($eventId) {
            $query->whereHas('registration', function($q) use ($eventId) {
                $q->where('event_id', $eventId);
            });
        }

        $durationLogs = $query->orderBy('duration_minutes', 'desc')->get();
        
        // Duration by day of week
        $durationByDayOfWeek = $durationLogs->groupBy(function($log) {
            return $log->created_at->format('l'); // Full day name
        })->map(function($logs) {
            return [
                'count' => $logs->count(),
                'avg_duration' => round($logs->avg('duration_minutes'), 2),
                'total_duration' => $logs->sum('duration_minutes')
            ];
        });

        // Duration by hour
        $durationByHour = $durationLogs->groupBy(function($log) {
            return $log->created_at->format('H');
        })->map(function($logs) {
            return [
                'count' => $logs->count(),
                'avg_duration' => round($logs->avg('duration_minutes'), 2),
                'total_duration' => $logs->sum('duration_minutes')
            ];
        });

        return [
            'analysis' => $durationAnalysis,
            'by_day_of_week' => $durationByDayOfWeek,
            'by_hour' => $durationByHour,
            'detailed_logs' => $durationLogs->take(50), // Top 50 by duration
            'summary' => $this->generateSummaryReport($eventId, $dateFrom, $dateTo)
        ];
    }

    // Add this method to your VisitorLogController to fix duration calculation
private function calculateAndStoreDurations()
{
    // Get all check-ins that don't have corresponding check-outs with durations
    $incompleteVisits = DB::table('visitor_logs as checkin')
        ->select([
            'checkin.id as checkin_id',
            'checkin.registration_id',
            'checkin.created_at as checkin_time',
            'checkout.id as checkout_id',
            'checkout.created_at as checkout_time'
        ])
        ->leftJoin('visitor_logs as checkout', function($join) {
            $join->on('checkin.registration_id', '=', 'checkout.registration_id')
                 ->where('checkout.action', '=', 'checkout')
                 ->whereColumn('checkout.created_at', '>', 'checkin.created_at');
        })
        ->where('checkin.action', 'checkin')
        ->whereNull('checkin.deleted_at')
        ->whereNull('checkout.duration_minutes') // Only get ones without calculated duration
        ->whereNotNull('checkout.id') // Must have a checkout
        ->get();

    foreach ($incompleteVisits as $visit) {
        $checkinTime = Carbon::parse($visit->checkin_time);
        $checkoutTime = Carbon::parse($visit->checkout_time);
        $durationMinutes = $checkinTime->diffInMinutes($checkoutTime);

        // Update the checkout record with the calculated duration
        DB::table('visitor_logs')
            ->where('id', $visit->checkout_id)
            ->update(['duration_minutes' => $durationMinutes]);
    }
}

// Enhanced getDurationAnalysis method
private function getDurationAnalysis($eventId, $dateFrom, $dateTo)
{
    // First, ensure durations are calculated
    $this->calculateAndStoreDurations();

    $query = VisitorLog::where('action', 'checkout')
                      ->whereNotNull('duration_minutes')
                      ->whereBetween('created_at', [$dateFrom, $dateTo]);

    if ($eventId) {
        $query->whereHas('registration', function($q) use ($eventId) {
            $q->where('event_id', $eventId);
        });
    }

    $durations = $query->pluck('duration_minutes');

    // If no duration data, try to calculate from existing check-in/check-out pairs
    if ($durations->isEmpty()) {
        $durations = $this->calculateDurationsOnTheFly($eventId, $dateFrom, $dateTo);
    }

    if ($durations->isEmpty()) {
        return [
            'min' => 0,
            'max' => 0,
            'avg' => 0,
            'median' => 0,
            'ranges' => [
                '0-30 min' => 0,
                '31-60 min' => 0,
                '61-120 min' => 0,
                '121+ min' => 0,
            ],
            'labels' => ['0-30 min', '31-60 min', '61-120 min', '121+ min'],
            'data' => [0, 0, 0, 0],
            'total_records' => 0
        ];
    }

    $ranges = [
        '0-30 min' => $durations->filter(fn($d) => $d <= 30)->count(),
        '31-60 min' => $durations->filter(fn($d) => $d > 30 && $d <= 60)->count(),
        '61-120 min' => $durations->filter(fn($d) => $d > 60 && $d <= 120)->count(),
        '121+ min' => $durations->filter(fn($d) => $d > 120)->count(),
    ];

    return [
        'min' => $durations->min() ?? 0,
        'max' => $durations->max() ?? 0,
        'avg' => round($durations->avg() ?? 0, 2),
        'median' => $durations->median() ?? 0,
        'ranges' => $ranges,
        'labels' => array_keys($ranges),
        'data' => array_values($ranges),
        'total_records' => $durations->count(),
        // Additional statistics
        'percentiles' => [
            '25th' => $durations->sort()->values()[floor($durations->count() * 0.25)] ?? 0,
            '75th' => $durations->sort()->values()[floor($durations->count() * 0.75)] ?? 0,
            '90th' => $durations->sort()->values()[floor($durations->count() * 0.90)] ?? 0,
        ]
    ];
}

// Method to calculate durations on-the-fly from existing data
private function calculateDurationsOnTheFly($eventId, $dateFrom, $dateTo)
{
    $query = DB::table('visitor_logs')
        ->select([
            'registration_id',
            'action',
            'created_at'
        ])
        ->whereBetween('created_at', [$dateFrom, $dateTo])
        ->whereNull('deleted_at')
        ->orderBy('registration_id')
        ->orderBy('created_at');

    if ($eventId) {
        $query->whereExists(function($subQuery) use ($eventId) {
            $subQuery->select(DB::raw(1))
                    ->from('registrations')
                    ->whereColumn('registrations.id', 'visitor_logs.registration_id')
                    ->where('event_id', $eventId);
        });
    }

    $logs = $query->get()->groupBy('registration_id');
    $durations = collect();

    foreach ($logs as $registrationId => $userLogs) {
        $checkins = $userLogs->where('action', 'checkin')->sortBy('created_at');
        $checkouts = $userLogs->where('action', 'checkout')->sortBy('created_at');

        foreach ($checkins as $checkin) {
            // Find the next checkout after this checkin
            $checkout = $checkouts->where('created_at', '>', $checkin->created_at)->first();
            
            if ($checkout) {
                $checkinTime = Carbon::parse($checkin->created_at);
                $checkoutTime = Carbon::parse($checkout->created_at);
                $duration = $checkinTime->diffInMinutes($checkoutTime);
                
                // Only add reasonable durations (less than 24 hours)
                if ($duration > 0 && $duration < 1440) {
                    $durations->push($duration);
                }
            }
        }
    }

    return $durations;
}

// Enhanced overview analytics with better duration handling
private function getOverviewAnalytics($eventId, $dateFrom, $dateTo)
{
    $query = VisitorLog::query();
    
    if ($eventId) {
        $query->whereHas('registration', function($q) use ($eventId) {
            $q->where('event_id', $eventId);
        });
    }

    $query->whereBetween('created_at', [$dateFrom, $dateTo]);

    // Calculate durations on-the-fly if not stored
    $durationAnalysis = $this->getDurationAnalysis($eventId, $dateFrom, $dateTo);
    
    $totalVisits = (clone $query)->count();
    $uniqueVisitors = (clone $query)->distinct('registration_id')->count();
    $totalCheckins = (clone $query)->where('action', 'checkin')->count();
    $totalCheckouts = (clone $query)->where('action', 'checkout')->count();
    
    // Get stored duration or calculate average from analysis
    $avgDurationFromDB = (clone $query)->where('action', 'checkout')
                                       ->whereNotNull('duration_minutes')
                                       ->avg('duration_minutes');
    
    $avgDuration = $avgDurationFromDB ?? $durationAnalysis['avg'];
    
    // Get stored total duration or calculate from analysis
    $totalDurationFromDB = (clone $query)->where('action', 'checkout')
                                         ->whereNotNull('duration_minutes')
                                         ->sum('duration_minutes');
    
    $totalDuration = $totalDurationFromDB ?? ($durationAnalysis['avg'] * $durationAnalysis['total_records']);

    return [
        'total_visits' => $totalVisits,
        'unique_visitors' => $uniqueVisitors,
        'total_checkins' => $totalCheckins,
        'total_checkouts' => $totalCheckouts,
        'average_duration' => round($avgDuration ?? 0, 2),
        'total_duration' => $totalDuration ?? 0,
        'completion_rate' => $totalCheckins > 0 ? round(($totalCheckouts / $totalCheckins) * 100, 2) : 0,
        'duration_stats' => $durationAnalysis
    ];
}

// Method to create a comprehensive analytics report
private function getComprehensiveAnalytics($eventId, $dateFrom, $dateTo)
{
    return [
        'overview' => $this->getOverviewAnalytics($eventId, $dateFrom, $dateTo),
        'hourly_distribution' => $this->getHourlyDistribution($eventId, $dateFrom, $dateTo),
        'daily_trends' => $this->getDailyTrends($eventId, $dateFrom, $dateTo),
        'duration_analysis' => $this->getDurationAnalysis($eventId, $dateFrom, $dateTo),
        'top_events' => $this->getTopEvents($dateFrom, $dateTo),
        'peak_times' => $this->getPeakTimes($eventId, $dateFrom, $dateTo),
        'visitor_patterns' => $this->getVisitorPatterns($eventId, $dateFrom, $dateTo),
        'conversion_metrics' => $this->getConversionMetrics($eventId, $dateFrom, $dateTo),
        'trend_analysis' => $this->getTrendAnalysis($eventId, $dateFrom, $dateTo)
    ];
}

// New method for conversion metrics
private function getConversionMetrics($eventId, $dateFrom, $dateTo)
{
    $query = VisitorLog::whereBetween('created_at', [$dateFrom, $dateTo]);
    
    if ($eventId) {
        $query->whereHas('registration', function($q) use ($eventId) {
            $q->where('event_id', $eventId);
        });
    }

    $checkins = (clone $query)->where('action', 'checkin')->count();
    $checkouts = (clone $query)->where('action', 'checkout')->count();
    $uniqueRegistrations = (clone $query)->distinct('registration_id')->count();
    
    // Calculate bounce rate (visitors who checked in but never checked out)
    $bounceRate = $checkins > 0 ? round((($checkins - $checkouts) / $checkins) * 100, 2) : 0;
    
    // Calculate return visitor rate
    $repeatVisitors = DB::table('visitor_logs')
        ->select('registration_id')
        ->whereBetween('created_at', [$dateFrom, $dateTo])
        ->when($eventId, function($q) use ($eventId) {
            $q->whereExists(function($subQuery) use ($eventId) {
                $subQuery->select(DB::raw(1))
                        ->from('registrations')
                        ->whereColumn('registrations.id', 'visitor_logs.registration_id')
                        ->where('event_id', $eventId);
            });
        })
        ->groupBy('registration_id')
        ->havingRaw('COUNT(*) > 2') // More than one check-in/check-out pair
        ->count();

    $returnRate = $uniqueRegistrations > 0 ? round(($repeatVisitors / $uniqueRegistrations) * 100, 2) : 0;

    return [
        'completion_rate' => $checkins > 0 ? round(($checkouts / $checkins) * 100, 2) : 0,
        'bounce_rate' => $bounceRate,
        'return_visitor_rate' => $returnRate,
        'total_sessions' => $checkins,
        'completed_sessions' => $checkouts,
        'unique_visitors' => $uniqueRegistrations,
        'repeat_visitors' => $repeatVisitors
    ];
}

// New method for trend analysis
private function getTrendAnalysis($eventId, $dateFrom, $dateTo)
{
    // Compare with previous period
    $periodDiff = Carbon::parse($dateTo)->diffInDays(Carbon::parse($dateFrom));
    $previousDateFrom = Carbon::parse($dateFrom)->subDays($periodDiff);
    $previousDateTo = Carbon::parse($dateFrom)->subDay();

    $currentPeriod = $this->getOverviewAnalytics($eventId, $dateFrom, $dateTo);
    $previousPeriod = $this->getOverviewAnalytics($eventId, $previousDateFrom, $previousDateTo);

    $trends = [];
    foreach (['total_visits', 'unique_visitors', 'total_checkins', 'total_checkouts', 'average_duration'] as $metric) {
        $current = $currentPeriod[$metric] ?? 0;
        $previous = $previousPeriod[$metric] ?? 0;
        
        if ($previous > 0) {
            $change = (($current - $previous) / $previous) * 100;
            $trends[$metric] = [
                'current' => $current,
                'previous' => $previous,
                'change_percent' => round($change, 2),
                'trend' => $change > 0 ? 'up' : ($change < 0 ? 'down' : 'stable')
            ];
        } else {
            $trends[$metric] = [
                'current' => $current,
                'previous' => $previous,
                'change_percent' => $current > 0 ? 100 : 0,
                'trend' => $current > 0 ? 'up' : 'stable'
            ];
        }
    }

    return $trends;
}

// Updated analytics method in the controller
public function analytics(Request $request)
{
    $eventId = $request->input('event_id');
    $dateRange = $request->input('date_range', '7'); // days
    
    $dateFrom = now()->subDays($dateRange)->startOfDay();
    $dateTo = now()->endOfDay();

    // Use the comprehensive analytics method
    $analytics = $this->getComprehensiveAnalytics($eventId, $dateFrom, $dateTo);

    $events = Event::orderBy('name')->get();

    // Add debug information if no duration data
    if ($analytics['duration_analysis']['total_records'] === 0) {
        Log::info('No duration data found', [
            'event_id' => $eventId,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'total_logs' => VisitorLog::whereBetween('created_at', [$dateFrom, $dateTo])->count()
        ]);
    }

    return view('visitor-logs.analytics', compact('analytics', 'events', 'eventId', 'dateRange'));
}
}