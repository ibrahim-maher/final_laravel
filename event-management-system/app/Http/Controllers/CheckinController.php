<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use App\Models\VisitorLog;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class CheckinController extends Controller
{
    /**
     * Display the check-in page
     */
    public function index()
    {
        $activeEvents = Event::where('is_active', true)->get();
        $recentActivity = $this->getRecentActivity();
        $todayStats = $this->getTodayStats();

        return view('checkin.index', compact('activeEvents', 'recentActivity', 'todayStats'));
    }

    /**
     * Display the check-out page
     */
    public function checkout()
    {
        $activeEvents = Event::where('is_active', true)->get();
        $recentActivity = $this->getRecentActivity();
$activeVisitors = VisitorLog::getActiveVisitors()->map(function ($visitor) {
    return (array) $visitor;
});

        return view('checkin.checkout', compact('activeEvents', 'recentActivity', 'activeVisitors'));
    }

    /**
     * Display the scan for print page
     */
    public function scanForPrint()
    {
        return view('checkin.scan-for-print');
    }

    /**
     * Process QR code scan (unified endpoint)
     */
    public function scan(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'registration_id' => 'required|exists:registrations,id',
                'action' => 'sometimes|in:checkin,checkout,auto',
                'admin_note' => 'nullable|string|max:500',
                'location_data' => 'nullable|array',
                'device_info' => 'nullable|array'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $registration = Registration::with(['user', 'event'])->find($request->registration_id);

            // Check if event is active
            if (!$registration->event->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Event is not currently active'
                ], 400);
            }

            // Check if event time is valid
            $now = now();
            if ($now->lt($registration->event->start_date) || $now->gt($registration->event->end_date)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Event is not currently running'
                ], 400);
            }

            // Determine action
            $requestedAction = $request->input('action', 'auto');
            if ($requestedAction === 'auto') {
                $lastLog = $registration->visitorLogs()->latest()->first();
                $action = (!$lastLog || $lastLog->action === VisitorLog::ACTION_CHECKOUT) 
                    ? VisitorLog::ACTION_CHECKIN 
                    : VisitorLog::ACTION_CHECKOUT;
            } else {
                $action = $requestedAction;
            }

            // Check for duplicate actions
            $recentLog = $registration->visitorLogs()
                ->where('action', $action)
                ->where('created_at', '>=', now()->subMinutes(2))
                ->first();

            if ($recentLog) {
                return response()->json([
                    'success' => false,
                    'message' => "Already {$action} within the last 2 minutes"
                ], 409);
            }

            DB::beginTransaction();

            try {
                // Create visitor log
                $visitorLog = VisitorLog::create([
                    'registration_id' => $registration->id,
                    'action' => $action,
                    'admin_note' => $request->admin_note,
                    'created_by' => auth()->id(),
                    'location_data' => $request->location_data,
                    'device_info' => $request->device_info,
                    'qr_scanned' => true,
                ]);

                // Clear cache for statistics
                $this->clearStatsCache($registration->event->id);

                // Log the activity
                Log::info("Visitor {$action}", [
                    'registration_id' => $registration->id,
                    'user_email' => $registration->user->email,
                    'event_id' => $registration->event->id,
                    'admin_id' => auth()->id(),
                    'timestamp' => $visitorLog->created_at
                ]);

                DB::commit();

                // Broadcast real-time update (if using WebSockets)
                $this->broadcastUpdate($visitorLog);

                return response()->json([
                    'success' => true,
                    'action' => $action,
                    'user' => $registration->user->name ?? $registration->user->email,
                    'event' => $registration->event->name,
                    'timestamp' => $visitorLog->formatted_created_at,
                    'log_id' => $visitorLog->id,
                    'duration' => $visitorLog->duration_formatted,
                    'message' => "Successfully {$action} {$registration->user->name}"
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Check-in/out error', [
                'error' => $e->getMessage(),
                'registration_id' => $request->registration_id ?? null,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred processing your request. Please try again.'
            ], 500);
        }
    }

    /**
     * Manual check-in/out form processing
     */
    public function manual(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'registration_id' => 'required|exists:registrations,id',
            'action' => 'required|in:checkin,checkout',
            'admin_note' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid data provided',
                'errors' => $validator->errors()
            ], 422);
        }

        // Add QR scan flag to distinguish manual vs QR entries
        $request->merge(['device_info' => ['method' => 'manual']]);
        
        return $this->scan($request);
    }

    /**
     * Verify registration exists (for scan-for-print)
     */
    public function verifyRegistration(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'registration_id' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Registration ID is required'
            ], 422);
        }

        $registration = Registration::with(['user', 'event'])
            ->find($request->registration_id);

        if (!$registration) {
            return response()->json([
                'success' => false,
                'message' => "Registration ID {$request->registration_id} not found"
            ], 404);
        }

        return response()->json([
            'success' => true,
            'registration' => [
                'id' => $registration->id,
                'user_name' => $registration->user->name,
                'user_email' => $registration->user->email,
                'event_name' => $registration->event->name,
                'last_action' => $registration->last_action,
                'is_checked_in' => $registration->is_checked_in
            ]
        ]);
    }

    /**
     * Get real-time statistics
     */
    public function getStats(Request $request)
    {
        $eventId = $request->input('event_id');
        $cacheKey = "checkin_stats_{$eventId}_" . now()->format('Y-m-d-H');
        
        $stats = Cache::remember($cacheKey, 300, function() use ($eventId) {
            return VisitorLog::getStatistics($eventId);
        });

        return response()->json($stats);
    }

    /**
     * Get recent activity for dashboard
     */
    public function getRecentActivity($limit = 10)
    {
        return VisitorLog::with(['registration.user', 'registration.event', 'creator'])
            ->latest()
            ->limit($limit)
            ->get()
            ->map(function($log) {
                return [
                    'id' => $log->id,
                    'user_name' => $log->registration->user->name,
                    'event_name' => $log->registration->event->name,
                    'action' => $log->action,
                    'timestamp' => $log->formatted_created_at,
                    'created_by' => $log->creator->name ?? 'System',
                    'duration' => $log->duration_formatted
                ];
            });
    }

    /**
     * Get today's statistics
     */
    private function getTodayStats()
    {
        $cacheKey = 'today_stats_' . now()->format('Y-m-d');
        
        return Cache::remember($cacheKey, 300, function() {
            return [
                'checkins' => VisitorLog::where('action', 'checkin')
                    ->whereDate('created_at', today())
                    ->count(),
                'checkouts' => VisitorLog::where('action', 'checkout')
                    ->whereDate('created_at', today())
                    ->count(),
                'active_visitors' => $this->getActiveVisitorsCount(),
                'events_active' => Event::where('is_active', true)->count()
            ];
        });
    }

    /**
     * Get currently active visitors
     */
    public function getActiveVisitors()
    {
        // Get all registrations that have checked in but not checked out
        $activeVisitors = DB::table('visitor_logs as vl1')
            ->select('vl1.registration_id', 'vl1.created_at as checkin_time')
            ->join('registrations', 'registrations.id', '=', 'vl1.registration_id')
            ->join('users', 'users.id', '=', 'registrations.user_id')
            ->join('events', 'events.id', '=', 'registrations.event_id')
            ->where('vl1.action', 'checkin')
            ->whereNotExists(function($query) {
                $query->select(DB::raw(1))
                    ->from('visitor_logs as vl2')
                    ->whereRaw('vl2.registration_id = vl1.registration_id')
                    ->where('vl2.action', 'checkout')
                    ->whereRaw('vl2.created_at > vl1.created_at');
            })
            ->whereNull('vl1.deleted_at')
            ->get();

        return $activeVisitors;
    }

    /**
     * Get count of currently active visitors
     */
    private function getActiveVisitorsCount()
    {
        return DB::table('visitor_logs as vl1')
            ->where('vl1.action', 'checkin')
            ->whereNotExists(function($query) {
                $query->select(DB::raw(1))
                    ->from('visitor_logs as vl2')
                    ->whereRaw('vl2.registration_id = vl1.registration_id')
                    ->where('vl2.action', 'checkout')
                    ->whereRaw('vl2.created_at > vl1.created_at');
            })
            ->whereNull('vl1.deleted_at')
            ->count();
    }

    /**
     * Export visitor logs
     */
    public function export(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'event_id' => 'nullable|exists:events,id',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'action' => 'nullable|in:checkin,checkout',
            'format' => 'nullable|in:csv,xlsx,pdf'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $format = $request->input('format', 'csv');
        $query = VisitorLog::with(['registration.user', 'registration.event', 'creator']);

        // Apply filters
        if ($request->event_id) {
            $query->byEvent($request->event_id);
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

        $logs = $query->orderBy('created_at', 'desc')->get();

        $filename = 'visitor_logs_' . now()->format('Y_m_d_H_i_s');

        switch ($format) {
            case 'csv':
                return $this->exportCsv($logs, $filename);
            case 'xlsx':
                return $this->exportExcel($logs, $filename);
            case 'pdf':
                return $this->exportPdf($logs, $filename);
            default:
                return $this->exportCsv($logs, $filename);
        }
    }

    /**
     * Get hourly analytics
     */
    public function getHourlyAnalytics(Request $request)
    {
        $eventId = $request->input('event_id');
        $date = $request->input('date', now()->format('Y-m-d'));

        $distribution = VisitorLog::getHourlyDistribution($eventId, $date);

        return response()->json([
            'labels' => array_keys($distribution),
            'data' => array_values($distribution),
            'date' => $date
        ]);
    }

    /**
     * Bulk check-in/out operations
     */
    public function bulkOperation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'registration_ids' => 'required|array|min:1',
            'registration_ids.*' => 'exists:registrations,id',
            'action' => 'required|in:checkin,checkout',
            'admin_note' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid data provided',
                'errors' => $validator->errors()
            ], 422);
        }

        $results = [];
        $successful = 0;
        $failed = 0;

        DB::beginTransaction();

        try {
            foreach ($request->registration_ids as $registrationId) {
                try {
                    $tempRequest = new Request([
                        'registration_id' => $registrationId,
                        'action' => $request->action,
                        'admin_note' => $request->admin_note
                    ]);

                    $response = $this->scan($tempRequest);
                    $data = json_decode($response->getContent(), true);

                    if ($data['success']) {
                        $successful++;
                        $results[] = [
                            'registration_id' => $registrationId,
                            'success' => true,
                            'message' => $data['message']
                        ];
                    } else {
                        $failed++;
                        $results[] = [
                            'registration_id' => $registrationId,
                            'success' => false,
                            'message' => $data['message']
                        ];
                    }
                } catch (\Exception $e) {
                    $failed++;
                    $results[] = [
                        'registration_id' => $registrationId,
                        'success' => false,
                        'message' => 'Processing failed'
                    ];
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Bulk operation completed. {$successful} successful, {$failed} failed.",
                'summary' => [
                    'total' => count($request->registration_ids),
                    'successful' => $successful,
                    'failed' => $failed
                ],
                'results' => $results
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Bulk operation failed'
            ], 500);
        }
    }

    // Helper methods
    private function clearStatsCache($eventId = null)
    {
        $patterns = [
            'checkin_stats_*',
            'today_stats_*',
            'hourly_distribution_*'
        ];

        foreach ($patterns as $pattern) {
            Cache::forget($pattern);
        }
    }

    private function broadcastUpdate($visitorLog)
    {
        // Implementation for real-time updates via WebSockets
        // This would integrate with Laravel Echo/Pusher or similar
    }

    private function exportCsv($logs, $filename)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}.csv\"",
        ];

        $callback = function() use ($logs) {
            $file = fopen('php://output', 'w');
            
            fputcsv($file, [
                'ID', 'User Name', 'Email', 'Event', 'Action', 'Timestamp', 
                'Duration', 'Admin Note', 'Created By', 'IP Address', 'Method'
            ]);

            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->id,
                    $log->registration->user->name,
                    $log->registration->user->email,
                    $log->registration->event->name,
                    ucfirst($log->action),
                    $log->formatted_created_at,
                    $log->duration_formatted ?? '',
                    $log->admin_note ?? '',
                    $log->creator->name ?? 'System',
                    $log->ip_address ?? '',
                    $log->device_info['method'] ?? ($log->qr_scanned ? 'QR Code' : 'Manual')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportExcel($logs, $filename)
    {
        // Implementation for Excel export using Laravel Excel package
        // return Excel::download(new VisitorLogsExport($logs), $filename . '.xlsx');
    }

    private function exportPdf($logs, $filename)
    {
        // Implementation for PDF export using DomPDF or similar
        // return PDF::loadView('exports.visitor-logs-pdf', compact('logs'))->download($filename . '.pdf');
    }
}