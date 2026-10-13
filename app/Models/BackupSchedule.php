<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class BackupSchedule extends Model
{
    protected $fillable = [
        'frequency',
        'run_time',
        'email_notifications',
        'is_active',
        'backup_mode',
        'last_run_at',
        'next_run_at',
    ];

    protected $casts = [
        'email_notifications' => 'boolean',
        'is_active'           => 'boolean',
        'last_run_at'         => 'datetime',
        'next_run_at'         => 'datetime',
    ];

    /**
     * Retrieve the single schedule config row, or create a default one.
     */
    public static function getConfig(): self
    {
        return self::first() ?? self::create([
            'frequency'           => 'weekly',
            'run_time'            => '12:00:00',
            'email_notifications' => false,
            'is_active'           => true,
            'backup_mode'         => 'full',
        ]);
    }

    /**
     * Calculate the next run date/time based on frequency and run_time.
     */
    public function calculateNextRun(): Carbon
    {
        $now  = Carbon::now();
        $time = Carbon::parse($this->run_time);

        $next = $now->copy()->setTime($time->hour, $time->minute, 0);

        // If the calculated time is already past today, advance by the period
        if ($next->lte($now)) {
            $next = match ($this->frequency) {
                'daily'   => $next->addDay(),
                'weekly'  => $next->addWeek(),
                'monthly' => $next->addMonth(),
                default   => $next->addWeek(),
            };
        }

        return $next;
    }

    /**
     * Spanish label for frequency.
     */
    public function getFrequencyLabelAttribute(): string
    {
        return match ($this->frequency) {
            'daily'   => 'Diario',
            'weekly'  => 'Semanal',
            'monthly' => 'Mensual',
            default   => ucfirst($this->frequency),
        };
    }
}
