<?php

namespace App\Models;

use App\Enums\PartnerLevel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * Gallery Model
 *
 * Represents a gallery containing a combination of Items and Details.
 * Galleries organize mixed content types and provide translation and partner support.
 *
 * @property string $id Primary key (UUID)
 * @property string $internal_name Internal name for gallery
 * @property string|null $backward_compatibility Legacy ID for migration/compatibility
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Gallery extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'internal_name',
        'backward_compatibility',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [];

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
     * Get all translations for this gallery.
     */
    public function translations(): HasMany
    {
        return $this->hasMany(GalleryTranslation::class);
    }

    /**
     * Get all items belonging to this gallery.
     */
    public function items(): MorphToMany
    {
        return $this->morphedByMany(Item::class, 'galleryable')
            ->withPivot(['order', 'backward_compatibility'])
            ->withTimestamps()
            ->orderBy('pivot_order');
    }

    /**
     * Get all details belonging to this gallery.
     */
    public function details(): MorphToMany
    {
        return $this->morphedByMany(Detail::class, 'galleryable')
            ->withPivot(['order', 'backward_compatibility'])
            ->withTimestamps()
            ->orderBy('pivot_order');
    }

    /**
     * Get all partners associated with this gallery.
     */
    public function partners(): BelongsToMany
    {
        return $this->belongsToMany(Partner::class, 'gallery_partner')
            ->withPivot(['level', 'backward_compatibility'])
            ->withTimestamps()
            ->using(GalleryPartner::class);
    }

    /**
     * Get the default context translation for this gallery in a specific language.
     */
    public function getDefaultTranslation(string $languageId): ?GalleryTranslation
    {
        return $this->translations()
            ->defaultContext()
            ->forLanguage($languageId)
            ->first();
    }

    /**
     * Get a contextualized translation for this gallery.
     */
    public function getContextualizedTranslation(string $languageId, string $contextId): ?GalleryTranslation
    {
        return $this->translations()
            ->forLanguage($languageId)
            ->forContext($contextId)
            ->first();
    }

    /**
     * Get translation with fallback logic: try specific context, then default context.
     */
    public function getTranslationWithFallback(string $languageId, ?string $contextId = null): ?GalleryTranslation
    {
        if ($contextId) {
            $translation = $this->getContextualizedTranslation($languageId, $contextId);
            if ($translation) {
                return $translation;
            }
        }

        return $this->getDefaultTranslation($languageId);
    }

    /**
     * Get partners by level.
     */
    public function partnersByLevel(PartnerLevel $level): BelongsToMany
    {
        return $this->partners()->wherePivot('level', $level->value);
    }

    /**
     * Get direct partners (level: partner).
     */
    public function directPartners(): BelongsToMany
    {
        return $this->partnersByLevel(PartnerLevel::PARTNER);
    }

    /**
     * Get associated partners (level: associated_partner).
     */
    public function associatedPartners(): BelongsToMany
    {
        return $this->partnersByLevel(PartnerLevel::ASSOCIATED_PARTNER);
    }

    /**
     * Get minor contributors (level: minor_contributor).
     */
    public function minorContributors(): BelongsToMany
    {
        return $this->partnersByLevel(PartnerLevel::MINOR_CONTRIBUTOR);
    }

    /**
     * Get all gallery content (items and details combined).
     * This returns a collection of mixed Item and Detail models.
     */
    public function getAllContent()
    {
        // Get all items with their pivot data
        $items = $this->items()->get()->map(function ($item) {
            $item->pivot_type = 'item';

            return $item;
        });

        // Get all details with their pivot data
        $details = $this->details()->get()->map(function ($detail) {
            $detail->pivot_type = 'detail';

            return $detail;
        });

        // Combine and sort by order
        return $items->concat($details)->sortBy('pivot.order');
    }
}
