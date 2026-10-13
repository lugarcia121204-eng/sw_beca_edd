<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasOrderableRelations
{
    /**
     * Apply ordering to the query, supporting both direct columns and relations.
     *
     * @param Builder $query
     * @param string $order
     * @param string $direction
     * @return Builder
     */
    protected function applyOrdering(Builder $query, string $order = 'id', string $direction = 'desc'): Builder
    {
        $orderableRelations = $this->getOrderableRelations();

        if (isset($orderableRelations[$order])) {
            [$relation, $relatedColumn, $orderColumn] = $orderableRelations[$order];

            return $query->join($relation, function ($join) use ($relation, $relatedColumn) {
                $join->on('users.id', '=', "{$relation}.{$relatedColumn}");
            })->orderBy("{$relation}.{$orderColumn}", $direction)
              ->select('users.*');
        }

        return $query->orderBy($order, $direction);
    }

    /**
     * Define orderable relations.
     * Override this method in the controller to specify which relations can be ordered.
     *
     * @return array
     */
    protected function getOrderableRelations(): array
    {
        return [];
    }
}
