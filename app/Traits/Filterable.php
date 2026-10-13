<?php

namespace App\Traits;

trait Filterable
{
    /**
     * Get base filters from request query parameters.
     *
     * @param array $queryParams
     * @return object
     */
    protected function getFiltersBase(array $queryParams = []): object
    {
        return (object)[
            'search' => $queryParams['search'] ?? null,
            'order' => $queryParams['order'] ?? 'id',
            'direction' => $queryParams['direction'] ?? 'desc',
            'rows' => (int)($queryParams['rows'] ?? 10),
            'withTrashed' => $queryParams['withTrashed'] ?? false,
            'sort_field' => $queryParams['sort_field'] ?? null,
            'sort_direction' => $queryParams['sort_direction'] ?? 'asc',
        ];
    }
}
