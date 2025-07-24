<?php

namespace App\Providers;

use App\Faker\LocalImageProvider;
use App\Faker\LoremPicsumImageProvider;
use Faker\Factory as FakerFactory;
use Faker\Generator as FakerGenerator;
use Illuminate\Database\Eloquent\Factories\Factory as EloquentFactory;
use Illuminate\Support\ServiceProvider;

/**
 * LocalImageFakerServiceProvider
 *
 * Registers the appropriate image provider for use during database seeding.
 *
 * Default behavior:
 * - Non-production environments: Uses LocalImageProvider for fast, reliable seeding
 * - Production environments: Uses LoremPicsumImageProvider with automatic fallback to local images
 *
 * Configuration:
 * - Set FAKER_USE_LOCAL_IMAGES=true to force local images in any environment
 * - Set FAKER_USE_LOCAL_IMAGES=false to force remote images (with local fallback) in any environment
 *
 * The LoremPicsumImageProvider now includes automatic fallback to local images when network
 * requests fail, ensuring seeding always succeeds even with network issues.
 */
class LocalImageFakerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(FakerGenerator::class, function () {
            $faker = FakerFactory::create();

            // Use local images by default in non-production environments for reliability and performance
            // Can be overridden with FAKER_USE_LOCAL_IMAGES=false to force remote images
            $useLocalImages = config('app.faker_use_local_images', ! app()->environment('production'));

            if ($useLocalImages) {
                $faker->addProvider(new LocalImageProvider($faker));
            } else {
                // LoremPicsumImageProvider now has automatic fallback to local images
                $faker->addProvider(new LoremPicsumImageProvider($faker));
            }

            return $faker;
        });

        $this->app->singleton(EloquentFactory::class, function ($app) {
            return EloquentFactory::construct(
                $app->make(FakerGenerator::class), $this->app->databasePath('factories')
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
