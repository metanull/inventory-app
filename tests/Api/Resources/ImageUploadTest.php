<?php

namespace Tests\Api\Resources;

use App\Models\ImageUpload;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Api\Traits\AuthenticatesApiRequests;
use Tests\Api\Traits\TestsApiCrud;
use Tests\TestCase;

class ImageUploadTest extends TestCase
{
    use AuthenticatesApiRequests;
    use RefreshDatabase;
    use TestsApiCrud;

    protected function getResourceName(): string
    {
        return 'image-upload';
    }

    protected function getModelClass(): string
    {
        return ImageUpload::class;
    }

    /**
     * ImageUpload doesn't support standard create - it requires file upload
     */
    public function test_can_create_resource(): void
    {
        $this->markTestSkipped('ImageUpload requires file upload, not standard create');
    }

    /**
     * ImageUpload doesn't have an update route
     */
    public function test_can_update_resource(): void
    {
        $this->markTestSkipped('ImageUpload does not support update');
    }

    /**
     * ImageUpload doesn't have an update route
     */
    public function test_update_returns_404_for_nonexistent_resource(): void
    {
        $this->markTestSkipped('ImageUpload does not support update');
    }
}
