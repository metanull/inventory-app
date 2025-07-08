<?php

namespace Database\Seeders;

use App\Models\Detail;
use App\Models\Item;
use App\Models\Partner;
use App\Models\Picture;
use Illuminate\Database\Seeder;

class PictureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get some existing models to attach pictures to
        $items = Item::limit(5)->get();
        $details = Detail::limit(5)->get();
        $partners = Partner::limit(3)->get();

        // Create pictures for items
        foreach ($items as $item) {
            Picture::factory()
                ->count(rand(1, 3))
                ->for($item, 'pictureable')
                ->create();
        }

        // Create pictures for details
        foreach ($details as $detail) {
            Picture::factory()
                ->count(rand(1, 2))
                ->for($detail, 'pictureable')
                ->create();
        }

        // Create pictures for partners
        foreach ($partners as $partner) {
            Picture::factory()
                ->count(rand(1, 2))
                ->for($partner, 'pictureable')
                ->create();
        }
    }
}
