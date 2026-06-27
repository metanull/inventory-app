<?php

namespace App\Models;

use Database\Factories\ItemItemLinkFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $id
 */
class ItemItemLink extends Model
{
    /** @use HasFactory<ItemItemLinkFactory> */
    use HasFactory;

    use HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'source_id',
        'target_id',
        'context_id',
    ];

    /**
     * Get the columns that should automatically receive a unique identifier.
     *
     * @return array<int, string>
     */
    public function uniqueIds(): array
    {
        return ['id'];
    }

    /**
     * The source item of the link (the item initiating the link).
     *
     * @return BelongsTo<Item, $this>
     */
    public function source(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'source_id');
    }

    /**
     * The target item of the link (the item receiving the link).
     *
     * @return BelongsTo<Item, $this>
     */
    public function target(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'target_id');
    }

    /**
     * The context in which the link exists.
     *
     * @return BelongsTo<Context, $this>
     */
    public function context(): BelongsTo
    {
        return $this->belongsTo(Context::class);
    }

    /**
     * Get all translations for this link.
     *
     * @return HasMany<ItemItemLinkTranslation, $this>
     */
    public function translations(): HasMany
    {
        return $this->hasMany(ItemItemLinkTranslation::class);
    }

    /**
     * Scope to filter links by source item.
     *
     * @param  Builder<static>  $query
     * @param  string|Item  $source  The source Item ID or Item model instance
     * @return Builder<static>
     */
    public function scopeFromSource(Builder $query, $source): Builder
    {
        $sourceId = $source instanceof Item ? $source->id : $source;

        return $query->where('source_id', $sourceId);
    }

    /**
     * Scope to filter links by target item.
     *
     * @param  Builder<static>  $query
     * @param  string|Item  $target  The target Item ID or Item model instance
     * @return Builder<static>
     */
    public function scopeToTarget(Builder $query, $target): Builder
    {
        $targetId = $target instanceof Item ? $target->id : $target;

        return $query->where('target_id', $targetId);
    }

    /**
     * Scope to filter links by context.
     *
     * @param  Builder<static>  $query
     * @param  string|Context  $context  The Context ID or Context model instance
     * @return Builder<static>
     */
    public function scopeInContext(Builder $query, $context): Builder
    {
        $contextId = $context instanceof Context ? $context->id : $context;

        return $query->where('context_id', $contextId);
    }

    /**
     * Scope to filter links involving a specific item (as either source or target).
     *
     * @param  Builder<static>  $query
     * @param  string|Item  $item  The Item ID or Item model instance
     * @return Builder<static>
     */
    public function scopeInvolvingItem(Builder $query, $item): Builder
    {
        $itemId = $item instanceof Item ? $item->id : $item;

        return $query->where(function (Builder $query) use ($itemId) {
            $query->where('source_id', $itemId)
                ->orWhere('target_id', $itemId);
        });
    }
}
