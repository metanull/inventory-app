<?php

namespace App\Models;

use App\Traits\HasDisplayOrder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemDocument extends Model
{
    use HasDisplayOrder, HasFactory, HasUuids;

    protected $fillable = [
        'item_id',
        'language_id',
        'path',
        'original_name',
        'mime_type',
        'size',
        'title',
        'display_order',
        'extra',
        'backward_compatibility',
    ];

    protected $casts = [
        'size' => 'integer',
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
     * Get a query builder scoped to this document's siblings (same item_id).
     *
     * @return Builder<static>
     */
    protected function getSiblingsQuery(): Builder
    {
        return static::where('item_id', $this->item_id);
    }

    /**
     * Get the item this document belongs to.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Get the language of this document.
     */
    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}
