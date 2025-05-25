<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class VisitorLog extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'registration_id',
        'action',
        'admin_note',
        'created_by',
        'ip_address',
        'user_agent',
        'location_data',
        'device_info',
        'qr_scanned',
        'duration_minutes'
    ];

    protected $casts = [
        'location_data' => 'array',
        'device_info' => 'array',
        'qr_scanned' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    const ACTION_CHECKIN = 'checkin';
    const ACTION_CHECKOUT = 'checkout';

    // Relationships
    public function registration()
    {
        return $this->belongsTo(Registration::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
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
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek()
        ]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', Carbon::now()->month)
                    ->whereYear('created_at', Carbon::now()->year);
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

    // Accessors
    public function getFormattedCreatedAtAttribute()
    {
        return $this->created_at->format('M d, Y H:i:s');
    }

    public function getActionBadgeAttribute()
    {
        return $this->action === self::ACTION_CHECKIN 
            ? '<span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded-full">Check-in</span>'
            : '<span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded-full">Check-out</span>';
    }

    public function getDurationFormattedAttribute()
    {
        if (!$this->duration_minutes) return null;
        
        $hours = floor($this->duration_minutes / 60);
        $minutes = $this->duration_minutes % 60;
        
        if ($hours > 0) {
            return "{$hours}h {$minutes}m";
        }
        return "{$minutes}m";
    }

    // Methods
    public function isWithinEventTime()
    {
        $event = $this->registration->event;
        $now = now();
        return $now->between($event->start_date, $event->end_date);
    }

    public function calculateDuration()
    {
        if ($this->action !== self::ACTION_CHECKOUT) {
            return null;
        }

        $checkinLog = static::where('registration_id', $this->registration_id)
            ->where('action', self::ACTION_CHECKIN)
            ->where('created_at', '<', $this->created_at)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($checkinLog) {
            $duration = $this->created_at->diffInMinutes($checkinLog->created_at);
            $this->update(['duration_minutes' => $duration]);
            return $duration;
        }

        return null;
    }

    public static function getActiveVisitors($eventId = null)
    {
        $query = static::select('registration_id')
            ->where('action', self::ACTION_CHECKIN)
            ->whereNotIn('registration_id', function($subQuery) {
                $subQuery->select('registration_id')
                    ->from('visitor_logs')
                    ->where('action', self::ACTION_CHECKOUT)
                    ->whereColumn('created_at', '>', function($innerQuery) {
                        $innerQuery->select('created_at')
                            ->from('visitor_logs as vl2')
                            ->whereColumn('vl2.registration_id', 'visitor_logs.registration_id')
                            ->where('vl2.action', self::ACTION_CHECKIN)
                            ->orderBy('created_at', 'desc')
                            ->limit(1);
                    });
            });

        if ($eventId) {
            $query->byEvent($eventId);
        }

        return $query->distinct()->count();
    }

    public static function getStatistics($eventId = null, $dateFrom = null, $dateTo = null)
    {
        $query = static::query();

        if ($eventId) {
            $query->byEvent($eventId);
        }

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $checkins = (clone $query)->checkins()->count();
        $checkouts = (clone $query)->checkouts()->count();
        $todayCheckins = (clone $query)->checkins()->today()->count();
        $todayCheckouts = (clone $query)->checkouts()->today()->count();
        $thisWeekCheckins = (clone $query)->checkins()->thisWeek()->count();
        $thisMonthCheckins = (clone $query)->checkins()->thisMonth()->count();
        $activeVisitors = static::getActiveVisitors($eventId);

        return [
            'total_checkins' => $checkins,
            'total_checkouts' => $checkouts,
            'today_checkins' => $todayCheckins,
            'today_checkouts' => $todayCheckouts,
            'this_week_checkins' => $thisWeekCheckins,
            'this_month_checkins' => $thisMonthCheckins,
            'active_visitors' => $activeVisitors,
            'average_duration' => static::getAverageDuration($eventId, $dateFrom, $dateTo),
            'peak_hours' => static::getPeakHours($eventId, $dateFrom, $dateTo),
        ];
    }

    public static function getAverageDuration($eventId = null, $dateFrom = null, $dateTo = null)
    {
        $query = static::checkouts()->whereNotNull('duration_minutes');

        if ($eventId) {
            $query->byEvent($eventId);
        }

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        return $query->avg('duration_minutes') ?? 0;
    }

    public static function getPeakHours($eventId = null, $dateFrom = null, $dateTo = null)
    {
        $query = static::checkins();

        if ($eventId) {
            $query->byEvent($eventId);
        }

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        return $query->selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
            ->groupBy('hour')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get()
            ->mapWithKeys(function($item) {
                return [sprintf('%02d:00', $item->hour) => $item->count];
            })
            ->toArray();
    }

    public static function getHourlyDistribution($eventId = null, $date = null)
    {
        $query = static::checkins();

        if ($eventId) {
            $query->byEvent($eventId);
        }

        if ($date) {
            $query->whereDate('created_at', $date);
        } else {
            $query->today();
        }

        $hourlyData = $query->selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->keyBy('hour');

        $distribution = [];
        for ($i = 0; $i < 24; $i++) {
            $distribution[sprintf('%02d:00', $i)] = $hourlyData->get($i)?->count ?? 0;
        }

        return $distribution;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($log) {
            $log->ip_address = request()->ip();
            $log->user_agent = request()->userAgent();
        });

        static::created(function ($log) {
            if ($log->action === self::ACTION_CHECKOUT) {
                $log->calculateDuration();
            }
        });
    }
}