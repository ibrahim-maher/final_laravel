<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Registration;
use App\Models\VisitorLog;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        return view('reports.index');
    }

    public function events(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->subMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));
        
        $events = Event::with(['registrations', 'tickets'])
                      ->whereBetween('start_date', [$dateFrom, $dateTo])
                      ->get();

        $summary = [
            'total_events' => $events->count(),
            'total_registrations' => $events->sum(function($event) {
                return $event->registrations->count();
            }),
            'total_checkins' => VisitorLog::whereHas('registration.event', function($query) use ($dateFrom, $dateTo) {
                $query->whereBetween('start_date', [$dateFrom, $dateTo]);
            })->where('action', 'checkin')->count(),
            'average_attendance' => $events->count() > 0 ? 
                $events->sum(function($event) {
                    return $event->registrations->count();
                }) / $events->count() : 0,
        ];

        return view('reports.events', compact('events', 'summary', 'dateFrom', 'dateTo'));
    }

    public function registrations(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->subMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));
        
        $registrations = Registration::with(['user', 'event', 'ticketType'])
                                   ->whereBetween('created_at', [$dateFrom, $dateTo])
                                   ->get();

        // Registration trends by day
        $trends = Registration::selectRaw('DATE(created_at) as date, COUNT(*) as count')
                             ->whereBetween('created_at', [$dateFrom, $dateTo])
                             ->groupBy('date')
                             ->orderBy('date')
                             ->get();

        // Top events by registrations
        $topEvents = Event::withCount('registrations')
                         ->whereHas('registrations', function($query) use ($dateFrom, $dateTo) {
                             $query->whereBetween('created_at', [$dateFrom, $dateTo]);
                         })
                         ->orderBy('registrations_count', 'desc')
                         ->limit(10)
                         ->get();

        $summary = [
            'total_registrations' => $registrations->count(),
            'unique_users' => $registrations->unique('user_id')->count(),
            'average_per_day' => $registrations->count() / max(1, Carbon::parse($dateFrom)->diffInDays(Carbon::parse($dateTo)) + 1),
        ];

        return view('reports.registrations', compact('registrations', 'trends', 'topEvents', 'summary', 'dateFrom', 'dateTo'));
    }

    public function attendance(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->subMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));
        
        $logs = VisitorLog::with(['registration.user', 'registration.event'])
                         ->whereBetween('created_at', [$dateFrom, $dateTo])
                         ->get();

        // Hourly distribution
        $hourlyDistribution = $logs->groupBy(function($log) {
            return $log->created_at->format('H');
        })->map->count()->sortKeys();

        // Daily attendance
        $dailyAttendance = $logs->where('action', 'checkin')
                              ->groupBy(function($log) {
                                  return $log->created_at->format('Y-m-d');
                              })
                              ->map->count()
                              ->sortKeys();

        $summary = [
            'total_checkins' => $logs->where('action', 'checkin')->count(),
            'total_checkouts' => $logs->where('action', 'checkout')->count(),
            'peak_hour' => $hourlyDistribution->keys()->first(),
            'average_duration' => $this->calculateAverageDuration($logs),
        ];

        return view('reports.attendance', compact('logs', 'hourlyDistribution', 'dailyAttendance', 'summary', 'dateFrom', 'dateTo'));
    }

    private function calculateAverageDuration($logs)
    {
        $durations = [];
        
        $logs->groupBy('registration_id')->each(function($userLogs) use (&$durations) {
            $checkins = $userLogs->where('action', 'checkin')->sortBy('created_at');
            $checkouts = $userLogs->where('action', 'checkout')->sortBy('created_at');
            
            foreach ($checkins as $checkin) {
                $checkout = $checkouts->where('created_at', '>', $checkin->created_at)->first();
                if ($checkout) {
                    $durations[] = $checkin->created_at->diffInMinutes($checkout->created_at);
                }
            }
        });

        return count($durations) > 0 ? array_sum($durations) / count($durations) : 0;
    }

    public function export(Request $request)
    {
        $type = $request->get('type');
        $dateFrom = $request->get('date_from', now()->subMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));
        
        switch ($type) {
            case 'events':
                return $this->exportEvents($dateFrom, $dateTo);
            case 'registrations':
                return $this->exportRegistrations($dateFrom, $dateTo);
            case 'attendance':
                return $this->exportAttendance($dateFrom, $dateTo);
            default:
                abort(400, 'Invalid export type');
        }
    }

    private function exportEvents($dateFrom, $dateTo)
    {
        $events = Event::with(['registrations', 'venue', 'category'])
                      ->whereBetween('start_date', [$dateFrom, $dateTo])
                      ->get();

        $filename = 'events_report_' . now()->format('Y_m_d_H_i_s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($events) {
            $file = fopen('php://output', 'w');
            
            fputcsv($file, [
                'Event Name', 'Category', 'Venue', 'Start Date', 'End Date', 
                'Total Registrations', 'Total Check-ins', 'Status'
            ]);

            foreach ($events as $event) {
                $checkins = VisitorLog::whereHas('registration', function($query) use ($event) {
                    $query->where('event_id', $event->id);
                })->where('action', 'checkin')->count();

                fputcsv($file, [
                    $event->name,
                    $event->category->name,
                    $event->venue->name,
                    $event->start_date->format('Y-m-d H:i'),
                    $event->end_date->format('Y-m-d H:i'),
                    $event->registrations->count(),
                    $checkins,
                    $event->status,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}