<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

/**
 * Trait for models that have display_order column for sibling ordering.
 *
 * This trait provides methods to manage display ordering:
 * - Get next available order for new records
 * - Move items up/down in the order
 * - Tighten ordering to eliminate gaps (renumber to 1, 2, 3, ...)
 * - Auto-tighten after deletion
 *
 * Each model must implement getSiblingsQuery() to define how siblings are grouped.
 */
trait HasDisplayOrder
{
    /**
     * Boot the trait - registers delete hook for auto-tightening.
     */
    protected static function bootHasDisplayOrder(): void
    {
        static::deleted(function ($model) {
            // Get any remaining sibling to call tightenOrdering
            $remaining = $model->getSiblingsQuery()->first();
            if ($remaining) {
                $remaining->tightenOrdering();
            }
        });
    }

    /**
     * Get a query builder scoped to this model's siblings.
     * Siblings share the same parent/grouping and compete for display_order.
     *
     * @return Builder<static>
     */
    abstract protected function getSiblingsQuery(): Builder;

    /**
     * Get the next available display order for a new sibling.
     */
    public function getNextDisplayOrder(): int
    {
        $maxOrder = $this->getSiblingsQuery()->max('display_order');

        return $maxOrder ? $maxOrder + 1 : 1;
    }

    /**
     * Get the next available display order for a new sibling (static version).
     * Useful when creating new records before the model instance exists.
     *
     * @param  array<string, mixed>  $groupValues  Key-value pairs for the grouping columns
     */
    public static function getNextDisplayOrderFor(array $groupValues): int
    {
        $query = static::query();
        foreach ($groupValues as $column => $value) {
            if ($value === null) {
                $query->whereNull($column);
            } else {
                $query->where($column, $value);
            }
        }

        $maxOrder = $query->max('display_order');

        return $maxOrder ? $maxOrder + 1 : 1;
    }

    /**
     * Move this item up in the display order (decrease order number).
     */
    public function moveUp(): bool
    {
        return $this->moveInDirection('up');
    }

    /**
     * Move this item down in the display order (increase order number).
     */
    public function moveDown(): bool
    {
        return $this->moveInDirection('down');
    }

    /**
     * Move item in specified direction within a transaction.
     */
    protected function moveInDirection(string $direction): bool
    {
        return $this->getConnection()->transaction(function () use ($direction) {
            // Lock current item first to prevent race conditions
            $currentItem = static::where('id', $this->id)->lockForUpdate()->first();
            if (! $currentItem) {
                return false;
            }

            $currentOrder = $currentItem->display_order;

            if ($direction === 'up') {
                if ($currentOrder <= 1) {
                    return false;
                }

                $targetItem = $this->getSiblingsQuery()
                    ->where('display_order', $currentOrder - 1)
                    ->lockForUpdate()
                    ->first();
            } else { // down
                $targetItem = $this->getSiblingsQuery()
                    ->where('display_order', $currentOrder + 1)
                    ->lockForUpdate()
                    ->first();
            }

            if (! $targetItem) {
                return false;
            }

            // Swap display orders
            $targetOrder = $targetItem->display_order;
            $targetItem->update(['display_order' => $currentOrder]);
            $currentItem->update(['display_order' => $targetOrder]);

            // Update this instance with the new order
            $this->display_order = $targetOrder;

            return true;
        });
    }

    /**
     * Tighten the display order for all siblings, eliminating gaps.
     * Renumbers to a clean sequence: 1, 2, 3, ...
     */
    public function tightenOrdering(): void
    {
        $this->getConnection()->transaction(function () {
            $siblings = $this->getSiblingsQuery()
                ->orderBy('display_order')
                ->lockForUpdate()
                ->get();

            foreach ($siblings as $index => $sibling) {
                $newOrder = $index + 1;
                if ($sibling->display_order !== $newOrder) {
                    $sibling->update(['display_order' => $newOrder]);
                }
            }
        });
    }

    /**
     * Move this item to a specific position in the display order.
     * Other items will be shifted accordingly.
     */
    public function moveToPosition(int $newPosition): bool
    {
        if ($newPosition < 1) {
            return false;
        }

        return $this->getConnection()->transaction(function () use ($newPosition) {
            $currentItem = static::where('id', $this->id)->lockForUpdate()->first();
            if (! $currentItem) {
                return false;
            }

            $currentOrder = $currentItem->display_order;

            if ($currentOrder === $newPosition) {
                return true; // Already at requested position
            }

            $maxOrder = $this->getSiblingsQuery()->lockForUpdate()->max('display_order') ?? 0;
            $targetPosition = min($newPosition, $maxOrder);

            if ($currentOrder < $targetPosition) {
                // Moving down: shift items between current and target up
                $this->getSiblingsQuery()
                    ->where('display_order', '>', $currentOrder)
                    ->where('display_order', '<=', $targetPosition)
                    ->decrement('display_order');
            } else {
                // Moving up: shift items between target and current down
                $this->getSiblingsQuery()
                    ->where('display_order', '>=', $targetPosition)
                    ->where('display_order', '<', $currentOrder)
                    ->increment('display_order');
            }

            $currentItem->update(['display_order' => $targetPosition]);
            $this->display_order = $targetPosition;

            return true;
        });
    }
}
