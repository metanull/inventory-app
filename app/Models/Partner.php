<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Partner extends Model
{
    use HasFactory;
    use HasUuids;

    // No model-level eager loads. Use request-scoped includes in controllers.

    protected $fillable = [
        // 'id',
        'internal_name',
        'type',
        'backward_compatibility',
        'country_id',
        // GPS Location
        'latitude',
        'longitude',
        'map_zoom',
        // Relationships
        'project_id',
        'monument_item_id',
        // Visibility
        'visible',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'map_zoom' => 'integer',
        'visible' => 'boolean',
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
     * Get the items belonging to this partner.
     */
    public function items(): HasMany
    {
        return $this->hasMany(Item::class)->chaperone('partner');
    }

    /**
     * The country of the partner.
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Get the project this partner is associated with.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the monument item this partner is linked to.
     */
    public function monumentItem(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'monument_item_id');
    }

    /**
     * Get the translations for this partner.
     */
    public function translations(): HasMany
    {
        return $this->hasMany(PartnerTranslation::class);
    }

    /**
     * Get the images for this partner.
     */
    public function partnerImages(): HasMany
    {
        return $this->hasMany(PartnerImage::class)->orderBy('display_order');
    }

    /**
     * Get the collections this partner is associated with.
     */
    public function collections(): BelongsToMany
    {
        return $this->belongsToMany(Collection::class, 'collection_partner')
            ->withPivot(['collection_type', 'level', 'visible', 'relationship_type'])
            ->withTimestamps()
            ->using(CollectionPartner::class);
    }

    /**
     * Scope a query to only include visible partners.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeVisible($query)
    {
        return $query->where('visible', true);
    }

    /**
     * Get the default translation for a specific language.
     */
    public function getDefaultTranslation(string $languageId): ?PartnerTranslation
    {
        return $this->translations()
            ->defaultContext()
            ->forLanguage($languageId)
            ->first();
    }

    /**
     * Get a contextualized translation for a specific language and context.
     */
    public function getContextualizedTranslation(string $languageId, string $contextId): ?PartnerTranslation
    {
        return $this->translations()
            ->forLanguage($languageId)
            ->forContext($contextId)
            ->first();
    }

    /**
     * Get a translation with fallback logic.
     * First tries the specified context, then falls back to default context.
     */
    public function getTranslationWithFallback(string $languageId, string $contextId): ?PartnerTranslation
    {
        // Try to get the specified context translation
        $translation = $this->getContextualizedTranslation($languageId, $contextId);

        // If not found, fall back to default context
        if (! $translation) {
            $translation = $this->getDefaultTranslation($languageId);
        }

        return $translation;
    }
}
