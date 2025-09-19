<?php

namespace App\Console\Commands;

use App\Http\Resources\AvailableImageResource;
use App\Models\AvailableImage;
use Illuminate\Console\Command;
use Illuminate\Http\Request;

class TestAvailableImagesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:available-images';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test AvailableImage model and API resource';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== Testing AvailableImage Model ===');

        $images = AvailableImage::all();
        $this->info('Found '.count($images).' available images');

        foreach ($images as $image) {
            $this->line("- ID: {$image->id}");
            $this->line("  Internal Name: {$image->internal_name}");
            $this->line("  Original Name: {$image->original_name}");
            $this->line("  File Path: {$image->file_path}");
            $this->line("  File Size: {$image->file_size} bytes");
            $this->line("  MIME Type: {$image->mime_type}");
            $this->line("  Created: {$image->created_at}");
            $this->line('');
        }

        $this->info('=== Testing AvailableImageResource ===');

        // Create a mock request
        $request = new Request;

        $resource = AvailableImageResource::collection($images);
        $data = $resource->toArray($request);

        $this->info('Resource data structure:');
        $this->line(json_encode($data, JSON_PRETTY_PRINT));

        return 0;
    }
}
