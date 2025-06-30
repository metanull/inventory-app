<?php

namespace Database\Seeders;

use App\Models\Context;
use App\Models\Contextualization;
use App\Models\Detail;
use App\Models\Item;
use Illuminate\Database\Seeder;

class ContextualizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure we have contexts, items, and details to work with
        if (Context::count() === 0) {
            Context::factory(5)->create();
            Context::factory()->create(['is_default' => true]);
        }

        if (Item::count() === 0) {
            Item::factory(10)->create();
        }

        if (Detail::count() === 0) {
            Detail::factory(15)->create();
        }

        // Create contextualizations for items
        Contextualization::factory(20)->forItem()->create();

        // Create contextualizations for details
        Contextualization::factory(15)->forDetail()->create();

        // Create some contextualizations with the default context
        Contextualization::factory(10)->withDefaultContext()->create();
    }
}
