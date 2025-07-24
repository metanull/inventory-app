<?php

namespace Database\Seeders;

use App\Models\Detail;
use App\Models\Item;
use App\Models\Partner;
use App\Models\Picture;
use Illuminate\Database\Seeder;

class OptimizedPictureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating Picture records with local images...');

        // Get existing models using eager loading to avoid N+1 queries
        $items = Item::limit(5)->get();
        $details = Detail::limit(5)->get();
        $partners = Partner::limit(3)->get();

        $pictures = collect();

        // Create pictures for items
        foreach ($items as $item) {
            $count = rand(1, 3);
            for ($i = 0; $i < $count; $i++) {
                $pictureData = Picture::factory()->make([
                    'pictureable_type' => Item::class,
                    'pictureable_id' => $item->id,
                ])->toArray();
                $pictureData['id'] = (string) \Illuminate\Support\Str::uuid();
                $pictureData['created_at'] = now();
                $pictureData['updated_at'] = now();
                $pictures->push($pictureData);
            }
        }

        // Create pictures for details
        foreach ($details as $detail) {
            $count = rand(1, 2);
            for ($i = 0; $i < $count; $i++) {
                $pictureData = Picture::factory()->make([
                    'pictureable_type' => Detail::class,
                    'pictureable_id' => $detail->id,
                ])->toArray();
                $pictureData['id'] = (string) \Illuminate\Support\Str::uuid();
                $pictureData['created_at'] = now();
                $pictureData['updated_at'] = now();
                $pictures->push($pictureData);
            }
        }

        // Create pictures for partners
        foreach ($partners as $partner) {
            $count = rand(1, 2);
            for ($i = 0; $i < $count; $i++) {
                $pictureData = Picture::factory()->make([
                    'pictureable_type' => Partner::class,
                    'pictureable_id' => $partner->id,
                ])->toArray();
                $pictureData['id'] = (string) \Illuminate\Support\Str::uuid();
                $pictureData['created_at'] = now();
                $pictureData['updated_at'] = now();
                $pictures->push($pictureData);
            }
        }

        // Insert all pictures in batches for better performance
        $pictures->chunk(50)->each(function ($batch) {
            Picture::insert($batch->toArray());
        });
    }
}
