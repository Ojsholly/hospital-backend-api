<?php

namespace App\QueryFilters;

class Status extends Filter
{
    /**
     * Filter the incoming request by status.
     */
    protected function applyFilter($builder): mixed
    {
        $status = request()->query('status');                   // Get the status from the query string. This value is expected to be separated by a comma in case of multiple values.

        return $builder->when($status, function ($query) use ($status) {     // Check if the status is present in the query string.
        $query->whereIn('status', explode(',', $status));   // If present, filter the query by the status.
        });
    }
}
