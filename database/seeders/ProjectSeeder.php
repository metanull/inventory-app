<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Project;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $projects = [
            ['backward_compatibility' => 'BAR,AMA', 'internal_name' => 'Discover Baroque Art', 'is_launched' => true, 'is_enabled' => true, ], 
            ['backward_compatibility' => 'ISL,EPM', 'internal_name' => 'Discover Islamic Art', 'is_launched' => true, 'is_enabled' => true, ], 
            ['backward_compatibility' => 'AWE', 'internal_name' => 'Sharing History', 'is_launched' => true, 'is_enabled' => true, ], 
            ['backward_compatibility' => null, 'internal_name' => 'Explore', 'is_launched' => true, 'is_enabled' => true, ], 
            ['backward_compatibility' => null, 'internal_name' => 'Travels', 'is_launched' => true, 'is_enabled' => true, ], 
        ];

        foreach ($projects as $project) {
            Project::create($project);
        }
    }
}
