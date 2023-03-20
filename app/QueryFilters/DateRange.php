<?php

namespace App\QueryFilters;

use Carbon\Carbon;

class DateRange extends Filter
{
    /**
     * Filter the incoming request by date range.
     */
    protected function applyFilter($builder): mixed
    {
        $dateRange = request()->query('date_range');                   // Get the date range from the query string. The date values are expected to be separated by a comma.

        if ($dateRange !== null) {
            $range = explode(',', $dateRange);                              // Split the date range into an array.

            $from = Carbon::parse($range[0])->startOfDay()->toDateTimeString();
            $to = Carbon::parse($range[1])->endOfDay()->toDateTimeString();

            return $builder->whereBetween('created_at', [$from, $to]);     // Filter the builder by the date range
        }
    }
}
