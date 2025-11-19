<?php

namespace Database\Seeders;

use App\Models\Context;
use Illuminate\Database\Seeder;

class ContextSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Context::create(['backward_compatibility' => null, 'internal_name' => 'Default Context']);
        Context::where(['internal_name' => 'Default Context'])->update(['is_default' => true]);

        Context::factory()->count(4)->create();
    }
}
