<?php

namespace Tests\Api\Resources;

use App\Models\AvailableImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Api\Traits\AuthenticatesApiRequests;
use Tests\Api\Traits\TestsApiImageViewing;
use Tests\TestCase;

class AvailableImageTest extends TestCase
{
    use AuthenticatesApiRequests;
    use RefreshDatabase;
    use TestsApiImageViewing;

    protected function getResourceName(): string
    {
        return 'available-image';
    }

    protected function getModelClass(): string
    {
        return AvailableImage::class;
    }
}
