<?php

namespace App\Models;

use App\Events\ItemTranslationSaved;
use App\Traits\HasJsonFields;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

/**
 * ItemTranslation Model
 *
 * Represents language and context-specific translations for Items.
 * Supports internationalization and contextualization of Item content.
 *
 * @property string $item_id
 * @property string $language_id
 * @property string $context_id
 * @property string|null $name
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class ItemTranslation extends Model
{
    use HasFactory, HasJsonFields, HasUuids;

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::saved(function ($itemTranslation) {
            event(new ItemTranslationSaved($itemTranslation));
        });
    }

    /**
     * Delete the model from the database.
     * Ensures atomic deletion of the ItemTranslation and its spelling links.
     *
     * @return bool|null
     *
     * @throws \LogicException
     */
    public function delete()
    {
        // If the model doesn't exist, nothing to delete
        if (! $this->exists) {
            return false;
        }

        // Fire the deleting event (for other listeners, not for spelling detachment)
        if ($this->fireModelEvent('deleting') === false) {
            return false;
        }

        // Perform atomic deletion: detach spellings AND delete the model in one transaction
        return DB::transaction(function () {
            // First, detach all spelling links
            $this->spellings()->detach();

            // Then perform the actual deletion
            $this->performDeleteOnModel();

            // Mark the model as non-existing
            $this->exists = false;

            // Fire the deleted event
            $this->fireModelEvent('deleted', false);

            return true;
        });
    }

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'item_translations';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'item_id',
        'language_id',
        'context_id',
        'name',
        'alternate_name',
        'description',
        'type',
        'holder',
        'owner',
        'initial_owner',
        'dates',
        'location',
        'dimensions',
        'place_of_production',
        'method_for_datation',
        'method_for_provenance',
        'provenance',
        'obtention',
        'bibliography',
        'author_id',
        'text_copy_editor_id',
        'translator_id',
        'translation_copy_editor_id',
        'backward_compatibility',
        'extra',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'extra' => 'object',
    ];

    /**
     * Get the unique identifiers for the model.
     *
     * @return array<int, string>
     */
    public function uniqueIds(): array
    {
        return ['id'];
    }

    /**
     * Get the extra field decoded as an associative array.
     */
    protected function extraDecoded(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->normalizedJson('extra')
        );
    }

    /** @return BelongsTo<Item, $this> */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /** @return BelongsTo<Language, $this> */
    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    /** @return BelongsTo<Context, $this> */
    public function context(): BelongsTo
    {
        return $this->belongsTo(Context::class);
    }

    /**
     * Get the author of the translation.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class, 'author_id');
    }

    /**
     * Get the text copy editor.
     */
    public function textCopyEditor(): BelongsTo
    {
        return $this->belongsTo(Author::class, 'text_copy_editor_id');
    }

    /**
     * Get the translator.
     */
    public function translator(): BelongsTo
    {
        return $this->belongsTo(Author::class, 'translator_id');
    }

    /**
     * Get the translation copy editor.
     */
    public function translationCopyEditor(): BelongsTo
    {
        return $this->belongsTo(Author::class, 'translation_copy_editor_id');
    }

    /**
     * Get the glossary spellings linked to this item translation.
     */
    public function spellings(): BelongsToMany
    {
        return $this->belongsToMany(GlossarySpelling::class, 'item_translation_spelling', 'item_translation_id', 'spelling_id')
            ->withTimestamps();
    }

    /**
     * @param Builder<static> $query
     * @return Builder<static>
     */
    public function scopeDefaultContext(Builder $query): Builder
    {
        return $query->whereHas('context', function ($query) {
            $query->where('is_default', true);
        });
    }

    /**
     * @param Builder<static> $query
     * @return Builder<static>
     */
    public function scopeForLanguage(Builder $query, string $languageId): Builder
    {
        return $query->where('language_id', $languageId);
    }

    /**
     * @param Builder<static> $query
     * @return Builder<static>
     */
    public function scopeForContext(Builder $query, string $contextId): Builder
    {
        return $query->where('context_id', $contextId);
    }

    /**
     * Get sibling translations (other translations of the same item).
     */
    public function siblingTranslations(): HasMany
    {
        return $this->hasMany(static::class, 'item_id', 'item_id');
    }
}
