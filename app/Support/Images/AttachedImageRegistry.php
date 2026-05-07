<?php

namespace App\Support\Images;

use App\Contracts\StreamableImageFile;
use App\Models\CollectionImage;
use App\Models\ContributorImage;
use App\Models\ItemImage;
use App\Models\PartnerImage;
use App\Models\PartnerLogo;
use App\Models\PartnerTranslationImage;
use App\Models\TimelineEventImage;
use Illuminate\Database\Eloquent\Model;

class AttachedImageRegistry
{
    /**
     * All model classes that own files in localstorage.pictures.
     *
     * @var list<class-string<Model&StreamableImageFile>>
     */
    private const MODELS = [
        ItemImage::class,
        CollectionImage::class,
        PartnerImage::class,
        PartnerTranslationImage::class,
        ContributorImage::class,
        TimelineEventImage::class,
        PartnerLogo::class,
    ];

    /**
     * Return all registered model class names after validation.
     *
     * @return list<class-string<Model&StreamableImageFile>>
     *
     * @throws \RuntimeException if any entry is invalid.
     */
    public static function modelClasses(): array
    {
        self::validate();

        return self::MODELS;
    }

    /**
     * Return all database table names for registered models.
     *
     * @return list<string>
     */
    public static function tableNames(): array
    {
        return array_map(
            fn (string $class) => (new $class)->getTable(),
            self::modelClasses()
        );
    }

    /**
     * Yield every registered model instance from the database, chunked to avoid memory pressure.
     * Each yielded value is an instance of StreamableImageFile.
     */
    public static function eachImage(int $chunkSize = 500): \Generator
    {
        foreach (self::modelClasses() as $class) {
            foreach ($class::query()->cursor() as $record) {
                yield $record;
            }
        }
    }

    /**
     * Stream every referenced storage path from registered model rows in chunks.
     * Yields strings of the form returned by imageStoragePath().
     */
    public static function referencedPaths(int $chunkSize = 500): \Generator
    {
        foreach (self::modelClasses() as $class) {
            foreach ($class::query()->cursor() as $record) {
                /** @var StreamableImageFile $record */
                yield $record->imageStoragePath();
            }
        }
    }

    /**
     * Validate all registered entries.
     * Fails fast if any class does not exist, does not extend Model,
     * or does not implement StreamableImageFile.
     *
     * @throws \RuntimeException
     */
    public static function validate(): void
    {
        foreach (self::MODELS as $class) {
            if (! class_exists($class)) {
                throw new \RuntimeException("AttachedImageRegistry: class does not exist: {$class}");
            }

            if (! is_subclass_of($class, Model::class)) {
                throw new \RuntimeException("AttachedImageRegistry: {$class} does not extend Illuminate\\Database\\Eloquent\\Model");
            }

            if (! is_subclass_of($class, StreamableImageFile::class)) {
                throw new \RuntimeException("AttachedImageRegistry: {$class} does not implement App\\Contracts\\StreamableImageFile");
            }
        }
    }
}
