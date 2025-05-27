<?php

namespace App\Http\Controllers;

use App\Models\VisitorLog;
use App\Models\Event;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $eventId = $request->input('event_id');
        $reportType = $request->input('type', 'summary');
        $dateFrom = $request->input('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));

        // Generate the report data based on type
        $data = match($reportType) {
            'summary' => $this->generateSummaryReport($eventId, $dateFrom, $dateTo),
            'detailed' => $this->generateDetailedReport($eventId, $dateFrom, $dateTo),
            'attendance' => $this->generateAttendanceReport($eventId, $dateFrom, $dateTo),
            'duration' => $this->generateDurationReport($eventId, $dateFrom, $dateTo),
            default => $this->generateSummaryReport($eventId, $dateFrom, $dateTo)
        };

        $events = Event::orderBy('name')->get();

        return view('reports.index', compact(
            'data', 'events', 'reportType', 'eventId', 'dateFrom', 'dateTo'
        ));
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
        // You can expand this method for detailed reports
        $summary = $this->generateSummaryReport($eventId, $dateFrom, $dateTo);
        
        // Add additional detailed data here
        $summary['detailed'] = true;
        
        return $summary;
    }

    private function generateAttendanceReport($eventId, $dateFrom, $dateTo)
    {
        // You can expand this method for attendance reports
        $summary = $this->generateSummaryReport($eventId, $dateFrom, $dateTo);
        
        // Add attendance-specific data here
        $summary['attendance'] = true;
        
        return $summary;
    }

    private function generateDurationReport($eventId, $dateFrom, $dateTo)
    {
        // You can expand this method for duration reports
        $summary = $this->generateSummaryReport($eventId, $dateFrom, $dateTo);
        
        // Add duration-specific data here
        $summary['duration_analysis'] = true;
        
        return $summary;
    }
}