<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Registration;
use App\Models\User;
use App\Models\VisitorLog;
use App\Models\Venue;
use App\Models\Category;
use App\Models\Ticket;
use App\Models\BadgeTemplate;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            // Basic stats with comparisons
            $stats = $this->getBasicStats();
            $changes = $this->getStatChanges();
            
            // Advanced analytics
            $analytics = [
                'registration_trends' => $this->getRegistrationTrends(),
                'attendance_analytics' => $this->getAttendanceAnalytics(),
                'venue_performance' => $this->getVenuePerformance(),
                'category_distribution' => $this->getCategoryDistribution(),
                'revenue_analytics' => $this->getRevenueAnalytics(),
                'user_engagement' => $this->getUserEngagement(),
                'badge_template_usage' => $this->getBadgeTemplateUsage(),
                'peak_hours' => $this->getPeakHours(),
                'event_capacity_analysis' => $this->getEventCapacityAnalysis(),
                'geographic_distribution' => $this->getGeographicDistribution()
            ];
            
            // Recent activities
            $recentEvents = $this->getRecentEvents();
            $recentRegistrations = $this->getRecentRegistrations();
            $recentActivity = $this->getRecentActivity();
            
            // Event status distribution
            $eventStats = $this->getEventStats();
            
            // Performance metrics
            $performanceMetrics = $this->getPerformanceMetrics();
            
            // Alerts and notifications
            $alerts = $this->getSystemAlerts();
            
            return view('dashboard', compact(
                'stats', 
                'changes',
                'analytics',
                'recentEvents', 
                'recentRegistrations', 
                'recentActivity',
                'eventStats',
                'performanceMetrics',
                'alerts'
            ));

        } catch (\Exception $e) {
            logger()->error('Dashboard error: ' . $e->getMessage());
            return $this->fallbackDashboard();
        }
    }

    private function getBasicStats()
    {
        return Cache::remember('dashboard_basic_stats', 300, function() {
            return [
                'total_events' => Event::count(),
                'active_events' => Event::where('is_active', true)->count(),
                'total_registrations' => Registration::count(),
                'total_users' => User::count(),
                'total_venues' => Venue::count(),
                'total_categories' => Category::count(),
                'total_tickets' => Ticket::count(),
                'checkins_today' => VisitorLog::whereDate('created_at', today())
                                            ->where('action', 'checkin')
                                            ->count(),
                'active_visitors' => $this->getActiveVisitorsCount(),
                'total_revenue' => $this->getTotalRevenue(),
                'avg_event_attendance' => $this->getAverageEventAttendance(),
                'badge_templates_count' => BadgeTemplate::count()
            ];
        });
    }

    private function getStatChanges()
    {
        return Cache::remember('dashboard_stat_changes', 900, function() {
            $lastMonth = now()->subMonth();
            
            return [
                'events' => $this->calculatePercentageChange(
                    Event::where('created_at', '>=', $lastMonth)->count(),
                    Event::where('created_at', '<', $lastMonth)->count()
                ),
                'registrations' => $this->calculatePercentageChange(
                    Registration::where('created_at', '>=', $lastMonth)->count(),
                    Registration::where('created_at', '<', $lastMonth)->count()
                ),
                'users' => $this->calculatePercentageChange(
                    User::where('created_at', '>=', $lastMonth)->count(),
                    User::where('created_at', '<', $lastMonth)->count()
                ),
                'revenue' => $this->calculateRevenueChange(),
                'attendance' => $this->calculateAttendanceChange()
            ];
        });
    }

    private function getRegistrationTrends()
    {
        return Cache::remember('dashboard_registration_trends', 600, function() {
            $data = [];
            for ($i = 29; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i);
                $count = Registration::whereDate('created_at', $date)->count();
                $data[] = [
                    'date' => $date->format('M d'),
                    'count' => $count,
                    'cumulative' => Registration::whereDate('created_at', '<=', $date)->count()
                ];
            }
            return $data;
        });
    }

    private function getAttendanceAnalytics()
    {
        return Cache::remember('dashboard_attendance_analytics', 600, function() {
            $today = today();
            $weekAgo = $today->copy()->subDays(7);
            
            $hourlyData = [];
            for ($hour = 0; $hour < 24; $hour++) {
                $hourlyData[$hour] = VisitorLog::where('action', 'checkin')
                                              ->whereTime('created_at', '>=', sprintf('%02d:00:00', $hour))
                                              ->whereTime('created_at', '<', sprintf('%02d:00:00', $hour + 1))
                                              ->whereBetween('created_at', [$weekAgo, $today])
                                              ->count();
            }

            $dailyData = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = $today->copy()->subDays($i);
                $dailyData[] = [
                    'date' => $date->format('M d'),
                    'checkins' => VisitorLog::whereDate('created_at', $date)
                                           ->where('action', 'checkin')
                                           ->count(),
                    'checkouts' => VisitorLog::whereDate('created_at', $date)
                                            ->where('action', 'checkout')
                                            ->count()
                ];
            }

            return [
                'hourly_distribution' => $hourlyData,
                'daily_trends' => $dailyData,
                'peak_hour' => array_keys($hourlyData, max($hourlyData))[0] ?? 12,
                'average_duration' => $this->getAverageDuration(),
                'completion_rate' => $this->getCompletionRate()
            ];
        });
    }

    private function getVenuePerformance()
    {
        return Cache::remember('dashboard_venue_performance', 900, function() {
            return Venue::withCount(['events', 'events as active_events_count' => function($query) {
                            $query->where('is_active', true);
                        }])
                        ->with(['events' => function($query) {
                            $query->withCount('registrations');
                        }])
                        ->get()
                        ->map(function($venue) {
                            $totalRegistrations = $venue->events->sum('registrations_count');
                            $utilizationRate = $venue->events->count() > 0 ? 
                                ($totalRegistrations / ($venue->capacity * $venue->events->count())) * 100 : 0;
                            
                            return [
                                'name' => $venue->name,
                                'total_events' => $venue->events_count,
                                'active_events' => $venue->active_events_count,
                                'total_registrations' => $totalRegistrations,
                                'capacity' => $venue->capacity,
                                'utilization_rate' => round($utilizationRate, 2),
                                'revenue' => $this->getVenueRevenue($venue->id)
                            ];
                        })
                        ->sortByDesc('total_registrations')
                        ->take(10);
        });
    }

    private function getCategoryDistribution()
    {
        return Cache::remember('dashboard_category_distribution', 900, function() {
            return Category::withCount(['events', 'events as active_events_count' => function($query) {
                             $query->where('is_active', true);
                         }])
                         ->get()
                         ->map(function($category) {
                             $registrations = Registration::whereHas('event', function($query) use ($category) {
                                 $query->where('category_id', $category->id);
                             })->count();
                             
                             return [
                                 'name' => $category->name,
                                 'events_count' => $category->events_count,
                                 'active_events_count' => $category->active_events_count,
                                 'registrations_count' => $registrations,
                                 'average_registrations_per_event' => $category->events_count > 0 ? 
                                     round($registrations / $category->events_count, 2) : 0
                             ];
                         })
                         ->sortByDesc('events_count');
        });
    }

    private function getRevenueAnalytics()
    {
        return Cache::remember('dashboard_revenue_analytics', 600, function() {
            $monthlyRevenue = [];
            for ($i = 11; $i >= 0; $i--) {
                $month = now()->subMonths($i);
                $revenue = Registration::whereMonth('created_at', $month->month)
                                     ->whereYear('created_at', $month->year)
                                     ->join('tickets', 'registrations.ticket_type_id', '=', 'tickets.id')
                                     ->sum('tickets.price');
                
                $monthlyRevenue[] = [
                    'month' => $month->format('M Y'),
                    'revenue' => $revenue,
                    'registrations' => Registration::whereMonth('created_at', $month->month)
                                                 ->whereYear('created_at', $month->year)
                                                 ->count()
                ];
            }

            $topTickets = Ticket::withCount('registrations')
                               ->with('event')
                               ->orderBy('registrations_count', 'desc')
                               ->take(5)
                               ->get()
                               ->map(function($ticket) {
                                   return [
                                       'name' => $ticket->name,
                                       'event' => $ticket->event->name,
                                       'price' => $ticket->price,
                                       'sold' => $ticket->registrations_count,
                                       'revenue' => $ticket->price * $ticket->registrations_count
                                   ];
                               });

            return [
                'monthly_trends' => $monthlyRevenue,
                'top_tickets' => $topTickets,
                'total_revenue' => array_sum(array_column($monthlyRevenue, 'revenue')),
                'average_ticket_price' => Ticket::avg('price'),
                'revenue_per_registration' => $this->getRevenuePerRegistration()
            ];
        });
    }

    private function getUserEngagement()
    {
        return Cache::remember('dashboard_user_engagement', 900, function() {
            $roleDistribution = User::selectRaw('role, COUNT(*) as count')
                                  ->groupBy('role')
                                  ->pluck('count', 'role')
                                  ->toArray();

            $registrationsByUser = Registration::selectRaw('user_id, COUNT(*) as registrations_count')
                                             ->groupBy('user_id')
                                             ->havingRaw('COUNT(*) > 1')
                                             ->get()
                                             ->map(function($item) {
                                                 return $item->registrations_count;
                                             });

            $averageRegistrationsPerUser = Registration::count() / max(User::count(), 1);
            
            $activeUsers = User::whereHas('registrations', function($query) {
                             $query->where('created_at', '>=', now()->subDays(30));
                         })->count();

            return [
                'role_distribution' => $roleDistribution,
                'repeat_registrations' => $registrationsByUser->count(),
                'average_registrations_per_user' => round($averageRegistrationsPerUser, 2),
                'active_users_30_days' => $activeUsers,
                'user_retention_rate' => $this->getUserRetentionRate(),
                'most_active_users' => $this->getMostActiveUsers()
            ];
        });
    }

    private function getBadgeTemplateUsage()
    {
        return Cache::remember('dashboard_badge_template_usage', 900, function() {
            $templatesWithUsage = BadgeTemplate::withCount(['ticket as registrations_count' => function($query) {
                                                 $query->join('registrations', 'tickets.id', '=', 'registrations.ticket_type_id');
                                             }])
                                             ->with(['ticket.event', 'creator'])
                                             ->get()
                                             ->map(function($template) {
                                                 return [
                                                     'name' => $template->name,
                                                     'event' => $template->ticket->event->name ?? 'Unknown',
                                                     'usage_count' => $template->registrations_count,
                                                     'created_by' => $template->creator->name ?? 'System',
                                                     'created_at' => $template->created_at->format('M d, Y')
                                                 ];
                                             });

            return [
                'total_templates' => BadgeTemplate::count(),
                'templates_with_usage' => $templatesWithUsage->where('usage_count', '>', 0)->count(),
                'most_used_templates' => $templatesWithUsage->sortByDesc('usage_count')->take(5),
                'unused_templates' => $templatesWithUsage->where('usage_count', 0)->count()
            ];
        });
    }

    private function getPeakHours()
    {
        return Cache::remember('dashboard_peak_hours', 600, function() {
            $hourlyActivity = VisitorLog::selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
                                       ->where('created_at', '>=', now()->subDays(7))
                                       ->where('action', 'checkin')
                                       ->groupBy('hour')
                                       ->orderBy('count', 'desc')
                                       ->get();

            return [
                'peak_hour' => $hourlyActivity->first()->hour ?? 12,
                'peak_hour_count' => $hourlyActivity->first()->count ?? 0,
                'hourly_distribution' => $hourlyActivity->pluck('count', 'hour')->toArray()
            ];
        });
    }

    private function getEventCapacityAnalysis()
    {
        return Cache::remember('dashboard_capacity_analysis', 900, function() {
            $events = Event::with(['venue', 'registrations'])
                           ->where('start_date', '>=', now()->subDays(30))
                           ->get()
                           ->map(function($event) {
                               $capacity = $event->venue->capacity ?? 0;
                               $registrations = $event->registrations->count();
                               $utilizationRate = $capacity > 0 ? ($registrations / $capacity) * 100 : 0;
                               
                               return [
                                   'name' => $event->name,
                                   'capacity' => $capacity,
                                   'registrations' => $registrations,
                                   'utilization_rate' => round($utilizationRate, 2),
                                   'status' => $utilizationRate >= 90 ? 'high' : ($utilizationRate >= 70 ? 'medium' : 'low')
                               ];
                           });

            return [
                'high_utilization' => $events->where('status', 'high')->count(),
                'medium_utilization' => $events->where('status', 'medium')->count(),
                'low_utilization' => $events->where('status', 'low')->count(),
                'average_utilization' => $events->avg('utilization_rate'),
                'top_utilized_events' => $events->sortByDesc('utilization_rate')->take(5)
            ];
        });
    }

    private function getGeographicDistribution()
    {
        return Cache::remember('dashboard_geographic_distribution', 1800, function() {
            $countryData = User::selectRaw('country, COUNT(*) as count')
                              ->whereNotNull('country')
                              ->groupBy('country')
                              ->orderBy('count', 'desc')
                              ->take(10)
                              ->get();

            $registrationsByCountry = Registration::join('users', 'registrations.user_id', '=', 'users.id')
                                                 ->selectRaw('users.country, COUNT(*) as registrations')
                                                 ->whereNotNull('users.country')
                                                 ->groupBy('users.country')
                                                 ->orderBy('registrations', 'desc')
                                                 ->take(10)
                                                 ->get();

            return [
                'users_by_country' => $countryData,
                'registrations_by_country' => $registrationsByCountry,
                'total_countries' => User::whereNotNull('country')->distinct('country')->count()
            ];
        });
    }

    private function getRecentEvents()
    {
        return Event::with(['venue', 'category'])
                   ->withCount('registrations')
                   ->orderBy('created_at', 'desc')
                   ->limit(6)
                   ->get();
    }

    private function getRecentRegistrations()
    {
        return Registration::with(['user', 'event', 'ticketType'])
                          ->orderBy('created_at', 'desc')
                          ->limit(8)
                          ->get();
    }

    private function getRecentActivity()
    {
        return VisitorLog::with(['registration.user', 'registration.event'])
                        ->orderBy('created_at', 'desc')
                        ->limit(10)
                        ->get();
    }

    private function getEventStats()
    {
        return [
            'upcoming' => Event::where('start_date', '>', now())->count(),
            'ongoing' => Event::where('start_date', '<=', now())
                            ->where('end_date', '>=', now())
                            ->count(),
            'completed' => Event::where('end_date', '<', now())->count(),
        ];
    }

    private function getPerformanceMetrics()
    {
        return [
            'avg_registration_time' => $this->getAverageRegistrationTime(),
            'conversion_rate' => $this->getConversionRate(),
            'cancellation_rate' => $this->getCancellationRate(),
            'no_show_rate' => $this->getNoShowRate(),
            'system_health' => $this->getSystemHealth()
        ];
    }

    private function getSystemAlerts()
    {
        $alerts = [];
        
        // Check for events with low registration
        $lowRegistrationEvents = Event::where('is_active', true)
                                    ->where('start_date', '>', now())
                                    ->where('start_date', '<', now()->addDays(7))
                                    ->withCount('registrations')
                                    ->having('registrations_count', '<', 5)
                                    ->get();
        
        if ($lowRegistrationEvents->count() > 0) {
            $alerts[] = [
                'type' => 'warning',
                'message' => $lowRegistrationEvents->count() . ' upcoming events have low registration numbers',
                'action' => route('events.index')
            ];
        }
        
        // Check for overbooked events
        $overbookedEvents = Event::whereHas('venue')
                                 ->withCount('registrations')
                                 ->get()
                                 ->filter(function($event) {
                                     return $event->registrations_count > $event->venue->capacity;
                                 });
        
        if ($overbookedEvents->count() > 0) {
            $alerts[] = [
                'type' => 'danger',
                'message' => $overbookedEvents->count() . ' events are overbooked',
                'action' => route('events.index')
            ];
        }
        
        return $alerts;
    }

    // Helper methods
    private function getActiveVisitorsCount()
    {
        return VisitorLog::select('registration_id')
                        ->where('action', 'checkin')
                        ->whereNotIn('registration_id', function($query) {
                            $query->select('registration_id')
                                  ->from('visitor_logs')
                                  ->where('action', 'checkout')
                                  ->where('created_at', '>', function($subQuery) {
                                      $subQuery->select('created_at')
                                               ->from('visitor_logs as vl2')
                                               ->whereColumn('vl2.registration_id', 'visitor_logs.registration_id')
                                               ->where('vl2.action', 'checkin')
                                               ->orderBy('created_at', 'desc')
                                               ->limit(1);
                                  });
                        })
                        ->distinct()
                        ->count();
    }

    private function getTotalRevenue()
    {
        return Registration::join('tickets', 'registrations.ticket_type_id', '=', 'tickets.id')
                          ->sum('tickets.price');
    }

    private function getAverageEventAttendance()
    {
        $totalEvents = Event::count();
        if ($totalEvents === 0) return 0;
        
        return Registration::count() / $totalEvents;
    }

    private function calculatePercentageChange($current, $previous)
    {
        if ($previous == 0) return $current > 0 ? 100 : 0;
        return round((($current - $previous) / $previous) * 100, 1);
    }

    private function calculateRevenueChange()
    {
        $currentMonth = Registration::join('tickets', 'registrations.ticket_type_id', '=', 'tickets.id')
                                   ->whereMonth('registrations.created_at', now()->month)
                                   ->sum('tickets.price');
        
        $lastMonth = Registration::join('tickets', 'registrations.ticket_type_id', '=', 'tickets.id')
                                ->whereMonth('registrations.created_at', now()->subMonth()->month)
                                ->sum('tickets.price');
        
        return $this->calculatePercentageChange($currentMonth, $lastMonth);
    }

    private function calculateAttendanceChange()
    {
        $currentMonth = VisitorLog::whereMonth('created_at', now()->month)
                                 ->where('action', 'checkin')
                                 ->count();
        
        $lastMonth = VisitorLog::whereMonth('created_at', now()->subMonth()->month)
                              ->where('action', 'checkin')
                              ->count();
        
        return $this->calculatePercentageChange($currentMonth, $lastMonth);
    }

    private function getAverageDuration()
    {
        return VisitorLog::where('action', 'checkout')
                        ->whereNotNull('duration_minutes')
                        ->avg('duration_minutes') ?? 0;
    }

    private function getCompletionRate()
    {
        $checkins = VisitorLog::where('action', 'checkin')->count();
        $checkouts = VisitorLog::where('action', 'checkout')->count();
        
        return $checkins > 0 ? round(($checkouts / $checkins) * 100, 2) : 0;
    }

    private function getVenueRevenue($venueId)
    {
        return Registration::whereHas('event', function($query) use ($venueId) {
                             $query->where('venue_id', $venueId);
                         })
                         ->join('tickets', 'registrations.ticket_type_id', '=', 'tickets.id')
                         ->sum('tickets.price');
    }

    private function getRevenuePerRegistration()
    {
        $totalRevenue = $this->getTotalRevenue();
        $totalRegistrations = Registration::count();
        
        return $totalRegistrations > 0 ? round($totalRevenue / $totalRegistrations, 2) : 0;
    }

    private function getUserRetentionRate()
    {
        $usersWithMultipleRegistrations = User::whereHas('registrations', function($query) {
                                                }, '>=', 2)->count();
        $totalUsers = User::count();
        
        return $totalUsers > 0 ? round(($usersWithMultipleRegistrations / $totalUsers) * 100, 2) : 0;
    }

    private function getMostActiveUsers()
    {
        return User::withCount('registrations')
                  ->orderBy('registrations_count', 'desc')
                  ->take(5)
                  ->get()
                  ->map(function($user) {
                      return [
                          'name' => $user->name,
                          'email' => $user->email,
                          'registrations' => $user->registrations_count
                      ];
                  });
    }

    private function getAverageRegistrationTime()
    {
        // This would measure time from event creation to registration
        // Simplified implementation
        return 24; // hours
    }

    private function getConversionRate()
    {
        // Simplified: percentage of active events with registrations
        $activeEvents = Event::where('is_active', true)->count();
        $eventsWithRegistrations = Event::where('is_active', true)
                                       ->has('registrations')
                                       ->count();
        
        return $activeEvents > 0 ? round(($eventsWithRegistrations / $activeEvents) * 100, 2) : 0;
    }

    private function getCancellationRate()
    {
        $totalRegistrations = Registration::count();
        $cancelledRegistrations = Registration::where('status', 'cancelled')->count();
        
        return $totalRegistrations > 0 ? round(($cancelledRegistrations / $totalRegistrations) * 100, 2) : 0;
    }

    private function getNoShowRate()
    {
        // Users who registered but never checked in
        $registrationsWithCheckins = Registration::whereHas('visitorLogs', function($query) {
                                                   $query->where('action', 'checkin');
                                               })->count();
        $totalRegistrations = Registration::count();
        
        return $totalRegistrations > 0 ? round((($totalRegistrations - $registrationsWithCheckins) / $totalRegistrations) * 100, 2) : 0;
    }

    private function getSystemHealth()
    {
        return [
            'database_responsive' => true,
            'cache_working' => Cache::has('dashboard_basic_stats'),
            'recent_errors' => 0 // Would integrate with logging system
        ];
    }

    private function fallbackDashboard()
    {
        $stats = [
            'total_events' => Event::count(),
            'active_events' => 0,
            'total_registrations' => 0,
            'total_users' => User::count(),
            'checkins_today' => 0,
            'active_visitors' => 0,
        ];

        $changes = ['events' => 0, 'registrations' => 0, 'users' => 0];
        $analytics = [];
        $recentEvents = collect();
        $recentRegistrations = collect();
        $recentActivity = collect();
        $eventStats = ['upcoming' => 0, 'ongoing' => 0, 'completed' => 0];
        $performanceMetrics = [];
        $alerts = [];

        return view('dashboard', compact(
            'stats', 'changes', 'analytics', 'recentEvents', 
            'recentRegistrations', 'recentActivity', 'eventStats',
            'performanceMetrics', 'alerts'
        ));
    }
}