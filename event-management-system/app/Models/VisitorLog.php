<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class VisitorLog extends Model
{
    use HasFactory, SoftDeletes;

    // Action constants
    const ACTION_CHECKIN = 'checkin';
    const ACTION_CHECKOUT = 'checkout';

    protected $fillable = [
        'registration_id',
        'action',
        'admin_note',
        'created_by',
        'updated_by',
        'location_data',
        'device_info',
        'qr_scanned',
        'ip_address',
        'user_agent',
        'duration_minutes'
    ];

    protected $casts = [
        'location_data' => 'array',
        'device_info' => 'array',
        'qr_scanned' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    // Relationships
    public function registration()
    {
        return $this->belongsTo(Registration::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Scopes
    public function scopeCheckins($query)
    {
        return $query->where('action', self::ACTION_CHECKIN);
    }

    public function scopeCheckouts($query)
    {
        return $query->where('action', self::ACTION_CHECKOUT);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('created_at', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year);
    }

    public function scopeByEvent($query, $eventId)
    {
        return $query->whereHas('registration', function($q) use ($eventId) {
            $q->where('event_id', $eventId);
        });
    }

    public function scopeByUser($query, $userId)
    {
        return $query->whereHas('registration', function($q) use ($userId) {
            $q->where('user_id', $userId);
        });
    }

    public function scopeQrScanned($query)
    {
        return $query->where('qr_scanned', true);
    }

    public function scopeManualEntry($query)
    {
        return $query->where('qr_scanned', false);
    }

    public function scopeWithDuration($query)
    {
        return $query->whereNotNull('duration_minutes');
    }

    public function scopeRecent($query, $minutes = 30)
    {
        return $query->where('created_at', '>=', now()->subMinutes($minutes));
    }

    // Accessors
    public function getFormattedCreatedAtAttribute()
    {
        return $this->created_at->format('M d, Y \a\t H:i');
    }

    public function getDurationFormattedAttribute()
    {
        if (!$this->duration_minutes) {
            return null;
        }

        if ($this->duration_minutes < 60) {
            return $this->duration_minutes . 'm';
        }

        $hours = floor($this->duration_minutes / 60);
        $minutes = $this->duration_minutes % 60;
        
        if ($minutes > 0) {
            return $hours . 'h ' . $minutes . 'm';
        }
        
        return $hours . 'h';
    }

    public function getDurationHoursAttribute()
    {
        return $this->duration_minutes ? round($this->duration_minutes / 60, 2) : null;
    }

    public function getActionBadgeAttribute()
    {
        if ($this->action === self::ACTION_CHECKIN) {
            return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        <i class="fas fa-sign-in-alt mr-1"></i>Check-in
                    </span>';
        } else {
            return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        <i class="fas fa-sign-out-alt mr-1"></i>Check-out
                    </span>';
        }
    }

    public function getMethodBadgeAttribute()
    {
        if ($this->qr_scanned) {
            return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        <i class="fas fa-qrcode mr-1"></i>QR Code
                    </span>';
        } else {
            return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                        <i class="fas fa-keyboard mr-1"></i>Manual
                    </span>';
        }
    }

    // Static Methods for Statistics
    public static function getStatistics($eventId = null)
    {
        $cacheKey = 'visitor_log_stats_' . ($eventId ?? 'all') . '_' . today()->format('Y-m-d');
        
        return Cache::remember($cacheKey, 600, function() use ($eventId) {
            $query = self::query();
            
            if ($eventId) {
                $query->byEvent($eventId);
            }

            $totalCheckins = (clone $query)->checkins()->count();
            $totalCheckouts = (clone $query)->checkouts()->count();
            $todayCheckins = (clone $query)->checkins()->today()->count();
            $todayCheckouts = (clone $query)->checkouts()->today()->count();
            $activeVisitors = self::getActiveVisitorsCount($eventId);
            
            $avgDuration = (clone $query)->checkouts()
                                        ->whereNotNull('duration_minutes')
                                        ->avg('duration_minutes');
            
            $totalDuration = (clone $query)->checkouts()
                                          ->whereNotNull('duration_minutes')
                                          ->sum('duration_minutes');

            return [
                'total_checkins' => $totalCheckins,
                'total_checkouts' => $totalCheckouts,
                'today_checkins' => $todayCheckins,
                'today_checkouts' => $todayCheckouts,
                'active_visitors' => $activeVisitors,
                'average_duration' => round($avgDuration ?? 0, 2),
                'total_duration' => $totalDuration ?? 0,
                'completion_rate' => $totalCheckins > 0 ? round(($totalCheckouts / $totalCheckins) * 100, 2) : 0
            ];
        });
    }

    public static function getActiveVisitorsCount($eventId = null)
    {
        $query = DB::table('visitor_logs as vl1')
            ->where('vl1.action', self::ACTION_CHECKIN)
            ->whereNotExists(function($query) {
                $query->select(DB::raw(1))
                    ->from('visitor_logs as vl2')
                    ->whereRaw('vl2.registration_id = vl1.registration_id')
                    ->where('vl2.action', self::ACTION_CHECKOUT)
                    ->whereRaw('vl2.created_at > vl1.created_at');
            })
            ->whereNull('vl1.deleted_at');

        if ($eventId) {
            $query->join('registrations', 'registrations.id', '=', 'vl1.registration_id')
                  ->where('registrations.event_id', $eventId);
        }

        return $query->count();
    }

    public static function getActiveVisitors($eventId = null)
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
            ->where('vl1.action', self::ACTION_CHECKIN)
            ->whereNotExists(function($query) {
                $query->select(DB::raw(1))
                    ->from('visitor_logs as vl2')
                    ->whereRaw('vl2.registration_id = vl1.registration_id')
                    ->where('vl2.action', self::ACTION_CHECKOUT)
                    ->whereRaw('vl2.created_at > vl1.created_at');
            })
            ->whereNull('vl1.deleted_at');

        if ($eventId) {
            $query->where('r.event_id', $eventId);
        }

        return $query->get();
    }

    public static function getHourlyDistribution($eventId = null, $date = null)
    {
        $date = $date ?? today()->format('Y-m-d');
        $cacheKey = "hourly_distribution_{$eventId}_{$date}";

        return Cache::remember($cacheKey, 300, function() use ($eventId, $date) {
            $query = self::selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
                        ->whereDate('created_at', $date)
                        ->where('action', self::ACTION_CHECKIN)
                        ->groupBy('hour');

            if ($eventId) {
                $query->byEvent($eventId);
            }

            $data = $query->get()->keyBy('hour');
            
            $distribution = [];
            for ($i = 0; $i < 24; $i++) {
                $distribution[$i] = $data->get($i)?->count ?? 0;
            }

            return $distribution;
        });
    }

    public static function getPeakHours($eventId = null, $dateFrom = null, $dateTo = null)
    {
        $dateFrom = $dateFrom ?? today()->format('Y-m-d');
        $dateTo = $dateTo ?? today()->format('Y-m-d');
        
        $query = self::selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
                    ->whereBetween(DB::raw('DATE(created_at)'), [$dateFrom, $dateTo])
                    ->where('action', self::ACTION_CHECKIN)
                    ->groupBy('hour')
                    ->orderBy('count', 'desc');

        if ($eventId) {
            $query->byEvent($eventId);
        }

        $peakData = $query->get();
        
        if ($peakData->isEmpty()) {
            return [];
        }

        $maxCount = $peakData->first()->count;
        $peakHours = [];
        
        foreach ($peakData as $data) {
            if ($data->count === $maxCount) {
                $peakHours[$data->hour] = $data->count;
            }
        }

        return $peakHours;
    }

    public static function getDailyTrends($eventId = null, $days = 7)
    {
        $dateFrom = now()->subDays($days)->startOfDay();
        $dateTo = now()->endOfDay();

        $query = self::selectRaw('DATE(created_at) as date, action, COUNT(*) as count')
                    ->whereBetween('created_at', [$dateFrom, $dateTo])
                    ->groupBy(['date', 'action'])
                    ->orderBy('date');

        if ($eventId) {
            $query->byEvent($eventId);
        }

        return $query->get()->groupBy('date');
    }

    public static function getCompletionRate($eventId = null, $date = null)
    {
        $query = self::query();
        
        if ($eventId) {
            $query->byEvent($eventId);
        }
        
        if ($date) {
            $query->whereDate('created_at', $date);
        }

        $checkins = (clone $query)->checkins()->count();
        $checkouts = (clone $query)->checkouts()->count();

        return $checkins > 0 ? round(($checkouts / $checkins) * 100, 2) : 0;
    }

    // Instance Methods
    public function calculateDuration()
    {
        if ($this->action !== self::ACTION_CHECKOUT) {
            return null;
        }

        $checkin = self::where('registration_id', $this->registration_id)
                      ->where('action', self::ACTION_CHECKIN)
                      ->where('created_at', '<', $this->created_at)
                      ->latest()
                      ->first();

        if (!$checkin) {
            return null;
        }

        $duration = $this->created_at->diffInMinutes($checkin->created_at);
        
        $this->update(['duration_minutes' => $duration]);
        
        return $duration;
    }

    public function isRecentDuplicate($minutes = 2)
    {
        return self::where('registration_id', $this->registration_id)
                  ->where('action', $this->action)
                  ->where('created_at', '>=', now()->subMinutes($minutes))
                  ->where('id', '!=', $this->id)
                  ->exists();
    }

    public function getRelatedLogs($limit = 10)
    {
        return self::where('registration_id', $this->registration_id)
                  ->where('id', '!=', $this->id)
                  ->with(['creator'])
                  ->orderBy('created_at', 'desc')
                  ->limit($limit)
                  ->get();
    }

    public function getLastAction()
    {
        return self::where('registration_id', $this->registration_id)
                  ->where('created_at', '<', $this->created_at)
                  ->latest()
                  ->first();
    }

    public function getNextAction()
    {
        return self::where('registration_id', $this->registration_id)
                  ->where('created_at', '>', $this->created_at)
                  ->oldest()
                  ->first();
    }

    // Event Handlers
    protected static function booted()
    {
        static::creating(function ($log) {
            // Set IP address and user agent if not provided
            if (!$log->ip_address && request()) {
                $log->ip_address = request()->ip();
            }
            
            if (!$log->user_agent && request()) {
                $log->user_agent = request()->userAgent();
            }
        });

        static::created(function ($log) {
            // Calculate duration for checkout actions
            if ($log->action === self::ACTION_CHECKOUT) {
                $log->calculateDuration();
            }
            
            // Clear related caches
            self::clearStatisticsCache();
        });

        static::updated(function ($log) {
            self::clearStatisticsCache();
        });

        static::deleted(function ($log) {
            self::clearStatisticsCache();
        });
    }

    public static function clearStatisticsCache()
    {
        $patterns = [
            'visitor_log_stats_*',
            'hourly_distribution_*',
            'filtered_stats_*',
            'analytics_data_*'
        ];

        // In a real application, you might want to use a more sophisticated cache clearing mechanism
        // For now, we'll just clear some common cache keys
        Cache::forget('visitor_log_stats_all_' . today()->format('Y-m-d'));
        Cache::forget('hourly_distribution_null_' . today()->format('Y-m-d'));
    }

    // Helper method to get visitor status
    public function getVisitorStatus()
    {
        $lastLog = self::where('registration_id', $this->registration_id)
                      ->latest()
                      ->first();

        if (!$lastLog) {
            return 'never_visited';
        }

        if ($lastLog->action === self::ACTION_CHECKIN) {
            return 'checked_in';
        }

        return 'checked_out';
    }

    // Export helper
    public function toExportArray()
    {
        return [
            'ID' => $this->id,
            'Registration ID' => $this->registration_id,
            'User Name' => $this->registration->user->name,
            'Email' => $this->registration->user->email,
            'Event' => $this->registration->event->name,
            'Action' => ucfirst($this->action),
            'Timestamp' => $this->created_at->format('Y-m-d H:i:s'),
            'Duration (minutes)' => $this->duration_minutes ?? '',
            'Admin Note' => $this->admin_note ?? '',
            'Created By' => $this->creator->name ?? 'System',
            'IP Address' => $this->ip_address ?? '',
            'QR Scanned' => $this->qr_scanned ? 'Yes' : 'No',
            'Method' => $this->device_info['method'] ?? ($this->qr_scanned ? 'QR Code' : 'Manual')
        ];
    }
}