<?php

namespace App\Http\Controllers;

use App\Models\VisitorLog;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class VisitorLogController extends Controller
{
    public function index()
    {
        $logs = VisitorLog::with(['registration.user', 'registration.event', 'creator'])
                         ->when(request('search'), function($query) {
                             $query->whereHas('registration.user', function($q) {
                                 $q->where('name', 'like', '%' . request('search') . '%')
                                   ->orWhere('email', 'like', '%' . request('search') . '%');
                             });
                         })
                         ->when(request('event_id'), function($query) {
                             $query->whereHas('registration', function($q) {
                                 $q->where('event_id', request('event_id'));
                             });
                         })
                         ->when(request('action'), function($query) {
                             $query->where('action', request('action'));
                         })
                         ->when(request('date_from'), function($query) {
                             $query->whereDate('created_at', '>=', request('date_from'));
                         })
                         ->when(request('date_to'), function($query) {
                             $query->whereDate('created_at', '<=', request('date_to'));
                         })
                         ->orderBy('created_at', 'desc')
                         ->paginate(20);

        $events = Event::orderBy('name')->get();
        
        // Statistics
        $stats = [
            'total_checkins' => VisitorLog::where('action', 'checkin')->count(),
            'total_checkouts' => VisitorLog::where('action', 'checkout')->count(),
            'today_checkins' => VisitorLog::where('action', 'checkin')->whereDate('created_at', today())->count(),
            'active_visitors' => VisitorLog::select('registration_id')
                                         ->where('action', 'checkin')
                                         ->whereNotIn('registration_id', function($query) {
                                             $query->select('registration_id')
                                                   ->from('visitor_logs')
                                                   ->where('action', 'checkout')
                                                   ->where('created_at', '>', function($subQuery) {
                                                       $subQuery->select('created_at')
                                                               ->from('visitor_logs AS vl2')
                                                               ->whereColumn('vl2.registration_id', 'visitor_logs.registration_id')
                                                               ->where('vl2.action', 'checkin')
                                                               ->orderBy('created_at', 'desc')
                                                               ->limit(1);
                                                   });
                                         })
                                         ->distinct()
                                         ->count(),
        ];

        return view('visitor-logs.index', compact('logs', 'events', 'stats'));
    }

    public function show(VisitorLog $visitorLog)
    {
        $visitorLog->load(['registration.user', 'registration.event', 'creator']);
        return view('visitor-logs.show', compact('visitorLog'));
    }

    public function export(Request $request)
    {
        $logs = VisitorLog::with(['registration.user', 'registration.event', 'creator'])
                         ->when(request('event_id'), function($query) {
                             $query->whereHas('registration', function($q) {
                                 $q->where('event_id', request('event_id'));
                             });
                         })
                         ->when(request('date_from'), function($query) {
                             $query->whereDate('created_at', '>=', request('date_from'));
                         })
                         ->when(request('date_to'), function($query) {
                             $query->whereDate('created_at', '<=', request('date_to'));
                         })
                         ->orderBy('created_at', 'desc')
                         ->get();

        $filename = 'visitor_logs_' . now()->format('Y_m_d_H_i_s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($logs) {
            $file = fopen('php://output', 'w');
            
            // Add CSV headers
            fputcsv($file, [
                'Name', 'Email', 'Event', 'Action', 'Timestamp', 'Admin Note', 'Created By'
            ]);

            // Add data rows
            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->registration->user->full_name,
                    $log->registration->user->email,
                    $log->registration->event->name,
                    ucfirst($log->action),
                    $log->created_at->format('Y-m-d H:i:s'),
                    $log->admin_note ?? '',
                    $log->creator->name ?? 'System',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}