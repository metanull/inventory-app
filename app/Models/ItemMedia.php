<?php

namespace App\Models;

use App\Enums\MediaType;
use App\Traits\HasDisplayOrder;
use Database\Factories\ItemMediaFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemMedia extends Model
{
    /** @use HasFactory<ItemMediaFactory> */
    use HasDisplayOrder, HasFactory, HasUuids;

    protected $table = 'item_media';

    protected $fillable = [
        'item_id',
        'language_id',
        'type',
        'title',
        'description',
        'url',
        'display_order',
        'extra',
        'backward_compatibility',
    ];

    protected $casts = [
        'type' => MediaType::class,
        'display_order' => 'integer',
        'extra' => 'object',
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
     * Get a query builder scoped to this media's siblings (same item_id and type).
     *
     * @return Builder<static>
     */
    protected function getSiblingsQuery(): Builder
    {
        /** @var Builder<static> $query */
        $query = static::where('item_id', $this->item_id)
            ->where('type', $this->type);

        return $query;
    }

    /**
     * Get the item this media belongs to.
     *
     * @return BelongsTo<Item, $this>
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Get the language of this media.
     *
     * @return BelongsTo<Language, $this>
     */
    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}
