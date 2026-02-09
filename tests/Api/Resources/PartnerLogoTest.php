<?php

namespace Tests\Api\Resources;

use App\Models\Partner;
use App\Models\PartnerLogo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Api\Traits\AuthenticatesApiRequests;
use Tests\Api\Traits\TestsApiImageResource;
use Tests\Api\Traits\TestsApiImageViewing;
use Tests\TestCase;

class PartnerLogoTest extends TestCase
{
    use AuthenticatesApiRequests;
    use RefreshDatabase;
    use TestsApiImageResource;
    use TestsApiImageViewing {
        TestsApiImageResource::getFactoryData insteadof TestsApiImageViewing;
        TestsApiImageResource::hasColumn insteadof TestsApiImageViewing;
    }

    protected function getResourceName(): string
    {
        return 'partner-logo';
    }

    protected function getModelClass(): string
    {
        return PartnerLogo::class;
    }

    protected function getParentModel()
    {
        return Partner::factory()->create();
    }

    protected function getParentRelation(): string
    {
        return 'partner';
    }

    /**
     * Override to include logo_type field.
     * Note: display_order should not be included here because it conflicts
     * with withOrder() in the trait's ordering tests.
     */
    protected function getFactoryData(): array
    {
        $parent = $this->getParentModel();

        return [
            'partner_id' => $parent->id,
            'path' => 'logos/test-logo.svg',
            'original_name' => 'test-logo.svg',
            'mime_type' => 'image/svg+xml',
            'size' => 5000,
            'logo_type' => 'primary',
            'alt_text' => 'Test logo',
        ];
    }
}
