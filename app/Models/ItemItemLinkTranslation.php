<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ItemItemLinkTranslation Model
 *
 * Represents language-specific translations for item-to-item links.
 * Stores descriptions for the link in both directions (source→target and target→source).
 */
class ItemItemLinkTranslation extends Model
{
    use HasFactory;
    use HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'item_item_link_translations';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'item_item_link_id',
        'language_id',
        'description',
        'reciprocal_description',
        'backward_compatibility',
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
     * The item-item link that owns this translation.
     */
    public function itemItemLink(): BelongsTo
    {
        return $this->belongsTo(ItemItemLink::class);
    }

    /**
     * The language of this translation.
     */
    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    /**
     * Scope to filter translations by item-item link.
     *
     * @param  string|ItemItemLink  $link  The ItemItemLink ID or model instance
     */
    public function scopeForLink(Builder $query, $link): Builder
    {
        $linkId = $link instanceof ItemItemLink ? $link->id : $link;

        return $query->where('item_item_link_id', $linkId);
    }

    /**
     * Scope to filter translations by language.
     *
     * @param  string|Language  $language  The Language ID or model instance
     */
    public function scopeForLanguage(Builder $query, $language): Builder
    {
        $languageId = $language instanceof Language ? $language->id : $language;

        return $query->where('language_id', $languageId);
    }
}
