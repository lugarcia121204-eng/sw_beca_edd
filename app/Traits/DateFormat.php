<?php

namespace App\Traits;

use Carbon\Carbon;

trait DateFormat
{
    /**
     * Format date to text format
     *
     * @param string|Carbon $date
     * @return string
     */
    public function textFormatDate($date): string
    {
        if (!$date) {
            return '';
        }

        if (is_string($date)) {
            $date = Carbon::parse($date);
        }

        return $date->format('d/m/Y H:i');
    }
}
