<?php

namespace App\Models;

use App\Contracts\StreamableImageFile;
use App\Traits\HasDisplayOrder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ItemImage extends Model implements StreamableImageFile
{
    use HasDisplayOrder, HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'item_id',
        'path',
        'original_name',
        'mime_type',
        'size',
        'alt_text',
        'display_order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'size' => 'integer',
        'display_order' => 'integer',
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
     * Get the item this image belongs to.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Get the tags associated with this item image.
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'item_image_tag')->withTimestamps();
    }

    /**
     * Scope to get item images that have a specific tag.
     *
     * @param  string  $tagInternalName  The tag's internal_name
     */
    public function scopeWithTag(Builder $query, string $tagInternalName): Builder
    {
        return $query->whereHas('tags', function (Builder $query) use ($tagInternalName) {
            $query->where('tags.internal_name', $tagInternalName);
        });
    }

    /**
     * Scope to get item images that do not have a specific tag.
     *
     * @param  string  $tagInternalName  The tag's internal_name
     */
    public function scopeWithoutTag(Builder $query, string $tagInternalName): Builder
    {
        return $query->whereDoesntHave('tags', function (Builder $query) use ($tagInternalName) {
            $query->where('tags.internal_name', $tagInternalName);
        });
    }

    /**
     * Get the next available display order for an item.
     *
     * @deprecated Use static::getNextDisplayOrderFor(['item_id' => $itemId]) instead
     */
    public static function getNextDisplayOrderForItem(string $itemId): int
    {
        return static::getNextDisplayOrderFor(['item_id' => $itemId]);
    }

    /**
     * Get a query builder scoped to this image's siblings (same item_id).
     *
     * @return Builder<static>
     */
    protected function getSiblingsQuery(): Builder
    {
        return static::where('item_id', $this->item_id);
    }

    /**
     * Tighten the display order for all images of an item, eliminating gaps.
     *
     * @deprecated Use tightenOrdering() instead
     */
    public function tightenOrderingForItem(): void
    {
        $this->tightenOrdering();
    }

    /**
     * Attach an available image to an item, preserving the ID.
     */
    public static function attachFromAvailableImage(AvailableImage $availableImage, string $itemId, ?string $altText = null): static
    {
        return DB::transaction(function () use ($availableImage, $itemId, $altText) {
            $displayOrder = static::getNextDisplayOrderForItem($itemId);

            // Move file from available storage to pictures storage
            $availableDisk = config('localstorage.available.images.disk');
            $availableDir = trim(config('localstorage.available.images.directory'), '/');
            $picturesDisk = config('localstorage.pictures.disk');
            $picturesDir = trim(config('localstorage.pictures.directory'), '/');

            $filename = $availableImage->path; // Already just filename

            // Move the file from images/ to pictures/
            Storage::disk($picturesDisk)->writeStream(
                $picturesDir.'/'.$filename,
                Storage::disk($availableDisk)->readStream($availableDir.'/'.$filename)
            );
            Storage::disk($availableDisk)->delete($availableDir.'/'.$filename);

            $itemImage = static::create([
                'id' => $availableImage->id, // Preserve the ID
                'item_id' => $itemId,
                'path' => $filename, // Keep filename unchanged
                'original_name' => $availableImage->original_name ?? '',
                'mime_type' => $availableImage->mime_type ?? '',
                'size' => $availableImage->size ?? 0,
                'alt_text' => $altText ?? $availableImage->comment,
                'display_order' => $displayOrder,
            ]);

            $availableImage->delete();

            return $itemImage;
        });
    }

    /**
     * Detach this image and convert it back to an available image, preserving the ID.
     */
    public function detachToAvailableImage(): AvailableImage
    {
        return $this->getConnection()->transaction(function () {
            // Move file from pictures storage back to available storage
            $picturesDisk = config('localstorage.pictures.disk');
            $picturesDir = trim(config('localstorage.pictures.directory'), '/');
            $availableDisk = config('localstorage.available.images.disk');
            $availableDir = trim(config('localstorage.available.images.directory'), '/');

            $filename = $this->path; // Already just filename

            // Move the file from pictures/ back to images/
            Storage::disk($availableDisk)->writeStream(
                $availableDir.'/'.$filename,
                Storage::disk($picturesDisk)->readStream($picturesDir.'/'.$filename)
            );
            Storage::disk($picturesDisk)->delete($picturesDir.'/'.$filename);

            $availableImage = AvailableImage::create([
                'id' => $this->id, // Preserve the ID
                'path' => $filename, // Keep filename unchanged
                'original_name' => $this->original_name,
                'mime_type' => $this->mime_type,
                'size' => $this->size,
                'comment' => $this->alt_text,
            ]);

            $this->delete();

            return $availableImage;
        });
    }

    public function imageDisk(): string
    {
        return config('localstorage.pictures.disk');
    }

    public function imageStoragePath(): string
    {
        return trim(config('localstorage.pictures.directory'), '/').'/'.$this->path;
    }

    public function imageMimeType(): ?string
    {
        return $this->mime_type;
    }

    public function imageDownloadFilename(): string
    {
        return $this->original_name ?: basename($this->path);
    }
}
